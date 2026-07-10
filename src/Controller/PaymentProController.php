<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Document;
use App\Entity\DocumentOepTemp;
use App\Entity\DocumentTemporaire;
use App\Entity\Etablissement;
use App\Entity\LibelleGroupe;
use App\Entity\Professionnel;
use App\Entity\TempEtablissement;
use App\Entity\TempProfessionnel;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\DocumentOepTempRepository;
use App\Repository\EtablissementRepository;
use App\Repository\ProfessionRepository;
use App\Repository\ProfessionnelRepository;
use App\Repository\TempProfessionnelRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Service\PaiementProService;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Date;

#[Route('/api/paiement2')]
class PaymentProController extends ApiInterface
{


    #[Route('/historique/{type}', methods: ['GET'])]
    /**
     * liste historique.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiements')]
    // 
    public function index(TransactionRepository $transactionRepository, ProfessionRepository $professionRepository, $type): Response
    {
        try {

            /* {
                "user": {
                    "id": 112,
                    "username": "Doudou DEPPS25059008",
                    "email": "adoudanidani@live.fr",
                    "typeUser": "PROFESSIONNEL",
                    "personne": {
                        "code": "MS10250299.0001",
                        "poleSanitaire": "",
                        "nom": "Doudou",
                        "prenoms": "DANI",
                        "lieuExercicePro": "Adzopé",
                        "email": "adoudanidani@gmail.com",
                        "profession": "rd_kinesithérapie",
                        "number": "0707937156",
                        "quartier": "KOKO",
                        "id": 76,
                        "createdAt": "2025-05-09T13:10:32+02:00"
                    },
                    "createdAt": "2025-05-09T13:10:33+02:00"
                },
                "montant": "15000",
                "reference": "DEPPS250509130852007",
                "reference_channel": "LJV250509P1109412191",
                "channel": "Wave",
                "type": "NOUVELLE DEMANDE",
                "state": 1,
                "typeUser": "professionnel",
                "createdAt": "2025-05-09T13:08:52+02:00"
            }, */

            $transactions = $transactionRepository->getAllTransaction($type);


            $formattedTransactions = array_map(function (Transaction $transaction) use ($professionRepository, $type) {
                $personne = $transaction->getUser()->getPersonne();

                // Cas professionnel
                $profession = $transaction->getUser()->getTypeUser() == "professionnel"
                    ? $personne->getProfession()
                    : null;

                return [
                    "montant" => $transaction->getMontant(),
                    "reference" => $transaction->getReference(),
                    "reference_channel" => $transaction->getReferenceChannel(),
                    "channel" => $transaction->getChannel(),
                    "type" => $transaction->getType(),
                    "state" => $transaction->getState(),
                    "typeUser" => $transaction->getUser()->getTypeUser(),
                    "createdAt" => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
                    "email" => $transaction->getUser()->getEmail(),

                    "user" => $type == "professionnel" ? [
                        // bloc professionnel
                        'profession' => $this->formatEntityProfession($personne->getProfession()),
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
                        "data" => json_decode($transaction->getData() ?? "[]", true),
                        "createdAt" => $personne->getCreatedAt()?->format('Y-m-d H:i:s'),
                    ] : ($type == "etablissement" ? [
                        // bloc établissement
                        "code" => $personne->getCode(),
                        "email" => $personne->getEmail(),
                        "typePersonne" => $personne->getTypePersonne()->getLibelle(),
                        "typeUser" => $transaction->getUser()->getTypeUser(),
                        "nom" => $personne->getTypePersonne()->getLibelle() == "PHYSIQUE" ? $personne->getNom() : "",
                        "denomination" => $personne->getTypePersonne()->getLibelle() == "MORALE" ? $personne->getDenomination() : "",
                        "prenoms" => $personne->getTypePersonne()->getLibelle() == "PHYSIQUE" ? $personne->getPrenoms() : "",
                        "createdAt" => $personne->getCreatedAt()?->format('Y-m-d H:i:s'),
                        "data" => json_decode($transaction->getData() ?? "[]", true),
                    ] : ($type == "admin" ? (

                        $transaction->getUser()->getTypeUser() === "PROFESSIONNEL" ? [
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
                            "data" => json_decode($transaction->getData() ?? "[]", true),
                            "createdAt" => $personne->getCreatedAt()?->format('Y-m-d H:i:s'),
                        ] : [
                            "code" => $personne->getCode(),
                            "email" => $personne->getEmail(),
                            "typePersonne" => $personne->getTypePersonne()->getLibelle(),
                            "typeUser" => $transaction->getUser()->getTypeUser(),
                            "nom" => $personne->getTypePersonne()->getLibelle() == "PHYSIQUE" ? $personne->getNom() : "",
                            "denomination" => $personne->getTypePersonne()->getLibelle() == "MORALE" ? $personne->getDenomination() : "",
                            "prenoms" => $personne->getTypePersonne()->getLibelle() == "PHYSIQUE" ? $personne->getPrenoms() : "",
                            "createdAt" => $personne->getCreatedAt()?->format('Y-m-d H:i:s'),
                            "data" => json_decode($transaction->getData() ?? "[]", true),
                        ]
                    ) : [
                        // fallback
                        "code" => $personne->getCode(),
                        "email" => $personne->getEmail(),
                        "typePersonne" => $personne->getTypePersonne()->getLibelle(),
                    ])),
                ];
            }, $transactions);


