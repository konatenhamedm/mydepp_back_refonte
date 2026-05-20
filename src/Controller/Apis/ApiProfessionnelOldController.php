<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Professionnel;
use App\Entity\Transaction;
use App\Entity\ValidationWorkflow;
use App\Repository\ProfessionnelRepository;
use App\Repository\UserRepository;
use App\Repository\TransactionRepository;
use App\Service\SendMailService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\Registry;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

#[Route('/api/professionnel/old')]
class ApiProfessionnelOldController extends ApiInterface
{
    #[Route('/anciens/list', name: 'api_anciens_professionnels_list', methods: ['GET'])]
    #[OA\Tag(name: 'professionnel')]
    public function listAnciensProfessionnels(
        Request $request,
        ProfessionnelRepository $professionnelRepository,
    ): Response {
        try {
            $etatOld = $request->query->get('etat_old');
            $page    = (int) $request->query->get('page', 1);
            $limit   = (int) $request->query->get('limit', 20);

            $qb = $professionnelRepository->createQueryBuilder('p')
                ->where('p.etatOld IS NOT NULL');

            if ($etatOld) {
                $qb->andWhere('p.etatOld = :etat')->setParameter('etat', $etatOld);
            }

            $search = $request->query->get('search');
            if ($search) {
                $qb->andWhere('p.nom LIKE :search OR p.prenoms LIKE :search OR p.code LIKE :search')
                   ->setParameter('search', '%' . $search . '%');
            }

            $total = (clone $qb)->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();

            $professionnels = $qb
                ->orderBy('p.createdAt', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(function (Professionnel $p) {
                $profession = $p->getProfession();

                $raison = null;
                if ($p->getEtatOld() === 'documents_recus_invalide') {
                    $validationWorkflow = $this->em->getRepository(ValidationWorkflow::class)->findOneBy(
                        ['personne' => $p, 'etape' => 'documents_recus_invalide'],
                        ['id' => 'DESC']
                    );
                    $raison = $validationWorkflow?->getRaison();
                }

                return [
                    'id'              => $p->getId(),
                    'nom'             => $p->getNom(),
                    'prenoms'         => $p->getPrenoms(),
                    'code'            => $p->getCode(),
                    'etatOld'         => $p->getEtatOld(),
                    'dateValidation'  => $p->getDateValidation()?->format('d/m/Y'),
                    'createdAt'       => $p->getCreatedAt()?->format('d/m/Y'),
                    'profession'      => $profession?->getLibelle(),
                    'civilite'        => $p->getCivilite()?->getLibelle(),
                    'raison'          => $raison,
                ];
            }, $professionnels);

            return $this->json([
                'code'    => 200,
                'message' => 'Liste des anciens professionnels',
                'data'    => $data,
                'meta'    => [
                    'total' => (int) $total,
                    'page'  => $page,
                    'limit' => $limit,
                    'pages' => (int) ceil($total / $limit),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json(['code' => 500, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/anciens/{id}', name: 'api_anciens_professionnels_show', methods: ['GET'])]
    #[OA\Tag(name: 'professionnel')]
    public function showAncienProfessionnel(
        Professionnel $professionnel,
        Request $request
    ): Response {
        try {
            $profession = $professionnel->getProfession();
            $civilite = $professionnel->getCivilite();
            $nationate = $professionnel->getNationate();
            $specialite = $professionnel->getSpecialite();
            $situationPro = $professionnel->getSituationPro();

            $raison = null;
            if ($professionnel->getEtatOld() === 'documents_recus_invalide') {
                $validationWorkflow = $this->em->getRepository(ValidationWorkflow::class)->findOneBy(
                    ['personne' => $professionnel, 'etape' => 'documents_recus_invalide'],
                    ['id' => 'DESC']
                );
                $raison = $validationWorkflow?->getRaison();
            }

            $data = [
                'id'              => $professionnel->getId(),
                'nom'             => $professionnel->getNom(),
                'prenoms'         => $professionnel->getPrenoms(),
                'code'            => $professionnel->getCode(),
                'etatOld'         => $professionnel->getEtatOld(),
                'dateValidation'  => $professionnel->getDateValidation()?->format('d/m/Y'),
                'createdAt'       => $professionnel->getCreatedAt()?->format('d/m/Y'),
                'profession'      => $profession?->getLibelle(),
                'civilite'        => $civilite?->getLibelle(),
                'email'           => $professionnel->getEmail(),
                'number'          => $professionnel->getNumber(),
                'dateNaissance'   => $professionnel->getDateNaissance()?->format('d/m/Y'),
                'lieuDiplome'     => $professionnel->getLieuDiplome(),
                'diplome'         => $professionnel->getDiplome(),
                'nationalite'     => $nationate?->getLibelle(),
                'specialite'      => $specialite?->getLibelle(),
                'situationPro'    => $situationPro?->getLibelle(),
                'raison'          => $raison,
                
                // Documents URLs
                'photo'           => $this->getFichierUrl($professionnel->getPhoto(), $request),
                'diplomeFile'     => $this->getFichierUrl($professionnel->getDiplomeFile(), $request),
                'cni'             => $this->getFichierUrl($professionnel->getCni(), $request),
                'cv'              => $this->getFichierUrl($professionnel->getCv(), $request),
                'casier'          => $this->getFichierUrl($professionnel->getCasier(), $request),
                'certificat'      => $this->getFichierUrl($professionnel->getCertificat(), $request),
            ];

            return $this->json([
                'code'    => 200,
                'message' => 'Détails de l\'ancien professionnel',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return $this->json(['code' => 500, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/anciens/{id}/validate', name: 'api_anciens_professionnels_validate', methods: ['POST'])]
    #[OA\Tag(name: 'professionnel')]
    public function validateAncienProfessionnel(
        Request $request,
        Professionnel $professionnel,
        ProfessionnelRepository $professionnelRepository,
        Registry $workflowRegistry,
        SendMailService $sendMailService,
        UserRepository $userRepository,
    ): Response {
        try {
            $data = json_decode($request->getContent(), true);
            $status = $data['status'] ?? null; // 'documents_recus_valide' or 'documents_recus_invalide'
            $raison = $data['raison'] ?? null;

            if ($status !== 'documents_recus_valide' && $status !== 'documents_recus_invalide') {
                return $this->json(['code' => 400, 'message' => 'Statut de validation invalide.'], Response::HTTP_BAD_REQUEST);
            }

            $transition = ($status === 'documents_recus_valide') ? 'documents_valider' : 'documents_invalider';

            $workflow = $workflowRegistry->get($professionnel, 'validation_ancien_professionnel');

            if (!$workflow->can($professionnel, $transition)) {
                return $this->json([
                    'code'    => 400,
                    'message' => sprintf('Transition "%s" impossible depuis l\'état "%s".', $transition, $professionnel->getEtatOld()),
                ], Response::HTTP_BAD_REQUEST);
            }

            // Applique la transition
            $workflow->apply($professionnel, $transition);
            $professionnelRepository->add($professionnel, true);

            // Log de la décision dans ValidationWorkflow
            $validationWorkflow = new ValidationWorkflow();
            $validationWorkflow->setEtape($status);
            $validationWorkflow->setRaison($raison);
            $validationWorkflow->setPersonne($professionnel);
            $validationWorkflow->setCreatedBy($this->getUser());
            $validationWorkflow->setUpdatedBy($this->getUser());

            $this->em->persist($validationWorkflow);
            $this->em->flush();

            // Envoi email au professionnel
            try {
                $emailTo = $professionnel->getEmail();

                // Fallback : email du compte User lié
                if (!$emailTo || !filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
                    $linkedUser = $userRepository->findOneBy(['personne' => $professionnel->getId()]);
                    $emailTo = $linkedUser?->getEmail();
                }

                if ($emailTo && filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
                    $sendMailService->send(
                        'depps@leadagro.net',
                        $emailTo,
                        $this->getMailSubject($transition),
                        'ancien_professionnel_transition',
                        [
                            'professionnel' => [
                                'nom'       => $professionnel->getNom(),
                                'prenoms'   => $professionnel->getPrenoms(),
                                'civilite'  => $professionnel->getCivilite()?->getLibelle() ?? '',
                                'profession' => $professionnel->getProfession()?->getLibelle() ?? '',
                            ],
                            'transition' => $transition,
                            'raison'     => $raison, // optional but good to have
                        ]
                    );
                }

                // Notification interne
                $linkedUser = $linkedUser ?? $userRepository->findOneBy(['personne' => $professionnel->getId()]);
                $adminUser  = $this->getUser();
                if ($linkedUser && $adminUser) {
                    $sendMailService->sendNotification(
                        $this->getNotificationMessage($transition),
                        $linkedUser,
                        $adminUser
                    );
                }
            } catch (\Exception $e) {
                error_log("Erreur envoi email validation ancien professionnel: " . $e->getMessage());
            }

            return $this->json([
                'code'    => 200,
                'message' => 'Validation enregistrée avec succès.',
                'data'    => ['etatOld' => $professionnel->getEtatOld()],
            ]);
        } catch (\Exception $e) {
            return $this->json(['code' => 500, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/recu/recherche', name: 'api_professionnel_old_recu_recherche', methods: ['GET'])]
    #[OA\Tag(name: 'professionnel')]
    public function searchTransactions(
        Request $request,
        UserRepository $userRepository,
        TransactionRepository $transactionRepository,
        ProfessionnelRepository $professionnelRepository
    ): Response {
        try {
            $email = trim($request->query->get('email', ''));
            $code  = trim($request->query->get('code', ''));

            if (empty($email) && empty($code)) {
                return $this->json([
                    'statut' => 0,
                    'message' => 'Veuillez saisir au moins une adresse e-mail ou un code professionnel.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = null;
            $personne = null;

            if (!empty($code)) {
                // Recherche par code professionnel
                $personne = $professionnelRepository->findOneBy(['code' => $code]);
                if (!$personne) {
                    return $this->json([
                        'statut' => 0,
                        'message' => 'Aucun professionnel trouvé avec ce code.'
                    ], Response::HTTP_NOT_FOUND);
                }

                $user = $userRepository->findOneBy(['personne' => $personne->getId()]);
                if (!$user) {
                    return $this->json([
                        'statut' => 0,
                        'message' => 'Aucun compte utilisateur n\'est associé à ce professionnel.'
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                // Recherche par email
                $user = $userRepository->findOneBy(['email' => $email]);
                if (!$user) {
                    return $this->json([
                        'statut' => 0,
                        'message' => 'Aucun compte utilisateur trouvé avec cette adresse e-mail.'
                    ], Response::HTTP_NOT_FOUND);
                }

                $personne = $user->getPersonne();
                if (!$personne || !$personne instanceof Professionnel) {
                    return $this->json([
                        'statut' => 0,
                        'message' => 'Cet utilisateur n\'est pas enregistré en tant que professionnel.'
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            // Fetch successful transactions
            $transactions = $transactionRepository->findBy(
                ['user' => $user, 'state' => 1],
                ['createdAt' => 'DESC']
            );

            // Format transactions identically to standard index format in ApiPaiementController
            $formattedTransactions = array_map(function (Transaction $transaction) use ($personne) {
                $profession = $personne->getProfession();
                return [
                    "id" => $transaction->getId(),
                    "montant" => $transaction->getMontant(),
                    "reference" => $transaction->getReference(),
                    "reference_channel" => $transaction->getReferenceChannel(),
                    "channel" => $transaction->getChannel(),
                    "type" => $transaction->getType(),
                    "state" => $transaction->getState(),
                    "typeUser" => $transaction->getUser()->getTypeUser(),
                    "createdAt" => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
                    "email" => $transaction->getUser()->getEmail(),
                    "user" => [
                        'profession' => $profession ? [
                            'libelle' => $profession->getLibelle() ?? "",
                            'id' => $profession->getId(),
                            'code' => $profession->getCode(),
                            'montantNouvelleDemande' => $profession->getMontantNouvelleDemande(),
                            'montantRenouvellement' => $profession->getMontantRenouvellement(),
                        ] : null,
                        "typeUser" => $transaction->getUser()->getTypeUser(),
                        "code" => $personne->getCode(),
                        "poleSanitaire" => $personne->getPoleSanitaire(),
                        "nom" => $personne->getNom(),
                        "prenoms" => $personne->getPrenoms(),
                        "lieuExercicePro" => $personne->getLieuExercicePro(),
                        "email" => $personne->getEmail(),
                        "number" => $personne->getNumber(),
                        "quartier" => $personne->getQuartier(),
                        "id" => $personne->getId(),
                        "createdAt" => $personne->getCreatedAt()?->format('Y-m-d H:i:s'),
                    ]
                ];
            }, $transactions);

            return $this->json([
                'statut' => 1,
                'data' => $formattedTransactions
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'statut' => 0,
                'message' => 'Une erreur est survenue lors de la recherche : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/code/recherche', name: 'api_professionnel_old_code_recherche', methods: ['GET'])]
    #[OA\Tag(name: 'professionnel')]
    public function searchByCode(Request $request, ProfessionnelRepository $professionnelRepository, UserRepository $userRepository): Response
    {
        try {
            $code = trim($request->query->get('code', ''));
            if (empty($code)) {
                return $this->json([
                    'statut' => 0,
                    'message' => 'Veuillez renseigner le code professionnel.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $professionnel = $professionnelRepository->findOneBy(['code' => $code]);
            if (!$professionnel) {
                return $this->json([
                    'statut' => 0,
                    'message' => 'Aucun professionnel trouvé avec ce code.'
                ], Response::HTTP_NOT_FOUND);
            }

            $photoFile = ($user ? $user->getAvatar() : null) ?? $professionnel->getPhoto();

            $formattedData = [
                'id' => $professionnel->getId(),
                'nom' => $professionnel->getNom(),
                'prenoms' => $professionnel->getPrenoms(),
                'dateNaissance' => $professionnel->getDateNaissance()?->format('d/m/Y') ?? '—',
                'lieuNaissance' => '—', // Professionnel entity doesn't store birth place
                'contactPersonnel' => $professionnel->getNumber() ?? $professionnel->getEmail() ?? '—',
                'residenceProfessionnelle' => $professionnel->getLieuExercicePro() ?? $professionnel->getQuartier() ?? '—',
                'dateInscription' => $professionnel->getCreatedAt()?->format('d/m/Y') ?? '—',
                'specialite' => $profession ? ($profession->getLibelle() ?? '—') : '—',
                'code' => $professionnel->getCode(),
                'email' => $professionnel->getEmail() ?? ($user ? $user->getEmail() : '—'),
                'civilite' => $professionnel->getCivilite()?->getLibelle() ?? '—',
                'photo' => $photoFile ? $this->getFichierUrl($photoFile, $request) : null
            ];

            return $this->json([
                'statut' => 1,
                'data' => $formattedData
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'statut' => 0,
                'message' => 'Une erreur est survenue : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/code/update', name: 'api_professionnel_old_code_update', methods: ['POST'])]
    #[OA\Tag(name: 'professionnel')]
    public function updateCode(Request $request, ProfessionnelRepository $professionnelRepository, UserRepository $userRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $id = $data['id'] ?? null;
            $newYear = trim($data['year'] ?? '');

            if (!$id || empty($newYear)) {
                return $this->json([
                    'statut' => 0,
                    'message' => 'Informations manquantes.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $professionnel = $professionnelRepository->find($id);
            if (!$professionnel) {
                return $this->json([
                    'statut' => 0,
                    'message' => 'Professionnel introuvable.'
                ], Response::HTTP_NOT_FOUND);
            }

            $oldCode = $professionnel->getCode();
            $newCode = $oldCode;

            if (preg_match('/MS(\d{4})/', $oldCode, $matches)) {
                $newCode = preg_replace('/MS\d{4}/', 'MS' . $newYear, $oldCode);
            } elseif (preg_match('/(19|20)\d{2}/', $oldCode, $matches)) {
                $newCode = str_replace($matches[0], $newYear, $oldCode);
            } else {
                if (str_starts_with($oldCode, 'MS')) {
                    $newCode = 'MS' . $newYear . substr($oldCode, 2);
                } else {
                    $newCode = 'MS' . $newYear . $oldCode;
                }
            }

            $professionnel->setCode($newCode);
            $this->em->persist($professionnel);

            $user = $userRepository->findOneBy(['personne' => $professionnel->getId()]);
            if ($user && $user->getUsername() === $oldCode) {
                $user->setUsername($newCode);
                $this->em->persist($user);
            }

            $this->em->flush();

            $photoFile = ($user ? $user->getAvatar() : null) ?? $professionnel->getPhoto();

            $formattedData = [
                'id' => $professionnel->getId(),
                'nom' => $professionnel->getNom(),
                'prenoms' => $professionnel->getPrenoms(),
                'dateNaissance' => $professionnel->getDateNaissance()?->format('d/m/Y') ?? '—',
                'lieuNaissance' => '—', // Professionnel entity doesn't store birth place
                'contactPersonnel' => $professionnel->getNumber() ?? $professionnel->getEmail() ?? '—',
                'residenceProfessionnelle' => $professionnel->getLieuExercicePro() ?? $professionnel->getQuartier() ?? '—',
                'dateInscription' => $professionnel->getCreatedAt()?->format('d/m/Y') ?? '—',
                'specialite' => $profession ? ($profession->getLibelle() ?? '—') : '—',
                'code' => $newCode,
                'oldCode' => $oldCode,
                'email' => $professionnel->getEmail() ?? ($user ? $user->getEmail() : '—'),
                'civilite' => $professionnel->getCivilite()?->getLibelle() ?? '—',
                'photo' => $photoFile ? $this->getFichierUrl($photoFile, $request) : null
            ];

            return $this->json([
                'statut' => 1,
                'message' => 'Code mis à jour avec succès.',
                'data' => $formattedData
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'statut' => 0,
                'message' => 'Une erreur est survenue lors de la mise à jour : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getFichierUrl(?\App\Entity\Fichier $fichier, Request $request): ?string
    {
        if (!$fichier) {
            return null;
        }
        return $request->getSchemeAndHttpHost() . $fichier->getWebPath();
    }

    private function getMailSubject(string $transition): string
    {
        return match ($transition) {
            'documents_valider'   => 'Validation de vos documents - DEPPS',
            'documents_invalider' => 'Documents non conformes - DEPPS',
            default               => 'Mise à jour de votre dossier - DEPPS',
        };
    }

    private function getNotificationMessage(string $transition): string
    {
        return match ($transition) {
            'documents_valider'   => 'Vos documents d\'ancien professionnel ont été validés.',
            'documents_invalider' => 'Vos documents d\'ancien professionnel ont été invalidés.',
            default               => 'Votre dossier a été mis à jour.',
        };
    }
}
