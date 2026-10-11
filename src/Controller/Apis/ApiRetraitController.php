<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Retrait;
use App\Repository\RetraitRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/retrait')]
class ApiRetraitController extends ApiInterface
{
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

    // Seuls les comptes portant explicitement ROLE_SUPER_ADMIN peuvent valider
    // un décaissement — un admin/comptable "normal" (ROLE_ADMIN sans
    // ROLE_SUPER_ADMIN) ne doit voir/déclencher que la création de demandes.
    private function isSuperAdminUser(): bool
    {
        $user = $this->getUser();

        return $user && in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true);
    }

    #[Route('/', methods: ['GET'])]
    /**
     * Historique paginé des demandes de retrait (le plus récent en premier).
     * Paramètres de requête : ?with_pagination=true&page=1&limit=10&statut=en_attente
     */
    #[OA\Tag(name: 'retrait')]
    public function index(Request $request, RetraitRepository $retraitRepository): Response
    {
        try {
            if (!$this->isAdminUser()) {
                $this->setMessage("Action non autorisée.");
                $this->setStatusCode(403);
                return $this->response('[]');
            }

            $statut = $request->query->get('statut');
            $qb = $retraitRepository->findAllOrderedQueryBuilder($statut ?: null);

            return $this->responseData($qb, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $this->setStatusCode(500);
            return $this->response('[]');
        }
    }

    // Nom lisible du super admin en cours, pour horodater la décision (valider/annuler).
    private function currentSuperAdminNom(): string
    {
        $user = $this->getUser();
        $personne = $user ? $user->getPersonne() : null;

        return ($personne && method_exists($personne, 'getNom'))
            ? trim($personne->getNom() . ' ' . $personne->getPrenoms())
            : ($user ? $user->getEmail() : 'Super administrateur');
    }

    #[Route('/valider/{id}', methods: ['POST'])]
    /**
     * Marque une demande de retrait comme décaissée/validée (réservé aux super admins).
     */
    #[OA\Tag(name: 'retrait')]
    public function valider(?Retrait $retrait, RetraitRepository $retraitRepository): Response
    {
        try {
            if (!$this->isSuperAdminUser()) {
                $this->setMessage("Action réservée aux super administrateurs.");
                $this->setStatusCode(403);
                return $this->response('[]');
            }

            if (!$retrait) {
                $this->setMessage("Cette demande de retrait est introuvable.");
                $this->setStatusCode(404);
                return $this->response('[]');
            }

            if ($retrait->getStatut() === 'valide') {
                $this->setMessage("Cette demande a déjà été validée.");
                return $this->responseData($retrait, 'group1', ['Content-Type' => 'application/json']);
            }

            if ($retrait->getStatut() === 'annule') {
                $this->setMessage("Cette demande a été annulée, elle ne peut plus être validée.");
                $this->setStatusCode(400);
                return $this->response('[]');
            }

            $user = $this->getUser();

            $retrait->setStatut('valide');
            $retrait->setTraiteParNom($this->currentSuperAdminNom());
            $retrait->setTraiteAt(new \DateTimeImmutable());
            $retrait->setUpdatedBy($user);
            $retrait->setUpdatedAt();

            $retraitRepository->add($retrait, true);

            $this->setMessage("Demande de retrait validée avec succès.");
            return $this->responseData($retrait, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $this->setStatusCode(500);
            return $this->response('[]');
        }
    }

    #[Route('/annuler/{id}', methods: ['POST'])]
    /**
     * Annule une demande de retrait (réservé aux super admins). Un motif
     * optionnel peut être fourni ("motif" en JSON ou en champ de formulaire).
     */
    #[OA\Tag(name: 'retrait')]
    public function annuler(Request $request, ?Retrait $retrait, RetraitRepository $retraitRepository): Response
    {
        try {
            if (!$this->isSuperAdminUser()) {
                $this->setMessage("Action réservée aux super administrateurs.");
                $this->setStatusCode(403);
                return $this->response('[]');
            }

            if (!$retrait) {
                $this->setMessage("Cette demande de retrait est introuvable.");
                $this->setStatusCode(404);
                return $this->response('[]');
            }

            if ($retrait->getStatut() === 'annule') {
                $this->setMessage("Cette demande a déjà été annulée.");
                return $this->responseData($retrait, 'group1', ['Content-Type' => 'application/json']);
            }

            if ($retrait->getStatut() === 'valide') {
                $this->setMessage("Cette demande a déjà été décaissée, elle ne peut plus être annulée.");
                $this->setStatusCode(400);
                return $this->response('[]');
            }

            $motif = $request->get('motif');
            if (!$motif && $request->getContent()) {
                $jsonData = json_decode($request->getContent(), true);
                if (is_array($jsonData)) {
                    $motif = $jsonData['motif'] ?? null;
                }
            }

            $user = $this->getUser();

            $retrait->setStatut('annule');
            $retrait->setMotifAnnulation($motif ?: null);
            $retrait->setTraiteParNom($this->currentSuperAdminNom());
            $retrait->setTraiteAt(new \DateTimeImmutable());
            $retrait->setUpdatedBy($user);
            $retrait->setUpdatedAt();

            $retraitRepository->add($retrait, true);

            $this->setMessage("Demande de retrait annulée.");
            return $this->responseData($retrait, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $this->setStatusCode(500);
            return $this->response('[]');
        }
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Crée une demande de retrait (montant + numéro mobile money) et notifie
     * dg@mcm-ci.com par email avec les références de la demande.
     */
    #[OA\Tag(name: 'retrait')]
    public function create(Request $request, RetraitRepository $retraitRepository): Response
    {
        try {
            if (!$this->isAdminUser()) {
                $this->setMessage("Action non autorisée.");
                $this->setStatusCode(403);
                return $this->response('[]');
            }

            $montant = $request->get('montant');
            $telephone = $request->get('telephone');

            if (!$montant && $request->getContent()) {
                $jsonData = json_decode($request->getContent(), true);
                if (is_array($jsonData)) {
                    $montant = $jsonData['montant'] ?? null;
                    $telephone = $jsonData['telephone'] ?? null;
                }
            }

            if (empty($montant) || empty($telephone)) {
                $this->setMessage("Le montant et le numéro sont obligatoires.");
                $this->setStatusCode(400);
                return $this->response('[]');
            }

            $user = $this->getUser();
            $personne = $user ? $user->getPersonne() : null;
            $demandePar = ($personne && method_exists($personne, 'getNom'))
                ? trim($personne->getNom() . ' ' . $personne->getPrenoms())
                : ($user ? $user->getEmail() : 'Inconnu');

            $retrait = new Retrait();
            $retrait->setUser($user);
            $retrait->setMontant((string) $montant);
            $retrait->setTelephone((string) $telephone);
            $retrait->setDemandePar($demandePar);
            $retrait->setCreatedBy($user);
            $retrait->setUpdatedBy($user);
            $retrait->setCreatedAtValue();
            $retrait->setUpdatedAt();

            $retraitRepository->add($retrait, true);

            // L'envoi du mail est secondaire : la demande est déjà enregistrée,
            // on ne bloque pas la réponse si le SMTP échoue (même pattern que
            // les autres notifications du projet).
            try {
                $this->sendMailService->send(
                    'depps@leadagro.net',
                    'dg@mcm-ci.com',
                    'Nouvelle demande de retrait',
                    'retrait_demande',
                    [
                        'retrait' => [
                            'reference' => 'RET-' . $retrait->getId(),
                            'montant' => $retrait->getMontant(),
                            'telephone' => $retrait->getTelephone(),
                            'demandePar' => $retrait->getDemandePar(),
                            'date' => (new \DateTime())->format('d/m/Y à H:i'),
                        ],
                    ]
                );
            } catch (\Exception $mailException) {
            }

            return $this->responseData($retrait, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $this->setStatusCode(500);
            return $this->response('[]');
        }
    }
}