            $response = $this->responseData($formattedTransactions, 'group_user_trx', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response([]);
        }

        // On envoie la réponse
        return $response;
    }


    private function formatEntityProfession($entity): ?array
    {
        return $entity ? [
            'libelle' => $entity->getLibelle(),
            'id' => $entity->getId(),
            'code' => $entity->getCode(),
            'montantNouvelleDemande' => $entity->getMontantNouvelleDemande(),
            'montantRenouvellement' => $entity->getMontantRenouvellement(),
        ] : null;
    }
    #[Route('/status/renouvellement/{userId}', methods: ['GET'])]
    /**
     * liste historique.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiements')]
    // 
    public function status(TransactionRepository $transactionRepository, $userId, UserRepository $userRepository, ProfessionRepository $professionRepository): Response
    {
        try {
            $expire = false;
            $etatPro = false;
            $yearDue = 0;
            $finRenouvelement = "";
            $user = $userRepository->find($userId);
            $dernierAbonnement = $transactionRepository->findOneBy(
                ['user' => $userId, 'state' => 1],
                ['createdAt' => 'DESC']
            );
            if ($user->getTypeUser() == "PROFESSIONNEL") {
                $profession = $user->getPersonne()->getProfession();
                $isPayante = false;
                if ($profession) {
                    $montantNouveau = (int) $profession->getMontantNouvelleDemande();
                    $montantRenouv = (int) $profession->getMontantRenouvellement();
                    if ($montantNouveau > 0 || $montantRenouv > 0) {
                        $isPayante = true;
                    }
                }

                if (!$isPayante) {
                    $expire = false;
                    $joursRestants = 0;
                    $expiration = new \DateTime();
                    $etatPro = false;
                } else {
                    $today = new \DateTime();
                    $code = $user->getPersonne()->getCode() ?? '';

                    if (preg_match('/(?<!\d)((?:19|20)\d{2})(?!\d)/', $code, $matches)) {
                        $yearInCode = (int)$matches[1];
                        $currentYear = (int)$today->format('Y');
                        
                        $expire = $yearInCode < $currentYear;
                        $expiration = new \DateTime($yearInCode . '-12-31');
                        
                        if ($expire) {
                            $joursRestants = 0;
                            $yearDue = $currentYear - $yearInCode;
                        } else {
                            $joursRestants = $today->diff($expiration)->days;
                            $yearDue = 0;
                        }
                    } else {
                        // S'il n'a pas encore de code ou que le code ne contient pas d'année
                        $expire = false;
                        $expiration = new \DateTime();
                        $joursRestants = 0;
                        $yearDue = 0;
                    }

                    $etatPro = true;
                }
            } else {
                // ETABLISSEMENT
                $today = new \DateTime();
                $code = $user->getPersonne()->getCode() ?? '';

                if (preg_match('/(?<!\d)((?:19|20)\d{2})(?!\d)/', $code, $matches)) {
                    $yearInCode = (int)$matches[1];
                    $currentYear = (int)$today->format('Y');
                    
                    $expire = $yearInCode < $currentYear;
                    $expiration = new \DateTime($yearInCode . '-12-31');
                    
                    if ($expire) {
                        $joursRestants = 0;
                        $yearDue = $currentYear - $yearInCode;
                    } else {
                        $joursRestants = $today->diff($expiration)->days;
                        $yearDue = 0;
                    }
                } else {
                    // S'il n'a pas de code
                    $expire = false;
                    $expiration = new \DateTime();
                    $joursRestants = 0;
                    $yearDue = 0;
                }
                $joursRestants = max(0, $today->diff($expiration)->days);
                $expire = $expiration < $today;

                $etatPro = true;
            }


            $montantUnitaire = $user->getTypeUser() == "PROFESSIONNEL" ? $user->getPersonne()->getProfession()->getMontantRenouvellement() : null;
            $montantTotal = $user->getTypeUser() === "PROFESSIONNEL" ? (int) $montantUnitaire * (int) $yearDue : 0; // ou calcul spécifique à ETABLISSEMENT

            $transactions = [
                'expire' => $expire,
                'etatPro' => $etatPro,
                'montantTotal' => $montantTotal,
                'montantUnitaire' => (int)$montantUnitaire,
                'yearDue' => $yearDue,
                'date_expiration' => $expiration->format('Y-m-d'),
                'jours_restants' => $joursRestants,
            ];


            $response = $this->responseData($transactions, 'group_user_trx', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/info/transaction/{transactionId}', methods: ['GET'])]
    /**
     * liste historique.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiements')]
    // 
    public function indexInfoTransaction(TransactionRepository $transactionRepository, PaiementProService $paiementService, ProfessionnelRepository $professionnelRepository, $transactionId): Response
    {
        try {
            $transaction = $transactionRepository->findOneBy(['reference' => $transactionId]);

            if (!$transaction) {
                return $this->json(['data' => ['state' => 0, 'message' => 'Transaction introuvable']], 404);
            }

            // Si déjà validée ou échouée, on retourne directement sans rappeler MTN
            if ($transaction->getState() == 1) {
                return $this->json(['data' => ['state' => 1, 'message' => 'Paiement déjà validé']]);
            }
            if ($transaction->getState() == -1) {
                return $this->json(['data' => ['state' => -1, 'message' => 'Paiement échoué']]);
            }

            // Vérifier le statut via l'API MTN MoMo
            $statusResult = $paiementService->verifierStatutPaiementPro($transactionId);

          
            if (!isset($statusResult['status'])) {
                return $this->json(['data' => ['state' => 0, 'message' => 'Statut inconnu, en attente']]);
            }

            $momoStatus = $statusResult['status'];

            if ($momoStatus === 'SUCCESSFUL') {
                $transaction->setState(1);
                $transaction->setUpdatedAt();
                $transactionRepository->add($transaction, true);

                // ── Mise à jour de l'année dans le code du professionnel ──────────
                // Récupérer le professionnel lié à la transaction via l'utilisateur
                $user = $transaction->getUser();
                if ($user && $user->getPersonne() !== null) {
                    $personne = $user->getPersonne();
                    $professionnel = $professionnelRepository->find($personne->getId());

                    if ($professionnel && $professionnel->getCode() !== null) {
                        $oldCode = $professionnel->getCode();
                        $newCode = $oldCode;

                        // Lire le nombre d'années directement depuis le champ dédié
                        // Fallback sur getData() JSON pour la compatibilité avec les anciennes transactions
                        $yearsToPay = $transaction->getNbreAnnee()
                            ?? (int) (json_decode($transaction->getData() ?? '{}', true)['yearsToPay'] ?? 1);
                        if ($yearsToPay < 1) $yearsToPay = 1;

                        // Regex universelle : trouve la première année (19xx/20xx) dans le code
                        // Stratégie : extraire l'année DU CODE et y ajouter yearsToPay
                        // MS2024APDENT1725.0006 + 2 ans → MS2026APDENT1725.0006
                        // MSNI2023OPTLO2989.0032 + 1 an  → MSNI2024OPTLO2989.0032
                        if (preg_match('/(?<!\d)((?:19|20)\d{2})(?!\d)/', $oldCode, $matches, PREG_OFFSET_CAPTURE)) {
                            $yearFound  = (int) $matches[1][0];
                            $yearPos    = $matches[1][1];
                            $newYear    = (string) ($yearFound + $yearsToPay);
                            $newCode    = substr($oldCode, 0, $yearPos)
                                        . $newYear
                                        . substr($oldCode, $yearPos + strlen((string) $yearFound));
                        } else {
                            // Fallback : pas d'année trouvée → insérer l'année calculée depuis now
                            $baseYear = (int) date('Y');
                            $newYear  = (string) ($baseYear + $yearsToPay - 1);
                            if (preg_match('/^([A-Za-z]+)(.*)$/', $oldCode, $parts)) {
                                $newCode = $parts[1] . $newYear . $parts[2];
                            }
                        }

                        if ($newCode !== $oldCode) {
                            $professionnel->setCode($newCode);
                            $professionnelRepository->add($professionnel, true);
                        }
                    }
                }
                // ─────────────────────────────────────────────────────────────────

                return $this->json(['data' => ['state' => 1, 'message' => 'Paiement confirmé avec succès']]);
            } elseif ($momoStatus === 'FAILED') {
                $transaction->setState(-1);
                $transaction->setUpdatedAt();
                $transactionRepository->add($transaction, true);
                return $this->json(['data' => ['state' => -1, 'message' => 'Paiement échoué']]);
            } else {
                // PENDING ou autre
                return $this->json(['data' => ['state' => 0, 'message' => 'Paiement en attente de validation MTN', 'momoStatus' => $momoStatus]]);
            }
        } catch (\Exception $exception) {
            return $this->json(['data' => ['state' => 0, 'message' => $exception->getMessage()]], 500);
        }
    }
    #[Route('/find/one/transaction/{id}', methods: ['GET'])]
    /**
     * liste historique.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiements')]
    // 
    public function indexFindOneTransaction(TransactionRepository $transactionRepository, $id): Response
    {
        try {

            $transactions = $transactionRepository->find($id);

            $response = $this->responseData($transactions, 'group_user_trx', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/info/transaction/last/transaction/{userId}', methods: ['GET'])]
    /**
     * liste historique.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiements')]
    // 
    public function indexInfoTransactionLastTransaction(TransactionRepository $transactionRepository, $userId): Response
    {
        try {

            $transactions = $transactionRepository->findLastTransactionByUser($userId);

            $response = $this->responseData($transactions, 'group_user_trx', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("yuyuyu");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/info/transaction/last/transaction/formatter/{userId}', methods: ['GET'])]
    /**
     * liste historique.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiements')]
    // 
    public function indexInfoTransactionLastTransactionFormatter(TransactionRepository $transactionRepository, ProfessionRepository $professionRepository, $userId): Response
    {
        try {

            $user = $this->userRepository->find($userId);
            if (!$user) {
                // Si l'ID fourni est l'ID de la personne au lieu de l'user
                $user = $this->userRepository->findOneBy(['personne' => $userId]);
            }

            if (!$user) {
                return $this->responseData(null, 'group_user_trx', ['Content-Type' => 'application/json']);
            }

            $transactions = $transactionRepository->findLastTransactionByUser($user->getId());

            if (!$transactions) {
                return $this->responseData(null, 'group_user_trx', ['Content-Type' => 'application/json']);
            }

            $personne = $transactions->getUser()->getPersonne();
            $profession = null;
            if ($personne instanceof Professionnel) {
                $profession = $personne->getProfession();
            }

            $personneData = [
                'id' => $personne->getId(),
                "status" => $personne->getStatus(),
                "createdAt" => $personne->getCreatedAt() ? $personne->getCreatedAt()->format('Y-m-d H:i:s') : null,
            ];

            if ($personne instanceof Professionnel) {
                $personneData = array_merge($personneData, [
                    'profession' => $profession ? [
                        'libelle' => $profession->getLibelle() ?? "",
                        'id' => $profession->getId(),
                        'code' => $profession->getCode(),
                        'montantNouvelleDemande' => $profession->getMontantNouvelleDemande(),
                        'montantRenouvellement' => $profession->getMontantRenouvellement(),
                    ] : null,
                    "code" => $personne->getCode(),
                    "poleSanitaire" => $personne->getPoleSanitaire(),
                    "nom" => $personne->getNom(),
                    "prenoms" => $personne->getPrenoms(),
                    "lieuExercicePro" => $personne->getLieuExercicePro(),
                    "email" => $personne->getEmail(),
                    "number" => $personne->getNumber(),
                    "quartier" => $personne->getQuartier(),
                ]);
            } elseif ($personne instanceof Etablissement) {
                $personneData = array_merge($personneData, [
                    "denomination" => $personne->getDenomination(),
                    "nom" => $personne->getNom(),
                    "prenoms" => $personne->getPrenoms(),
                    "code" => $personne->getCode(),
                    "email" => $personne->getEmail() ?? $personne->getEmailAutre(),
                    "telephone" => $personne->getTelephone(),
                    "adresse" => $personne->getAdresse(),
                ]);
            }

            $data = [
                'user' => [
                    "id" => $transactions->getUser()->getId(),
                    "username" => $transactions->getUser()->getUsername(),
                    "email" => $transactions->getUser()->getUserIdentifier(),
                    "typeUser" => $transactions->getUser()->getTypeUser(),
                    'personne' => $personneData,
                ],
                "montant" => $transactions->getMontant(),
                "reference" => $transactions->getReference(),
                "reference_channel" => $transactions->getReferenceChannel(),
                "channel" => $transactions->getChannel(),
                "type" => $transactions->getType(),
                "state" => $transactions->getState(),
                "typeUser" => $transactions->getUser()->getTypeUser(),
                "createdAt" => $transactions->getCreatedAt() ? $transactions->getCreatedAt()->format('Y-m-d H:i:s') : null,
                "email" => $transactions->getUser()->getEmail(),
                "numero" => $transactions->getNumero(),
            ];


            $response = $this->responseData($data, 'group_user_trx', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/historique/by/user/{userId}', methods: ['GET'])]
    /**
     * liste historique.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiements')]
    // 
    public function indexByUser(TransactionRepository $transactionRepository, $userId): Response
    {
        try {

            $transactions = $transactionRepository->getAllTransactionByUser($userId);

            $response = $this->responseData($transactions, 'group_user_trx', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/get/transaction/{trxReference}', methods: ['GET'])]
    /**
     * liste historique.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'paiements')]
    // 
    public function getTransaction(TransactionRepository $transactionRepository, PaiementProService $paiementProService, $trxReference): Response
    {
        $transaction = $transactionRepository->findOneBy(['reference' => $trxReference]);

        if (!$transaction) {
            return $this->json(["data" => false, "error" => "Transaction introuvable"]);
        }

        $momoResult = null;

        // Si encore en attente, interroger MTN pour mettre à jour le state
        if ($transaction->getState() == 0) {
            $momoResult = $paiementProService->verifierStatutPaiementPro($trxReference);
            // Re-fetch après la mise à jour éventuelle
            $transaction = $transactionRepository->findOneBy(['reference' => $trxReference]);
        }

        $state = $transaction->getState();

        if ($state == 1) {
            return $this->json(["data" => true]);
        }

        if ($state == -1) {
            return $this->json([
                "data" => false,
                "failed" => true,
                "reason" => $momoResult['reason'] ?? null,
                "message" => "Paiement refusé par MTN MoMo : " . ($momoResult['reason'] ?? 'FAILED'),
            ]);
        }

        // state == 0 : toujours en attente
        return $this->json([
            "data" => false,
            "failed" => false,
            "status" => $momoResult['status'] ?? 'PENDING',
            "mtn" => $momoResult,
        ]);
    }


    public function numero()
    {

        $query = $this->em->createQueryBuilder();
        $query->select("count(a.id)")
            ->from(Transaction::class, 'a');

        $nb = $query->getQuery()->getSingleScalarResult();
        if ($nb == 0) {
            $nb = 1;
        } else {
            $nb = $nb + 1;
        }
        return ('DEPPS' . date("y", strtotime("now")) . date("m", strtotime("now")) . date("j", strtotime("now")) . str_pad($nb, 3, '0', STR_PAD_LEFT));
    }



    #[Route('/info-paiement', name: 'webhook_paiement2',  methods: ['POST'])]
    /**
     * Il s'agit de la webhook pour les paiement.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "codePaiement", type: "string"),
                    new OA\Property(property: "referencePaiement", type: "string"),
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "moyenPaiement", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'paiements')]

    public function webHook(Request $request, TransactionRepository $transactionRepository, TempProfessionnelRepository $tempProfessionnelRepository, SessionInterface $session, PaiementProService $paiementService): Response
    {
        $response = $paiementService->methodeWebHook($request);


        return  $this->responseData($response, 'group1', ['Content-Type' => 'application/json']);
    }
    #[Route('/info-paiement-oep', name: 'webhook_paiement_oep2',  methods: ['POST'])]
    /**
     * Il s'agit de la webhook pour les paiement.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "codePaiement", type: "string"),
                    new OA\Property(property: "referencePaiement", type: "string"),
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "moyenPaiement", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'paiements')]

    public function webHookOep(Request $request,  PaiementProService $paiementService): Response
    {
        $response = $paiementService->methodeWebHookOep($request);


        return  $this->responseData($response, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/info-paiement-renouvellement', name: 'webhook_paiement_renouvellement2',  methods: ['POST'])]
    /**
     * Il s'agit de la webhook pour les paiement.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "codePaiement", type: "string"),
                    new OA\Property(property: "referencePaiement", type: "string"),
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "moyenPaiement", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'paiements')]

    public function webHookRenouvellement(Request $request, TransactionRepository $transactionRepository, TempProfessionnelRepository $tempProfessionnelRepository, SessionInterface $session, PaiementProService $paiementService): Response
    {
        $response = $paiementService->methodeWebHookRenouvellement($request);


        return  $this->responseData($response, 'group1', ['Content-Type' => 'application/json']);
    }




    #[Route('/initiation/transaction',  methods: ['POST'])]
    /**
     * Permet de créer une transaction et lui on prendra sa reference pour initier le paiement dans code paiement.
     */
    #[OA\Post(
        summary: "",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "user", type: "string"),
                    new OA\Property(property: "montant", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'paiements')]

    public function create(Request $request, UserRepository $userRepository, TransactionRepository $transactionRepository): Response
    {

        $data = json_decode($request->getContent(), true);

        $transaction = new Transaction();

        $transaction->setChannel("");
        $transaction->setReference($this->numero());
        $transaction->setMontant($data['montant']);
        $transaction->setNumero($data['numero'] ?? null);
        $transaction->setReferenceChannel("");
        $transaction->setUser($userRepository->find($data['user']));
        $transaction->setType("Renouvellement");
        $transaction->setState(1);
        $transaction->setCreatedBy($userRepository->find($data['user']));
        $transaction->setUpdatedBy($userRepository->find($data['user']));
        $transaction->setState(0);
        $transaction->setCreatedAtValue(new Date());
        $transaction->setUpdatedAt(new Date());
        $transactionRepository->add($transaction, true);



        $response = $this->responseData($transaction, 'group_user', ['Content-Type' => 'application/json']);

        return $response;
    }



    #[Route('/paiement', name: 'paiement2', methods: ['POST'])]
    /**
     * Permet de faire le âiement
     */
    #[OA\Post(
        summary: "",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nom", type: "string"),
                    new OA\Property(property: "prenoms", type: "string"),
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "numero", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'paiements')]

    public function doPaiement(Request $request, PaiementProService $paiementService)
    {


        /*   dd($request); */

        $createTransactionData = $paiementService->traiterPaiement($request);

        if (isset($createTransactionData['code']) && $createTransactionData['code'] !== 200) {
            return $this->json([
                'code' => $createTransactionData['code'],
                'message' => $createTransactionData['error'] ?? 'Erreur lors de l\'initiation du paiement'
            ], $createTransactionData['code'] == 500 ? 500 : 400);
        }

        if (!isset($createTransactionData['type'])) {
            return $this->json([
                'code' => 400,
                'message' => 'Type de paiement manquant'
            ], 400);
        }

        if ($createTransactionData['type'] == "professionnel") {
            $resultat = $this->createProfessionnelTemp($request, $createTransactionData);
        } else {
            $resultat = $this->createEtablissemntTemp($request, $createTransactionData);
        }

        return $resultat;
    }

    #[Route('/inite/oep', name: 'initie_ope2', methods: ['POST'])]
    /**
     * Permet d'initier l'ope
     */
    #[OA\Post(
        summary: "Permet d'initier l'ope",
        description: "Permet de créer un nouvel établissement avec toutes les informations requises et documents joints.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [


                        new OA\Property(property: "etablissement", type: "string"),
                        new OA\Property(property: "email", type: "string"),
                        new OA\Property(property: "user", type: "string"),
                        new OA\Property(property: "niveauIntervention", type: "string"),
                        new OA\Property(
                            property: "documents",
                            type: "array",
                            items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "libelle", type: "string"),
                                    new OA\Property(property: "path", type: "string", format: "binary"),
                                    new OA\Property(property: "libelleGroupe", type: "string")
                                ]
                            ),
                        ),
                    ],
                    type: "object"
                )
            )
        ),
        responses: []
    )]
    #[OA\Tag(name: 'paiements')]
    public function initieOpe(Request $request, PaiementProService $paiementService, EtablissementRepository $etablissementRepository, DocumentOepTempRepository $documentOepTempRepository)
    {
        $names = 'document_' . '01';
        $filePrefix  = str_slug($names);
        $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
        // $etablissement = $etablissementRepository->find($request->get('perdsonneId'));
        $createTransactionData = $paiementService->traiterPaiementOpe($request);

        if ($createTransactionData) {
            $documents = $request->get('documents');
            $uploadedFiles = $request->files->get('documents');

            foreach ($documents as $index => $doc) {

                $newDocument = new DocumentOepTemp();
                $newDocument->setLibelle($doc['libelle'])
                    ->setReference($createTransactionData['reference'])
                    ->setEtablissement($request->get('etablissement'))
                    ->setLibelleGroupe($this->em->getRepository(LibelleGroupe::class)->find($doc['libelleGroupe'] ?? null));

                if (isset($uploadedFiles[$index])) {


                    if (!empty($uploadedFiles[$index]['path'])) {
                        $uploadedFile = $uploadedFiles[$index]['path'];
                        $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
                        if ($fichier) {
                            $newDocument->setPath($fichier);
                        }
                    }
                }

                $documentOepTempRepository->add($newDocument, true);
            }
        }


        return $this->json($createTransactionData);
    }

    #[Route('/renouvellement', name: 'renouvellement2', methods: ['POST'])]
    /**
     * Permet de faire le âiement
     */
    #[OA\Post(
        summary: "",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nom", type: "string"),
                    new OA\Property(property: "prenoms", type: "string"),
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "numero", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'paiements')]
    public function doRenouvellement(Request $request, PaiementProService $paiementService)
    {
        $createTransactionData = $paiementService->traiterPaiementRenouvellement($request);
        return $this->json(
            [
                'message' => 'Professionnel bien enregistré',
                'data' => $createTransactionData
            ]
        );
    }


    public function createProfessionnelTemp(Request $request, $data)
    {

        $names = 'document_' . '01';
        $filePrefix  = str_slug($names);
        $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
        $professionnel = new TempProfessionnel();

        //etape 1

        $professionnel->setPassword($request->get('password'));
        $professionnel->setCode($request->get('code'));
        $professionnel->setEmail($request->get('email'));
        $professionnel->setUsername($request->get('nom') . " " . $this->numero());

        // etatpe 2

        $professionnel->setPoleSanitaire($request->get('poleSanitaire'));
        $professionnel->setRegion($request->get('region'));
        $professionnel->setDistrict($request->get('district'));
        $professionnel->setVille($request->get('ville'));
        $professionnel->setCommune($request->get('commune'));
        $professionnel->setQuartier($request->get('quartier'));

        $professionnel->setNom($request->get('nom'));
        $professionnel->setProfessionnel($request->get('professionnel'));
        $professionnel->setPrenoms($request->get('prenoms'));
        $professionnel->setLieuExercicePro($request->get('lieuExercicePro'));
        $professionnel->setSpecialiteAutre($request->get('specialiteAutre'));

        // etatpe 3
        $professionnel->setStatusPro($request->get('statusPro'));
        $professionnel->setTypeDiplome($request->get('typeDiplome'));

        $professionnel->setProfession($request->get('profession'));
        $professionnel->setEmailAutre($request->get('emailAutre'));
        $professionnel->setCivilite($request->get('civilite'));
        $professionnel->setEmailPro($request->get('emailPro'));
        $professionnel->setDateDiplome($request->get('dateDiplome'));
        $professionnel->setDateNaissance($request->get('dateNaissance'));
        $professionnel->setNumber($request->get('numero'));
        $professionnel->setLieuDiplome($request->get('lieuDiplome'));
        $professionnel->setLieuObtentionDiplome($request->get('lieuObtentionDiplome'));
        $professionnel->setOrigineDiplome($request->get('origineDiplome'));
        $professionnel->setNationate($request->get('nationalite'));
        $professionnel->setSituation($request->get('situation'));
        $professionnel->setDatePremierDiplome(new DateTimeImmutable($request->get('datePremierDiplome')));
        $professionnel->setPoleSanitairePro($request->get('poleSanitairePro'));
        $professionnel->setDiplome($request->get('diplome'));
        $professionnel->setSituationPro($request->get('situationPro'));
        $professionnel->setAppartenirOrganisation($request->get('appartenirOrganisation'));
        $professionnel->setAppartenirOrdre($request->get('appartenirOrdre'));

        if ($request->get('appartenirOrganisation') == "oui") {
            $professionnel->setOrganisationNom($request->get('organisationNom'));
        }

        if ($request->get('appartenirOrdre') == "oui") {
            $professionnel->setNumeroInscription($request->get('numeroInscription'));
            $professionnel->setOrdre($request->get('ordre'));
        }


        $professionnel->setReference($data['reference']);
        $professionnel->setTypeUser(User::TYPE['PROFESSIONNEL']);

        // etatpe 4

        $uploadedPhoto = $request->files->get('photo');
        $uploadedCasier = $request->files->get('casier');
        $uploadedCni = $request->files->get('cni');
        $uploadedDiplome = $request->files->get('diplomeFile');
        $uploadedCertificat = $request->files->get('certificat');
        $uploadedCv = $request->files->get('cv');


        if ($uploadedPhoto) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedPhoto, self::UPLOAD_PATH);
            if ($fichier) {
                $professionnel->setPhoto($fichier);
            }
        }
        if ($uploadedCasier) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedCasier, self::UPLOAD_PATH);
            if ($fichier) {
                $professionnel->setCasier($fichier);
            }
        }
        if ($uploadedCni) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedCni, self::UPLOAD_PATH);
            if ($fichier) {
                $professionnel->setCni($fichier);
            }
        }
        if ($uploadedDiplome) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedDiplome, self::UPLOAD_PATH);
            if ($fichier) {
                $professionnel->setDiplomeFile($fichier);
            }
        }
        if ($uploadedCertificat) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedCertificat, self::UPLOAD_PATH);
            if ($fichier) {
                $professionnel->setCertificat($fichier);
            }
        }
        if ($uploadedCv) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedCv, self::UPLOAD_PATH);
            if ($fichier) {
                $professionnel->setCv($fichier);
            }
        }

        // etatpe 5




        $errorResponse = $this->errorResponse($professionnel);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $this->em->persist($professionnel);
            $this->em->flush();
        }


        return  $this->json([
            'message' => 'Professionnel bien enregistré',
            'data' => $data
        ]);
    }

    //CREATION DU TEMP ETABLISSEMENT
    public function createEtablissemntTemp(Request $request, $data)
    {

        $names = 'document_' . '01';
        $filePrefix  = str_slug($names);
        $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
        $etablissement = new TempEtablissement();


        $etablissement->setPassword($request->get('password'));
        $etablissement->setEmail($request->get('email'));
        $etablissement->setUsername($request->get('nomEntreprise') . " " . $this->numero());

        $etablissement->setTypePersonne($request->get('typePersonne'));
        $etablissement->setNiveauIntervention($request->get('niveauIntervention'));

        $etablissement->setReference($data['reference']);
        $etablissement->setTypeUser(User::TYPE['ETABLISSEMENT']);
        $etablissement->setNom($request->get('nom'));
        $etablissement->setPrenoms($request->get('prenoms'));
        $etablissement->setDenomination($request->get('denomination'));
        $etablissement->setEmailAutre($request->get('emailAutre'));
        $etablissement->setBp($request->get('bp'));
        $etablissement->setAdresse($request->get('adresse'));
        $etablissement->setNomRepresentant($request->get('nomRepresentant'));
        $etablissement->setTelephone($request->get('telephone'));
        $etablissement->setTypeSociete($request->get('typeSociete'));

        // Structure
        $etablissement->setTypeDemandeEtablissement($request->get('typeDemandeEtablissement'));
        $etablissement->setTypeEtablissement($request->get('typeEtablissement'));
        $etablissement->setNatureEtablissement($request->get('natureEtablissement'));
        $etablissement->setTypeOrganisation($request->get('typeOrganisation'));
        $etablissement->setAccordMinistere($request->get('accordMinistere'));
        $etablissement->setDateValiditeAccord($request->get('dateValiditeAccord'));

        // Adresses et Contacts
        $etablissement->setRegion($request->get('region'));
        $etablissement->setDistrict($request->get('district'));
        $etablissement->setVilleVillage($request->get('villeVillage'));
        $etablissement->setCommune($request->get('commune'));
        $etablissement->setQuartier($request->get('quartier'));
        $etablissement->setZoneSecteur($request->get('zoneSecteur'));
        $etablissement->setVillaImmeubleEtagePorte($request->get('villaImmeubleEtagePorte'));
        $etablissement->setIlotNumero($request->get('ilotNumero'));
        $etablissement->setLotNumero($request->get('lotNumero'));
        $etablissement->setRueAvenue($request->get('rueAvenue'));
        $etablissement->setPointDeRepere($request->get('pointDeRepere'));
        $etablissement->setAdresseElectronique($request->get('adresseElectronique'));
        $etablissement->setTelephoneFixe($request->get('telephoneFixe'));
        $etablissement->setWhatsapp($request->get('whatsapp'));
        $etablissement->setTelephoneMobile($request->get('telephoneMobile'));
        $etablissement->setTelephoneAutre($request->get('telephoneAutre'));
        $etablissement->setAdressePostale($request->get('adressePostale'));

        // Personne physique
        $etablissement->setCivilite($request->get('civilite'));
        $etablissement->setProfession($request->get('profession'));
        $etablissement->setCniNumero($request->get('cniNumero'));
        $etablissement->setWhatsappPersonnel($request->get('whatsappPersonnel'));

        // Personne morale / Représentant
        $etablissement->setStatutJuridique($request->get('statutJuridique'));
        $etablissement->setRepresentantCivilite($request->get('representantCivilite'));
        $etablissement->setRepresentantQualite($request->get('representantQualite'));
        $etablissement->setRepresentantCni($request->get('representantCni'));
        $etablissement->setRepresentantTelephone($request->get('representantTelephone'));
        $etablissement->setRepresentantWhatsapp($request->get('representantWhatsapp'));
        $etablissement->setRepresentantEmail($request->get('representantEmail'));

        // Responsable médicolégal
        $etablissement->setResponsableCivilite($request->get('responsableCivilite'));
        $etablissement->setResponsableNom($request->get('responsableNom'));
        $etablissement->setResponsabiliteMedicolegale($request->get('responsabiliteMedicolegale'));
        $etablissement->setResponsableProfession($request->get('responsableProfession'));
        $etablissement->setResponsableDiplome($request->get('responsableDiplome'));
        $etablissement->setResponsableSpecialite($request->get('responsableSpecialite'));
        $etablissement->setResponsableNiveauFormation($request->get('responsableNiveauFormation'));
        $etablissement->setResponsableStatutAdministratif($request->get('responsableStatutAdministratif'));
        $etablissement->setResponsableEmail($request->get('responsableEmail'));
        $etablissement->setResponsableTelephone($request->get('responsableTelephone'));
        $etablissement->setResponsableWhatsapp($request->get('responsableWhatsapp'));
        $etablissement->setResponsableNumeroOrdre($request->get('responsableNumeroOrdre'));
        $etablissement->setResponsableCni($request->get('responsableCni'));

        // Enregistrement / Certificat / Horaires
        $etablissement->setAnneeCreation($request->get('anneeCreation'));
        $etablissement->setEnregistreeDepps($request->get('enregistreeDepps'));
        $etablissement->setNumeroEnregistrement($request->get('numeroEnregistrement'));
        $etablissement->setOrganismeEnregistrement($request->get('organismeEnregistrement'));
        $etablissement->setAnneeAutorisation($request->get('anneeAutorisation'));
        $etablissement->setACertificatConformite($request->get('aCertificatConformite'));
        $etablissement->setDateValiditeCertificat($request->get('dateValiditeCertificat'));
        $etablissement->setHoraireOuverture($request->get('horaireOuverture'));
        $etablissement->setAutreHoraireOuverture($request->get('autreHoraireOuverture'));

        // Contrôle Qualité et Services
        $etablissement->setAAccreditation($request->get('aAccreditation'));
        $etablissement->setEngagementProcessusAccreditation($request->get('engagementProcessusAccreditation'));
        $etablissement->setCertificationQualite($request->get('certificationQualite'));
        $etablissement->setAutresCertification($request->get('autresCertification'));
        $services = $request->get('services');
        if (is_array($services)) {
            $etablissement->setServices(json_encode($services));
        }


        $documents = $request->get('documents');
        $uploadedFiles = $request->files->get('documents');

        if ($documents) {
            foreach ($documents as $index => $doc) {

                $newDocument = new DocumentTemporaire();
                $newDocument->setLibelle($doc['libelle'])
                    ->setLibelleGroupe($this->em->getRepository(LibelleGroupe::class)->find($doc['libelleGroupe'] ?? null));

                if (isset($uploadedFiles[$index]) && !empty($uploadedFiles[$index]['path'])) {
                    $uploadedFile = $uploadedFiles[$index]['path'];
                    $docPrefix = str_slug('document_' . ($index + 1));
                    $fichier = $this->utils->sauvegardeFichier($filePath, $docPrefix, $uploadedFile, self::UPLOAD_PATH);
                    if ($fichier) {
                        $newDocument->setPath($fichier);
                    }
                }

                $etablissement->addDocumentTemporaire($newDocument);
            }
        } else {
            return $this->errorResponse($etablissement, 'pas de document!');
        }




        /* 
        $libelles = $request->get('documents'); // Récupère les libellés
        $uploadedDocuments = $request->files->get('documents'); // Récupère les fichiers

        if ($uploadedDocuments) {
            foreach ($uploadedDocuments as $key => $uploadedDocument) {
                $uploadedPhoto = $uploadedDocument['path'] ?? null;
                $libelle = $libelles[$key]['libelle'] ?? null;
                $libelleGroupe = $libelles[$key]['libelleGroupe'] ?? null;

                if ($uploadedPhoto) {
                    $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedPhoto, self::UPLOAD_PATH);
                    if ($fichier) {
                        $document = new DocumentTemporaire();
                        $document->setPath($fichier);
                        $document->setLibelle($libelle);
                        $document->setLibelleGroupe($this->em->getRepository(LibelleGroupe::class)->find($libelleGroupe));
                        $etablissement->addDocumentTemporaire($document);
                    }
                }
            }
        }
 */

        $errorResponse = $this->errorResponse($etablissement);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $this->em->persist($etablissement);
            $this->em->flush();
        }

        return  $this->json([
            'message' => 'Professionnel bien enregistré',
            'data' => $data
        ]);
    }
}
