<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\PlaqueImmatriculation;
use App\Repository\PlaqueImmatriculationRepository;
use App\Repository\PlaqueParametreRepository;
use App\Service\PlaqueWordParser;
use OpenApi\Attributes as OA;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Espace "Plaques d'immatriculation" :
 *  - admin : import de fiches Word, liste, suppression, code d'accès
 *  - public : fiche "grand public" (QR code public) et fiche "service DEPPS" protégée par code (QR code privé)
 */
#[Route('/api/plaque-immatriculation')]
class ApiPlaqueImmatriculationController extends ApiInterface
{
    private const DOSSIER_SIGNES = 'media_deeps/plaques';
    private const TAILLE_MAX_WORD = 10 * 1024 * 1024;

    // Anti force brute sur le code d'accès : N échecs par IP dans la fenêtre → blocage
    private const ESSAIS_MAX = 10;
    private const FENETRE_SECONDES = 900;

    private function isAdminUser(): bool
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }

        return $user->getTypeUser() === 'ADMINISTRATEUR'
            || in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)
            || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    private function reponse(mixed $data, string $message = 'Opération effectuée avec succès', int $statut = 200): JsonResponse
    {
        $response = $this->json(['data' => $data, 'message' => $message, 'status' => $statut], $statut);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }

    private function refusAdmin(): JsonResponse
    {
        return $this->reponse(null, "Action réservée à l'administrateur.", 403);
    }

    private function dossierWord(): string
    {
        // Hors du dossier public : le Word contient les données privées
        $dossier = $this->getParameter('kernel.project_dir') . '/var/plaques';
        if (!is_dir($dossier)) {
            mkdir($dossier, 0775, true);
        }
        return $dossier;
    }

    private function resume(PlaqueImmatriculation $p): array
    {
        return [
            'id' => $p->getId(),
            'token' => $p->getToken(),
            'denomination' => $p->getDenomination(),
            'nature' => $p->getNature(),
            'immatriculation' => $p->getImmatriculation(),
            'numeroDossier' => $p->getNumeroDossier(),
            'localisation' => $p->getLocalisation(),
            'signeVisibilite' => $p->getSigneVisibilite(),
            'nomFichier' => $p->getNomFichier(),
            'createdAt' => $p->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $p->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    /* ───────────────────────────── ADMIN ───────────────────────────── */

    #[Route('/', methods: ['GET'])]
    #[OA\Tag(name: 'plaque-immatriculation')]
    public function index(PlaqueImmatriculationRepository $repository): Response
    {
        if (!$this->isAdminUser()) {
            return $this->refusAdmin();
        }

        return $this->reponse(array_map(fn ($p) => $this->resume($p), $repository->findAllOrdered()));
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Tag(name: 'plaque-immatriculation')]
    public function getOne(?PlaqueImmatriculation $plaque): Response
    {
        if (!$this->isAdminUser()) {
            return $this->refusAdmin();
        }
        if (!$plaque) {
            return $this->reponse(null, 'Plaque introuvable.', 404);
        }

        return $this->reponse($this->resume($plaque) + [
            'publique' => $plaque->getDonneesPubliques(),
            'privee' => $plaque->getDonneesPrivees(),
            'signeDataUri' => $this->signeDataUri($plaque),
        ]);
    }

    /**
     * Image du signe de visibilité en data URI : le front la dessine sur la plaque (canvas)
     * sans problème de CORS (une image d'un autre domaine bloquerait l'export PNG).
     */
    private function signeDataUri(PlaqueImmatriculation $plaque): ?string
    {
        $chemin = $plaque->getSigneVisibilite();
        if (!$chemin) {
            return null;
        }
        $fichier = $this->getParameter('upload_dir') . '/' . $chemin;
        if (!is_file($fichier)) {
            return null;
        }
        $mime = match (strtolower(pathinfo($fichier, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($fichier));
    }

    #[Route('/upload', methods: ['POST'])]
    #[OA\Post(
        summary: "Import de fiches Word de plaques d'immatriculation",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(properties: [
                    new OA\Property(property: 'fichiers[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
                ], type: 'object')
            )
        )
    )]
    #[OA\Tag(name: 'plaque-immatriculation')]
    public function upload(
        Request $request,
        PlaqueImmatriculationRepository $repository,
        PlaqueWordParser $parser,
        LoggerInterface $logger
    ): Response {
        if (!$this->isAdminUser()) {
            return $this->refusAdmin();
        }

        $fichiers = $request->files->get('fichiers') ?? [];
        if ($fichiers instanceof UploadedFile) {
            $fichiers = [$fichiers];
        }
        if (!$fichiers) {
            return $this->reponse([], 'Aucun fichier reçu.', 400);
        }

        $resultats = [];
        foreach ($fichiers as $fichier) {
            $resultats[] = $this->importerFichier($fichier, $repository, $parser, $logger);
        }

        $nbErreurs = count(array_filter($resultats, fn ($r) => $r['statut'] === 'erreur'));
        $message = $nbErreurs === 0
            ? count($resultats) . ' fiche(s) importée(s) avec succès.'
            : (count($resultats) - $nbErreurs) . ' fiche(s) importée(s), ' . $nbErreurs . ' en erreur.';

        return $this->reponse($resultats, $message);
    }

    private function importerFichier(
        UploadedFile $fichier,
        PlaqueImmatriculationRepository $repository,
        PlaqueWordParser $parser,
        LoggerInterface $logger
    ): array {
        $nom = $fichier->getClientOriginalName();

        try {
            if (!$fichier->isValid()) {
                // Motif précis (ex. limite upload_max_filesize du serveur, envoi interrompu…)
                throw new \RuntimeException("Échec de l'envoi du fichier : " . $fichier->getErrorMessage());
            }
            if (strtolower($fichier->getClientOriginalExtension()) !== 'docx') {
                throw new \RuntimeException('Seuls les fichiers Word .docx sont acceptés.');
            }
            if ($fichier->getSize() > self::TAILLE_MAX_WORD) {
                throw new \RuntimeException('Fichier trop volumineux (10 Mo maximum).');
            }

            $donnees = $parser->parse($fichier->getPathname());
            $resume = $donnees['resume'];

            // Réimport d'une fiche existante : on garde le même token → QR codes inchangés
            $plaque = $repository->findExisting($resume['immatriculation'], $resume['denomination']);
            $estNouvelle = $plaque === null;
            if ($estNouvelle) {
                $plaque = new PlaqueImmatriculation();
                $plaque->setToken(bin2hex(random_bytes(16)));
                $plaque->setCreatedBy($this->getUser());
                $plaque->setCreatedAtValue();
            }

            $plaque->setDenomination(mb_substr($resume['denomination'], 0, 255));
            $plaque->setNature($resume['nature']);
            $plaque->setImmatriculation($resume['immatriculation']);
            $plaque->setNumeroDossier($resume['numeroDossier'] ? mb_substr($resume['numeroDossier'], 0, 100) : null);
            $plaque->setLocalisation($resume['localisation']);
            $plaque->setDonneesPubliques($donnees['publique']);
            $plaque->setDonneesPrivees($donnees['privee']);
            $plaque->setUpdatedBy($this->getUser());
            $plaque->setUpdatedAt();

            if ($donnees['signe']) {
                $plaque->setSigneVisibilite($this->enregistrerSigne($donnees['signe'], $plaque->getSigneVisibilite()));
            }

            // Copie du Word original hors du dossier public
            $ancienWord = $plaque->getFichierStocke();
            $nomStocke = $plaque->getToken() . '-' . bin2hex(random_bytes(4)) . '.docx';
            $fichier->move($this->dossierWord(), $nomStocke);
            if ($ancienWord && $ancienWord !== $nomStocke) {
                @unlink($this->dossierWord() . '/' . $ancienWord);
            }
            $plaque->setFichierStocke($nomStocke);
            $plaque->setNomFichier(mb_substr($nom, 0, 255));

            $repository->add($plaque, true);

            return [
                'fichier' => $nom,
                'statut' => $estNouvelle ? 'cree' : 'mis_a_jour',
                'message' => $estNouvelle ? 'Plaque créée.' : 'Plaque mise à jour (QR codes inchangés).',
                'plaque' => $this->resume($plaque),
            ];
        } catch (\Throwable $e) {
            // Erreur imprévue (base de données, droits d'écriture…) : journalisée et affichée
            // à l'administrateur pour pouvoir la corriger, au lieu d'un message générique.
            if (!$e instanceof \RuntimeException || $e instanceof \Doctrine\DBAL\Exception) {
                $logger->error('Import de plaque impossible', ['fichier' => $nom, 'exception' => $e]);
            }

            return [
                'fichier' => $nom,
                'statut' => 'erreur',
                'message' => $e instanceof \RuntimeException && !$e instanceof \Doctrine\DBAL\Exception
                    ? $e->getMessage()
                    : 'Erreur lors de l’enregistrement : ' . $e->getMessage(),
                'plaque' => null,
            ];
        }
    }

    /** Enregistre l'image du signe de visibilité dans /uploads et retourne son chemin relatif. */
    private function enregistrerSigne(array $signe, ?string $ancien): string
    {
        $dossier = $this->getParameter('upload_dir') . '/' . self::DOSSIER_SIGNES;
        if (!is_dir($dossier)) {
            mkdir($dossier, 0775, true);
        }

        $nom = bin2hex(random_bytes(12)) . '.' . $signe['extension'];
        file_put_contents($dossier . '/' . $nom, $signe['contenu']);

        if ($ancien) {
            @unlink($this->getParameter('upload_dir') . '/' . $ancien);
        }

        return self::DOSSIER_SIGNES . '/' . $nom;
    }

    #[Route('/fichier/{id}', methods: ['GET'])]
    #[OA\Tag(name: 'plaque-immatriculation')]
    public function telechargerWord(?PlaqueImmatriculation $plaque): Response
    {
        if (!$this->isAdminUser()) {
            return $this->refusAdmin();
        }
        $chemin = $plaque?->getFichierStocke() ? $this->dossierWord() . '/' . $plaque->getFichierStocke() : null;
        if (!$chemin || !is_file($chemin)) {
            return $this->reponse(null, 'Fichier Word introuvable.', 404);
        }

        $response = new BinaryFileResponse($chemin);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $plaque->getNomFichier() ?: 'plaque.docx',
            'plaque-' . $plaque->getId() . '.docx'
        );
        return $response;
    }

    #[Route('/delete/{id}', methods: ['DELETE'])]
    #[OA\Tag(name: 'plaque-immatriculation')]
    public function delete(?PlaqueImmatriculation $plaque, PlaqueImmatriculationRepository $repository): Response
    {
        if (!$this->isAdminUser()) {
            return $this->refusAdmin();
        }
        if (!$plaque) {
            return $this->reponse(null, 'Plaque introuvable.', 404);
        }

        if ($plaque->getFichierStocke()) {
            @unlink($this->dossierWord() . '/' . $plaque->getFichierStocke());
        }
        if ($plaque->getSigneVisibilite()) {
            @unlink($this->getParameter('upload_dir') . '/' . $plaque->getSigneVisibilite());
        }
        $repository->remove($plaque, true);

        return $this->reponse(null, 'Plaque supprimée. Ses QR codes ne fonctionnent plus.');
    }

    #[Route('/code-acces', methods: ['GET'])]
    #[OA\Tag(name: 'plaque-immatriculation')]
    public function infoCodeAcces(PlaqueParametreRepository $parametreRepository): Response
    {
        if (!$this->isAdminUser()) {
            return $this->refusAdmin();
        }
        $parametre = $parametreRepository->getParametres();

        return $this->reponse([
            'estParDefaut' => password_verify(PlaqueParametreRepository::CODE_ACCES_DEFAUT, $parametre->getCodeAccesHash()),
            'updatedAt' => $parametre->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedBy' => $parametre->getUpdatedByNom(),
        ]);
    }

    #[Route('/code-acces', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Modifier le code d'accès des fiches privées",
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'nouveauCode', type: 'string', example: '87654321'),
            new OA\Property(property: 'confirmation', type: 'string', example: '87654321'),
        ]))
    )]
    #[OA\Tag(name: 'plaque-immatriculation')]
    public function modifierCodeAcces(Request $request, PlaqueParametreRepository $parametreRepository): Response
    {
        if (!$this->isAdminUser()) {
            return $this->refusAdmin();
        }

        $body = json_decode($request->getContent(), true) ?: [];
        $code = (string) ($body['nouveauCode'] ?? $request->get('nouveauCode', ''));
        $confirmation = (string) ($body['confirmation'] ?? $request->get('confirmation', ''));

        if (!preg_match('/^\d{8}$/', $code)) {
            return $this->reponse(null, 'Le code doit contenir exactement 8 chiffres.', 422);
        }
        if ($code !== $confirmation) {
            return $this->reponse(null, 'Les deux codes saisis ne correspondent pas.', 422);
        }

        $parametre = $parametreRepository->getParametres();
        $parametre->setCodeAccesHash(password_hash($code, PASSWORD_DEFAULT));
        $parametre->setUpdatedAt(new \DateTimeImmutable());
        $parametre->setUpdatedByNom($this->getUser()?->getUserIdentifier());
        $this->em->flush();

        return $this->reponse(null, "Code d'accès modifié. Il s'applique immédiatement à toutes les fiches privées.");
    }

    /* ───────────────────────────── PUBLIC ───────────────────────────── */

    #[Route('/public/{token}', methods: ['GET'])]
    #[OA\Tag(name: 'plaque-immatriculation')]
    public function fichePublique(string $token, PlaqueImmatriculationRepository $repository): Response
    {
        $plaque = $repository->findOneBy(['token' => $token]);
        if (!$plaque) {
            return $this->reponse(null, "Cette plaque n'est pas répertoriée dans la base officielle de la DEPPS.", 404);
        }

        return $this->reponse([
            'denomination' => $plaque->getDenomination(),
            'signeVisibilite' => $plaque->getSigneVisibilite(),
            'fiche' => $plaque->getDonneesPubliques(),
            'updatedAt' => $plaque->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ]);
    }

    /** Infos minimales pour l'écran de saisie du code (sans aucune donnée privée). */
    #[Route('/public/{token}/entete', methods: ['GET'])]
    #[OA\Tag(name: 'plaque-immatriculation')]
    public function entetePrivee(string $token, PlaqueImmatriculationRepository $repository): Response
    {
        $plaque = $repository->findOneBy(['token' => $token]);
        if (!$plaque) {
            return $this->reponse(null, "Cette plaque n'est pas répertoriée dans la base officielle de la DEPPS.", 404);
        }

        return $this->reponse(['denomination' => $plaque->getDenomination()]);
    }

    #[Route('/public/{token}/prive', methods: ['POST'])]
    #[OA\Post(
        summary: 'Fiche Service DEPPS (code d’accès requis)',
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'code', type: 'string', example: '12345678'),
        ]))
    )]
    #[OA\Tag(name: 'plaque-immatriculation')]
    public function fichePrivee(
        string $token,
        Request $request,
        PlaqueImmatriculationRepository $repository,
        PlaqueParametreRepository $parametreRepository,
        CacheItemPoolInterface $cache
    ): Response {
        $plaque = $repository->findOneBy(['token' => $token]);
        if (!$plaque) {
            return $this->reponse(null, "Cette plaque n'est pas répertoriée dans la base officielle de la DEPPS.", 404);
        }

        $cleEssais = 'plaque_code_' . hash('sha256', (string) $request->getClientIp());
        $item = $cache->getItem($cleEssais);
        $essais = $item->isHit() ? (int) $item->get() : 0;
        if ($essais >= self::ESSAIS_MAX) {
            return $this->reponse(['essaisRestants' => 0], 'Trop de tentatives. Réessayez dans 15 minutes.', 429);
        }

        $body = json_decode($request->getContent(), true) ?: [];
        $code = (string) ($body['code'] ?? $request->get('code', ''));

        if (!password_verify($code, $parametreRepository->getParametres()->getCodeAccesHash())) {
            $essais++;
            $item->set($essais);
            $item->expiresAfter(self::FENETRE_SECONDES);
            $cache->save($item);
            $restants = max(0, self::ESSAIS_MAX - $essais);

            return $this->reponse(
                ['essaisRestants' => $restants],
                $restants > 0 ? "Code d'accès incorrect." : 'Trop de tentatives. Réessayez dans 15 minutes.',
                $restants > 0 ? 403 : 429
            );
        }

        $cache->deleteItem($cleEssais);

        return $this->reponse([
            'denomination' => $plaque->getDenomination(),
            'signeVisibilite' => $plaque->getSigneVisibilite(),
            'fiche' => $plaque->getDonneesPrivees(),
            'updatedAt' => $plaque->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ]);
    }
}
