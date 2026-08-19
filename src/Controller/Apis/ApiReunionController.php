<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Reunion;
use App\Entity\ReunionPartenaire;
use App\Entity\Fichier;
use App\Repository\ReunionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/api/reunion')]
class ApiReunionController extends ApiInterface
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

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des réunions.
     * Si l'utilisateur n'est pas Administrateur, retourne uniquement ses propres réunions.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des réunions',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Reunion::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'reunion')]
    public function index(Request $request, ReunionRepository $reunionRepository): Response
    {
        try {
            $startDate = $request->query->get('start_date');
            $endDate = $request->query->get('end_date');
            $type = $request->query->get('type');

            $user = $this->getUser();
            $isAdmin = $this->isAdminUser();
            $filterUser = $isAdmin ? null : $user;

            $reunions = $reunionRepository->findByAdvancedFilter($startDate, $endDate, $type, $filterUser);

            return $this->responseData($reunions, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche une réunion en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche une réunion en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Reunion::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'reunion')]
    public function getOne(?Reunion $reunion)
    {
        try {
            if ($reunion) {
                $response = $this->responseData($reunion, 'group1', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($reunion);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/public/{token}', methods: ['GET'])]
    /**
     * Endpoint PUBLIC : retourne les infos d'une réunion via son token (pour la page de présence).
     */
    #[OA\Tag(name: 'reunion')]
    public function publicByToken(string $token, ReunionRepository $reunionRepository): Response
    {
        try {
            $reunion = $reunionRepository->findOneBy(['token' => $token]);
            if ($reunion) {
                $response = $this->responseData($reunion, 'group1', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage('Réunion introuvable');
                $this->setStatusCode(404);
                $response = $this->response('[]');
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer une réunion avec possibilité d'ajouter des partenaires.
     */
    #[OA\Tag(name: 'reunion')]
    public function create(Request $request, ReunionRepository $reunionRepository): Response
    {
        try {
            $objet = $request->get('objet');
            $type = $request->get('type', 'presentiel');
            $lien = $request->get('lien');
            $jour = $request->get('jour');

            if (!$objet && $request->getContent()) {
                $jsonData = json_decode($request->getContent(), true);
                if (is_array($jsonData)) {
                    $objet = $jsonData['objet'] ?? '';
                    $type = $jsonData['type'] ?? 'presentiel';
                    $lien = $jsonData['lien'] ?? null;
                    $jour = $jsonData['jour'] ?? null;
                }
            }

            $reunion = new Reunion();
            $reunion->setObjet($objet ?? '');
            $reunion->setType($type ?? 'presentiel');
            $reunion->setLien($type === 'en_ligne' ? $lien : null);
            $reunion->setJour(!empty($jour) ? new \DateTime($jour) : null);
            $reunion->setToken(bin2hex(random_bytes(16)));
            $reunion->setCreatedBy($this->getUser());
            $reunion->setUpdatedBy($this->getUser());
            $reunion->setCreatedAtValue();
            $reunion->setUpdatedAt();

            $errorResponse = $this->errorResponse($reunion);
            if ($errorResponse !== null) {
                return $errorResponse;
            }

            $this->em->persist($reunion);
            $this->handlePartenaires($request, $reunion);
            $this->em->flush();

            return $this->responseData($reunion, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $this->setStatusCode(500);
            return $this->response('[]');
        }
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    /**
     * Permet de modifier une réunion (réservé à l'Administrateur).
     */
    #[OA\Tag(name: 'reunion')]
    public function update(Request $request, ?Reunion $reunion, ReunionRepository $reunionRepository): Response
    {
        try {
            if (!$this->isAdminUser()) {
                $this->setMessage("Action non autorisée. Seul l'administrateur peut modifier une réunion.");
                $this->setStatusCode(403);
                return $this->response('[]');
            }

            if (!$reunion) {
                $this->setMessage("Cette ressource est inexistante");
                $this->setStatusCode(404);
                return $this->response('[]');
            }

            $objet = $request->get('objet');
            $type = $request->get('type', 'presentiel');
            $lien = $request->get('lien');
            $jour = $request->get('jour');

            if (!$objet && $request->getContent()) {
                $jsonData = json_decode($request->getContent(), true);
                if (is_array($jsonData)) {
                    $objet = $jsonData['objet'] ?? $reunion->getObjet();
                    $type = $jsonData['type'] ?? $reunion->getType();
                    $lien = $jsonData['lien'] ?? $reunion->getLien();
                    $jour = $jsonData['jour'] ?? ($reunion->getJour() ? $reunion->getJour()->format('Y-m-d') : null);
                }
            }

            if ($objet !== null) {
                $reunion->setObjet($objet);
            }
            if ($type !== null) {
                $reunion->setType($type);
            }
            $reunion->setLien($type === 'en_ligne' ? $lien : null);
            $reunion->setJour(!empty($jour) ? new \DateTime($jour) : null);
            $reunion->setUpdatedBy($this->getUser());
            $reunion->setUpdatedAt();

            $errorResponse = $this->errorResponse($reunion);
            if ($errorResponse !== null) {
                return $errorResponse;
            }

            $this->handlePartenaires($request, $reunion);
            $this->em->flush();

            return $this->responseData($reunion, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $this->setStatusCode(500);
            return $this->response('[]');
        }
    }

    #[Route('/delete/{id}', methods: ['DELETE'])]
    /**
     * Permet de supprimer une réunion (réservé à l'Administrateur).
     */
    #[OA\Tag(name: 'reunion')]
    public function delete(Request $request, ?Reunion $reunion, ReunionRepository $reunionRepository): Response
    {
        try {
            if (!$this->isAdminUser()) {
                $this->setMessage("Action non autorisée. Seul l'administrateur peut supprimer une réunion.");
                $this->setStatusCode(403);
                return $this->response('[]');
            }

            if ($reunion !== null) {
                $reunionRepository->remove($reunion, true);

                $this->setMessage("Opération effectuée avec succès");
                return $this->response($reunion);
            }

            $this->setMessage("Cette ressource est inexistante");
            $this->setStatusCode(300);
            return $this->response('[]');
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }

    #[Route('/delete/all', methods: ['DELETE'])]
    /**
     * Permet de supprimer plusieurs réunions (réservé à l'Administrateur).
     */
    #[OA\Tag(name: 'reunion')]
    public function deleteAll(Request $request, ReunionRepository $reunionRepository): Response
    {
        try {
            if (!$this->isAdminUser()) {
                $this->setMessage("Action non autorisée. Seul l'administrateur peut supprimer des réunions.");
                $this->setStatusCode(403);
                return $this->response('[]');
            }

            $data = json_decode($request->getContent());
            if (!empty($data->ids)) {
                foreach ($data->ids as $value) {
                    $id = is_object($value) ? ($value->id ?? null) : ($value['id'] ?? $value);
                    if ($id) {
                        $reunion = $reunionRepository->find($id);
                        if ($reunion !== null) {
                            $reunionRepository->remove($reunion);
                        }
                    }
                }
                $this->em->flush();
            }

            $this->setMessage("Opération effectuée avec succès");
            return $this->response('[]');
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }

    /**
     * Traite l'ajout / mise à jour des partenaires d'une réunion
     */
    private function handlePartenaires(Request $request, Reunion $reunion): void
    {
        $rawPartenaires = $request->get('partenaires');
        if (is_string($rawPartenaires)) {
            $rawPartenaires = json_decode($rawPartenaires, true);
        }

        if (empty($rawPartenaires) && $request->getContent()) {
            $json = json_decode($request->getContent(), true);
            if (!empty($json['partenaires'])) {
                $rawPartenaires = $json['partenaires'];
            }
        }

        // Si des partenaires sont transmis (tableau même vide si on a tout supprimé)
        if ($rawPartenaires !== null && is_array($rawPartenaires)) {
            // Supprimer les anciens partenaires
            foreach ($reunion->getPartenaires() as $existing) {
                $reunion->removePartenaire($existing);
                $this->em->remove($existing);
            }

            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
            $allFiles = $request->files->all();

            foreach ($rawPartenaires as $index => $pData) {
                $nom = is_array($pData) ? ($pData['nom'] ?? '') : '';
                if (empty($nom) && empty($pData['existing_logo_id'])) {
                    // Ignorer les lignes totalement vides
                    if (!isset($allFiles['partenaires'][$index]['logo']) && !$request->files->get("partenaire_logo_{$index}")) {
                        continue;
                    }
                }

                $partenaire = new ReunionPartenaire();
                $partenaire->setNom($nom);
                $partenaire->setReunion($reunion);

                // Récupération du fichier logo uploadé
                $uploadedFile = null;
                if (isset($allFiles['partenaires'][$index]['logo']) && $allFiles['partenaires'][$index]['logo'] instanceof UploadedFile) {
                    $uploadedFile = $allFiles['partenaires'][$index]['logo'];
                } elseif ($request->files->get("partenaire_logo_{$index}") instanceof UploadedFile) {
                    $uploadedFile = $request->files->get("partenaire_logo_{$index}");
                }

                if ($uploadedFile) {
                    $filePrefix = 'partenaire_' . uniqid();
                    $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
                    if ($fichier) {
                        $partenaire->setLogo($fichier);
                    }
                } elseif (!empty($pData['existing_logo_id'])) {
                    $existingFichier = $this->em->getRepository(Fichier::class)->find((int)$pData['existing_logo_id']);
                    if ($existingFichier) {
                        $partenaire->setLogo($existingFichier);
                    }
                }

                $this->em->persist($partenaire);
                $reunion->addPartenaire($partenaire);
            }
        }
    }
}
