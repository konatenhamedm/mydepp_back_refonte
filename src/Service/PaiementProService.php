<?php


namespace App\Service;

use App\Controller\FileTrait;
use App\Entity\TempProfessionnel;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\ProfessionnelRepository;
use App\Repository\TempProfessionnelRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaiementProService
{ 
    
    use FileTrait;
    public function __construct(
        private TransactionRepository $transactionRepository,
        private UserRepository $userRepository,
        private HttpClientInterface $httpClient,
        private Utils $utils,
        private ProfessionnelRepository $professionnelRepository,
        private EntityManagerInterface $em,
        private TempProfessionnelRepository $tempProfessionnelRepository,
        private PaiementService $paiementService
    ) {}

    /**
     * Initie un paiement professionnel via Momo
     */
    public function initierPaiementPro(array $data): array
    {
        $username = $_ENV['MOMO_USERNAME'];
        $password = $_ENV['MOMO_PASSWORD'];
        $basicToken = base64_encode("$username:$password");
        $momoPrimaryKey = $_ENV['MOMO_PRIMARY_KEY'];
        $momoSubscriptionKey = $_ENV['MOMO_SUBSCRIPTION_KEY'] ;

        // Obtenir le token
        $tokenResponse = $this->httpClient->request('POST', 'https://proxy.momoapi.mtn.com/collection/token/', [
            'headers' => [
                'Authorization' => "Basic $basicToken",
                'Cache-Control' => 'no-cache',
                'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
            ],
        ]);
        if ($tokenResponse->getStatusCode() !== 200) {
            return ['error' => 'Erreur lors de la récupération du token'];
        }
        $token = $tokenResponse->toArray()['access_token'];

        $amount = $data['amount'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $userId = $data['user'] ?? null;
        if (!$amount || !$phoneNumber || !$userId) {
            return ['error' => 'Montant, numéro de téléphone et user requis'];
        }
        $myUuid = Uuid::uuid4()->toString();
        $paymentBody = [
            'amount' => (string) $amount,
            'currency' => 'XOF',
            'externalId' => $myUuid,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => "225$phoneNumber",
            ],
            'payerMessage' => 'string',
            'payeeNote' => 'string',
        ];
        $paymentResponse = $this->httpClient->request(
            'POST',
            'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay',
            [
                'headers' => [
                    'Authorization' => "Bearer $token",
                    'X-Callback-Url' => 'https://webhook.site/#!/view/3b1651b5-677b-4034-8c6a-e37ce123869e',
                    'X-Reference-Id' => $myUuid,
                    'X-Target-Environment' => 'mtnivorycoast',
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                    'Ocp-Apim-Subscription-Key' => $momoSubscriptionKey,
                ],
                'json' => $paymentBody,
            ]
        );
        if (!in_array($paymentResponse->getStatusCode(), [200, 202])) {
            return ['error' => 'Erreur lors de l\'initiation du paiement'];
        }
        // Enregistrer la transaction
        $transaction = new Transaction();
        $transaction->setUser($this->userRepository->find($userId));
        $transaction->setChannel('momo');
        $transaction->setReference($myUuid);
        $transaction->setMontant($amount);
        $transaction->setReferenceChannel($myUuid);
        $transaction->setType('PAIEMENT MOMO PRO');
        $transaction->setTypeUser('professionnel');
        $transaction->setState(0);
        $transaction->setCreatedAtValue();
        $transaction->setUpdatedAt();
        $this->transactionRepository->add($transaction, true);
        return [
            'success' => true,
            'message' => 'Paiement initié avec succès',
            'referenceId' => $myUuid,
        ];
    }

    /**
     * Vérifie le statut d'un paiement professionnel et met à jour la transaction
     */
    public function verifierStatutPaiementPro(string $referenceId): array
    {
        $username = $_ENV['MOMO_USERNAME'];
        $password = $_ENV['MOMO_PASSWORD'];
        $basicToken = base64_encode("$username:$password");
        $momoPrimaryKey = $_ENV['MOMO_PRIMARY_KEY'] ?? '';
        // Obtenir le token
        $tokenResponse = $this->httpClient->request('POST', 'https://proxy.momoapi.mtn.com/collection/token/', [
            'headers' => [
                'Authorization' => "Basic $basicToken",
                'Cache-Control' => 'no-cache',
                'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
            ],
        ]);
        if ($tokenResponse->getStatusCode() !== 200) {
            return ['error' => 'Erreur lors de la récupération du token'];
        }
        $token = $tokenResponse->toArray()['access_token'];
        // Vérifier le statut
        $statusResponse = $this->httpClient->request(
            'GET',
            "https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay/$referenceId",
            [
                'headers' => [
                    'Authorization' => "Bearer $token",
                    'X-Target-Environment' => 'mtnivorycoast',
                    'Cache-Control' => 'no-cache',
                    'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
                ],
            ]
        );
        if ($statusResponse->getStatusCode() !== 200) {
            return ['error' => 'Erreur lors de la vérification du statut'];
        }
        $statusData = $statusResponse->toArray();
        $transaction = $this->transactionRepository->findOneBy(['reference' => $referenceId]);
        if ($transaction) {
            
            if (($statusData['status'] ?? null) !== 'FAILED' && ($statusData['status'] ?? null) !== 'PENDING') {
                
                $response = $transaction->getTypeUser() == "professionnel" ?  $this->paiementService->updateProfessionnel($referenceId) :  null;
            }


            if ($response) {
                if ($transaction->getTypeUser() == "professionnel") {
                    $temp =  $this->tempProfessionnelRepository->findOneBy(['reference' => $referenceId]);
                    $this->tempProfessionnelRepository->remove($temp, true);
                } else {
                   // $temp =  $this->tempEtablissementRepository->findOneBy(['reference' => $data['codePaiement']]);
                   // $this->tempEtablissementRepository->remove($temp, true);
                }
            }



        }
        return [
            'success' => true,
            'message' => 'Statut vérifié',
            'status' => $statusData['status'] ?? null,
            'reason' => $statusData['reason'] ?? null,
            'data' => $statusData,
        ];
    }

    public function getMomoToken(): ?string
    {
        $username = $_ENV['MOMO_USERNAME'];
        $password = $_ENV['MOMO_PASSWORD'];
        $basicToken = base64_encode("$username:$password");
        $momoPrimaryKey = $_ENV['MOMO_PRIMARY_KEY'] ?? '';

        try {
            $tokenResponse = $this->httpClient->request('POST', 'https://proxy.momoapi.mtn.com/collection/token/', [
                'headers' => [
                    'Authorization' => "Basic $basicToken",
                    'Cache-Control' => 'no-cache',
                    'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
                ],
            ]);

            if ($tokenResponse->getStatusCode() !== 200) {
                return null;
            }

            $data = $tokenResponse->toArray();
            return $data['access_token'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Génère un identifiant de référence unique
     */
    public function generateReferenceId(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Initie un paiement Momo
     */
    public function initiateMomoPayment(string $token, array $body, string $referenceId): array
    {
        $momoSubscriptionKey = $_ENV['MOMO_SUBSCRIPTION_KEY'] ?? 'f42e9a3ae31842fba6e8c2fea23fa0d7';

        try {
            $paymentResponse = $this->httpClient->request(
                'POST',
                'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay',
                [
                    'headers' => [
                        'Authorization' => "Bearer $token",
                        'X-Callback-Url' => 'https://webhook.site/#!/view/3b1651b5-677b-4034-8c6a-e37ce123869e',
                        'X-Reference-Id' => $referenceId,
                        'X-Target-Environment' => 'mtnivorycoast',
                        'Content-Type' => 'application/json',
                        'Cache-Control' => 'no-cache',
                        'Ocp-Apim-Subscription-Key' => $momoSubscriptionKey,
                    ],
                    'json' => $body,
                ]
            );

            if (in_array($paymentResponse->getStatusCode(), [200, 202])) {
                return ['success' => true];
            }

            return ['success' => false, 'error' => 'Erreur lors de l\'initiation du paiement'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Crée une transaction temporaire pour un professionnel
     */
    public function initTransactionTemp(Request $request, $montant, string $referenceId): void
    {
        $transaction = new Transaction();
       // $transaction->setUser($this->userRepository->find($request->get('user')));
        $transaction->setChannel('momo');
        $transaction->setReference($referenceId);
        $transaction->setMontant($montant);
        $transaction->setReferenceChannel($referenceId);
        $transaction->setType('PAIEMENT MOMO PRO');
        $transaction->setTypeUser($request->get('type'));
        $transaction->setState(0);
        $transaction->setCreatedAtValue();
        $transaction->setUpdatedAt();
        $this->transactionRepository->add($transaction, true);

        $request->get('type')   == 'professionnel' ? $this->createProfessionnelTemp($request,$referenceId) : null;

         /* return  [
            'message' => 'Professionnel bien enregistré',
           /*  'data' => $data */
        /* ]; */ 
    }




    public function createProfessionnelTemp(Request $request,$referenceId)
    {

        $names = 'document_' . '01';
        $filePrefix  = str_slug($names);
        $filePath = $this->getUploadDir('media_deeps', true);
        $professionnel = new TempProfessionnel();

        //etape 1

        $professionnel->setPassword($request->get('password'));
        $professionnel->setCode($request->get('code'));
        $professionnel->setEmail($request->get('email'));
        $professionnel->setUsername($request->get('nom') . " " . $request->get('prenoms'));

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


        $professionnel->setReference($referenceId);
        $professionnel->setTypeUser(User::TYPE['PROFESSIONNEL']);

        // etatpe 4

        $uploadedPhoto = $request->files->get('photo');
        $uploadedCasier = $request->files->get('casier');
        $uploadedCni = $request->files->get('cni');
        $uploadedDiplome = $request->files->get('diplomeFile');
        $uploadedCertificat = $request->files->get('certificat');
        $uploadedCv = $request->files->get('cv');


        if ($uploadedPhoto) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedPhoto, 'media_deeps');
            if ($fichier) {
                $professionnel->setPhoto($fichier);
            }
        }
        if ($uploadedCasier) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedCasier, 'media_deeps');
            if ($fichier) {
                $professionnel->setCasier($fichier);
            }
        }
        if ($uploadedCni) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedCni, 'media_deeps');
            if ($fichier) {
                $professionnel->setCni($fichier);
            }
        }
        if ($uploadedDiplome) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedDiplome, 'media_deeps');
            if ($fichier) {
                $professionnel->setDiplomeFile($fichier);
            }
        }
        if ($uploadedCertificat) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedCertificat, 'media_deeps');
            if ($fichier) {
                $professionnel->setCertificat($fichier);
            }
        }
        if ($uploadedCv) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedCv, 'media_deeps');
            if ($fichier) {
                $professionnel->setCv($fichier);
            }
        }

        // etatpe 5




      /*   $errorResponse = $this->errorResponse($professionnel);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $this->em->persist($professionnel);
            $this->em->flush();
        }
 */

/* 
            $this->em->persist($professionnel);
            $this->em->flush(); */

            $this->tempProfessionnelRepository->add($professionnel, true);


       
    }




    /**
     * Traite le webhook de notification de paiement Momo
     */
    public function traiterWebhookMomo(array $webhookData): array
    {
        try {
            $referenceId = $webhookData['referenceId'] ?? null;
            
            if (!$referenceId) {
                return ['success' => false, 'error' => 'Reference ID manquant'];
            }

            // Récupérer la transaction
            $transaction = $this->transactionRepository->findOneBy(['reference' => $referenceId]);
            
            if (!$transaction) {
                return ['success' => false, 'error' => 'Transaction non trouvée'];
            }

            // Vérifier le statut auprès de l'API Momo
            $statusResult = $this->verifierStatutPaiementPro($referenceId);
            
            if (!$statusResult['success']) {
                return $statusResult;
            }

            return [
                'success' => true,
                'message' => 'Webhook traité avec succès',
                'status' => $statusResult['status'],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifie et met à jour le statut d'une transaction via webhook
     */
    public function verifierEtMettreAJourStatut(string $referenceId): array
    {
        $username = $_ENV['MOMO_USERNAME'];
        $password = $_ENV['MOMO_PASSWORD'];
        $basicToken = base64_encode("$username:$password");
        $momoPrimaryKey = $_ENV['MOMO_PRIMARY_KEY'] ?? '';

        try {
            // Obtenir le token
            $tokenResponse = $this->httpClient->request('POST', 'https://proxy.momoapi.mtn.com/collection/token/', [
                'headers' => [
                    'Authorization' => "Basic $basicToken",
                    'Cache-Control' => 'no-cache',
                    'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
                ],
            ]);

            if ($tokenResponse->getStatusCode() !== 200) {
                return ['success' => false, 'error' => 'Erreur lors de la récupération du token'];
            }

            $token = $tokenResponse->toArray()['access_token'];

            // Vérifier le statut du paiement
            $statusResponse = $this->httpClient->request(
                'GET',
                "https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay/$referenceId",
                [
                    'headers' => [
                        'Authorization' => "Bearer $token",
                        'X-Target-Environment' => 'mtnivorycoast',
                        'Cache-Control' => 'no-cache',
                        'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
                    ],
                ]
            );

            if ($statusResponse->getStatusCode() !== 200) {
                return ['success' => false, 'error' => 'Erreur lors de la vérification du statut'];
            }

            $statusData = $statusResponse->toArray();
            $status = $statusData['status'] ?? null;

            // Mettre à jour la transaction
            $transaction = $this->transactionRepository->findOneBy(['reference' => $referenceId]);
            
            if ($transaction) {
                if ($status === 'SUCCESSFUL') {
                    $transaction->setState(1);
                } elseif ($status === 'FAILED') {
                    $transaction->setState(-1);
                } else {
                    $transaction->setState(0); // PENDING
                }
                
                $transaction->setUpdatedAt(new \DateTimeImmutable());
                $this->transactionRepository->add($transaction, true);
            }

            return [
                'success' => true,
                'status' => $status,
                'data' => $statusData,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Récupère une transaction par sa référence
     */
    public function getTransactionByReference(string $referenceId): ?Transaction
    {
        return $this->transactionRepository->findOneBy(['reference' => $referenceId]);
    }

    /**
     * Initie un paiement pour le renouvellement d'abonnement professionnel
     */
    public function initierRenouvellementAbonnement(array $data): array
    {
        $phoneNumber = $data['phoneNumber'] ?? null;
        $userId = $data['user'] ?? null;

        if (!$phoneNumber || !$userId) {
            return ['error' => 'Numéro de téléphone et user requis'];
        }

        // Récupérer l'utilisateur et son professionnel
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return ['error' => 'Utilisateur non trouvé'];
        }

        $professionnel = $user->getPersonne();
        if (!$professionnel) {
            return ['error' => 'Professionnel non trouvé'];
        }

        // Vérifier que la profession existe
        $profession = $professionnel->getProfession();
        if (!$profession) {
            return ['error' => 'Profession non définie pour ce professionnel'];
        }

        // Calculer le montant basé sur la profession et les années dues
        $montant = $profession->getMontantRenouvellement();
        if (!$montant) {
            return ['error' => 'Montant de renouvellement non défini'];
        }

        $dateValidation = $professionnel->getDateValidation();
        if (!$dateValidation) {
            return ['error' => 'Date de validation non définie'];
        }

        $expiration = clone $dateValidation;
        $today = new \DateTime();
        $yearDue = (int)$today->format('Y') - (int)$expiration->format('Y');
        
        if ($yearDue < 1) {
            $yearDue = 1; // Au minimum 1 an
        }
        
        $montantTotal = $montant * $yearDue;

        // Obtenir le token Momo
        $token = $this->getMomoToken();
        if (!$token) {
            return ['error' => 'Erreur lors de la récupération du token Momo'];
        }

        // Générer une référence unique
        $referenceId = $this->generateReferenceId();

        // Créer le corps de la requête de paiement
        $paymentBody = [
            'amount' => (string) $montantTotal,
            'currency' => 'XOF',
            'externalId' => $referenceId,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => "225$phoneNumber",
            ],
            'payerMessage' => 'Renouvellement abonnement professionnel',
            'payeeNote' => 'Renouvellement abonnement',
        ];

        // Initier le paiement Momo
        $paymentResult = $this->initiateMomoPayment($token, $paymentBody, $referenceId);

        if (!$paymentResult['success']) {
            return ['error' => $paymentResult['error'] ?? 'Erreur lors de l\'initiation du paiement'];
        }

        // Enregistrer la transaction
        $transaction = new Transaction();
        $transaction->setUser($user);
        $transaction->setChannel('momo');
        $transaction->setReference($referenceId);
        $transaction->setMontant($montantTotal);
        $transaction->setReferenceChannel($referenceId);
        $transaction->setType('RENOUVELLEMENT');
        $transaction->setTypeUser('professionnel');
        $transaction->setState(0);
        $transaction->setCreatedAtValue();
        $transaction->setUpdatedAt();
        $this->transactionRepository->add($transaction, true);

        return [
            'success' => true,
            'message' => 'Paiement de renouvellement initié avec succès',
            'referenceId' => $referenceId,
            'montant' => $montantTotal,
            'yearDue' => $yearDue,
        ];
    }

    /**
     * Initie un paiement OEP (Ouverture d'Exploitation) pour établissement
     */
    public function initierOepInscription(array $data): array
    {
        $phoneNumber = $data['phoneNumber'] ?? null;
        $userId = $data['user'] ?? null;
        $montant = $data['amount'] ?? null;

        if (!$phoneNumber || !$userId) {
            return ['error' => 'Numéro de téléphone et user requis'];
        }

        // Récupérer l'utilisateur
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return ['error' => 'Utilisateur non trouvé'];
        }

        // Si le montant n'est pas fourni, le récupérer depuis l'établissement
        if (!$montant) {
            $etablissement = $user->getPersonne();
            if (!$etablissement) {
                return ['error' => 'Établissement non trouvé'];
            }
            
            $niveauIntervention = $etablissement->getNiveauIntervention();
            if (!$niveauIntervention) {
                return ['error' => 'Niveau d\'intervention non défini pour cet établissement'];
            }
            
            $montant = $niveauIntervention->getMontantRenouvellement();
            if (!$montant) {
                return ['error' => 'Montant de renouvellement non défini'];
            }
        }

        // Obtenir le token Momo
        $token = $this->getMomoToken();
        if (!$token) {
            return ['error' => 'Erreur lors de la récupération du token Momo'];
        }

        // Générer une référence unique
        $referenceId = $this->generateReferenceId();

        // Créer le corps de la requête de paiement
        $paymentBody = [
            'amount' => (string) $montant,
            'currency' => 'XOF',
            'externalId' => $referenceId,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => "225$phoneNumber",
            ],
            'payerMessage' => "Ouverture d'exploitation",
            'payeeNote' => 'OEP',
        ];

        // Initier le paiement Momo
        $paymentResult = $this->initiateMomoPayment($token, $paymentBody, $referenceId);

        if (!$paymentResult['success']) {
            return ['error' => $paymentResult['error'] ?? 'Erreur lors de l\'initiation du paiement'];
        }

        // Enregistrer la transaction
        $transaction = new Transaction();
        $transaction->setUser($user);
        $transaction->setChannel('momo');
        $transaction->setReference($referenceId);
        $transaction->setMontant($montant);
        $transaction->setReferenceChannel($referenceId);
        $transaction->setType("OUVERTURE D'EXPLOITATION");
        $transaction->setTypeUser('etablissement');
        $transaction->setState(0);
        $transaction->setCreatedAtValue();
        $transaction->setUpdatedAt();
        $this->transactionRepository->add($transaction, true);

        return [
            'success' => true,
            'message' => 'Paiement OEP initié avec succès',
            'referenceId' => $referenceId,
            'montant' => $montant,
        ];
    }

}