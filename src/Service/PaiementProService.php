<?php


namespace App\Service;

use App\Controller\FileTrait;
use App\Entity\TempProfessionnel;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\ProfessionnelRepository;
use App\Repository\TempEtablissementRepository;
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
        private TempEtablissementRepository $tempEtablissementRepository,
        private PaiementService $paiementService
    ) {}

    /**
     * Surcharge locale pour éviter l'appel à getParameter() (disponible uniquement en Controller).
     */
    public function getUploadDir($path, $create = false)
    {
        $uploadBaseDir = dirname(__DIR__, 2) . '/public/uploads';
        $path = $uploadBaseDir . '/' . $path;

        if ($create && !is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    /**
     * Initie un paiement professionnel via Momo en mimiquant PaiementService
     */
    public function traiterPaiement(Request $request): array
    {
        $data = $request->request->all(); // FormData text fields
        if(empty($data)){
            $data = json_decode($request->getContent(), true) ?? [];
        }

        $type = $data['type'] ?? $request->get('type');
        $professionInfo = $data['profession'] ?? $request->get('profession');
        $niveauInterventionInfo = $data['niveauIntervention'] ?? $request->get('niveauIntervention');

        $montant = $type == "professionnel" 
            ? $this->em->getRepository(\App\Entity\Profession::class)->findOneBy(['code' => $professionInfo])->getMontantNouvelleDemande() 
            : $this->em->getRepository(\App\Entity\NiveauIntervention::class)->find($niveauInterventionInfo)->getMontant();

        $phoneNumber = $data['numero'] ?? $data['phoneNumber'] ?? $request->get('numero') ?? $request->get('phoneNumber');

        $username = $_ENV['MOMO_USERNAME'];
        $password = $_ENV['MOMO_PASSWORD'];
        $basicToken = base64_encode("$username:$password");
        $momoPrimaryKey = $_ENV['MOMO_PRIMARY_KEY'];
        $momoSubscriptionKey = $_ENV['MOMO_SUBSCRIPTION_KEY'];

        $tokenResponse = $this->httpClient->request('POST', 'https://proxy.momoapi.mtn.com/collection/token/', [
            'headers' => [
                'Authorization' => "Basic $basicToken",
                'Cache-Control' => 'no-cache',
                'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
            ],
        ]);

        if ($tokenResponse->getStatusCode() !== 200) {
            return ['code' => 500, 'error' => 'Erreur lors de la récupération du token'];
        }

        $token = $tokenResponse->toArray()['access_token'];
        $myUuid = Uuid::uuid4()->toString();

        if (!$phoneNumber) {
            return ['code' => 500, 'error' => 'Numéro manquant'];
        }

        $paymentBody = [
            'amount' => (string) $montant,
            'currency' => 'XOF',
            'externalId' => $myUuid,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => "225$phoneNumber",
            ],
            'payerMessage' => 'Paiement adhesion',
            'payeeNote' => 'Paiement',
        ];

        $paymentResponse = $this->httpClient->request(
            'POST',
            'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay',
            [
                'headers' => [
                    'Authorization' => "Bearer $token",
                    'X-Callback-Url' => 'https://webhook.site/9fb6dc51-7956-4d1d-9628-6b2b0fcf2fba',
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
            $errorContent = $paymentResponse->getContent(false);
            return [
                'code' => 500, 
                'error' => 'Erreur API MTN Momo : ' . $paymentResponse->getStatusCode() . ' - ' . $errorContent
            ];
        }

        $transaction = new Transaction();
        $transaction->setChannel('momo');
        $transaction->setReference($myUuid);
        $transaction->setMontant($montant);
        $transaction->setReferenceChannel($myUuid);
        $transaction->setType('NOUVELLE DEMANDE');
        $transaction->setTypeUser($type);
        $transaction->setState(0);
        $transaction->setCreatedAtValue();
        $transaction->setUpdatedAt();
        $this->transactionRepository->add($transaction, true);

        return [
            'code' => 200,
            'url' => null,
            'reference' => $myUuid,
            'type' => $type
        ];
    }

    /**
     * Vérifie le statut d'un paiement professionnel et met à jour la transaction
     */
    public function verifierStatutPaiementPro(string $referenceId): array
    {
        $username = $_ENV['MOMO_USERNAME'];
        $password = $_ENV['MOMO_PASSWORD'];
        $response = null;
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
        // dd($transaction->getTypeUser());
        if ($transaction) {
            
            if (($statusData['status'] ?? null) !== 'FAILED' && ($statusData['status'] ?? null) !== 'PENDING') {
                
                $response = ($transaction->getTypeUser() == "professionnel" || $transaction->getTypeUser() == "PROFESSIONNEL") ?  $this->paiementService->updateProfessionnel($referenceId) :  $this->paiementService->updateEtablissement($referenceId);
            }


            if ($response) {
                if ($transaction->getTypeUser() == "professionnel" || $transaction->getTypeUser() == "PROFESSIONNEL") {
                    $temp =  $this->tempProfessionnelRepository->findOneBy(['reference' => $referenceId]);
                    $this->tempProfessionnelRepository->remove($temp, true);
                } else {
                   $temp =  $this->tempEtablissementRepository->findOneBy(['reference' =>$referenceId]);
                   $this->tempEtablissementRepository->remove($temp, true);
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
                        'X-Callback-Url' => 'https://webhook.site/9fb6dc51-7956-4d1d-9628-6b2b0fcf2fba',
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
                    $transaction->setState(0); // FAILED
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

    public function traiterPaiementRenouvellement(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->em->getRepository(User::class)->find($data['user']);
        $montant = $user->getPersonne()->getProfession()->getMontantRenouvellement();
        $expiration = (clone $user->getPersonne()->getDateValidation());
        $today = new \DateTime();
        $yearDue = (int)$today->format('Y') - (int)$expiration->format('Y');

        if ($yearDue < 1) $yearDue = 1;

        $yearsToPay = $data['yearsToPay'] ?? $yearDue;
        if ($yearsToPay < 1) $yearsToPay = 1;

        $montantTotal = $montant * $yearsToPay;
        $phoneNumber = $data['numero'] ?? null;

        $token = $this->getMomoToken();
        $myUuid = Uuid::uuid4()->toString();

        $paymentBody = [
            'amount' => (string) $montantTotal,
            'currency' => 'XOF',
            'externalId' => $myUuid,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => "225$phoneNumber",
            ],
            'payerMessage' => 'Renouvellement',
            'payeeNote' => 'Renouvellement abonnement',
        ];

        $paymentResult = $this->initiateMomoPayment($token, $paymentBody, $myUuid);

        if (!$paymentResult['success']) {
            return ['code' => 500, 'error' => 'Erreur d\'initiation', 'type' => $data['type'] ?? 'professionnel'];
        }

        $transaction = new Transaction();
        $transaction->setUser($user);
        $transaction->setChannel('momo');
        $transaction->setReference($myUuid);
        $transaction->setMontant($montantTotal);
        $transaction->setReferenceChannel($myUuid);
        $transaction->setType('RENOUVELLEMENT');
        $transaction->setTypeUser($data['type'] ?? 'professionnel');
        $transaction->setState(0);
        $transaction->setData(json_encode(['yearsToPay' => $yearsToPay]));
        $transaction->setCreatedAtValue();
        $transaction->setUpdatedAt();
        $this->transactionRepository->add($transaction, true);

        return [
            'code' => 200,
            'url' => null,
            'reference' => $myUuid,
            'type' => $data['type'] ?? 'professionnel'
        ];
    }

    public function traiterPaiementOpe(Request $request): array
    {
        $montant = $this->em->getRepository(\App\Entity\NiveauIntervention::class)->find($request->get('niveauIntervention'))->getMontantRenouvellement();

        $phoneNumber = $request->get('telephone') ?? clone $request->get('numero') ?? $request->get('email'); // dummy match

        $myUuid = Uuid::uuid4()->toString();
        $token = $this->getMomoToken();

        $paymentBody = [
            'amount' => (string) $montant,
            'currency' => 'XOF',
            'externalId' => $myUuid,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => "225$phoneNumber",
            ],
            'payerMessage' => "Ouverture d'exploitation",
            'payeeNote' => 'OEP',
        ];

        $this->initiateMomoPayment($token, $paymentBody, $myUuid);

        $transaction = new Transaction();
        $transaction->setChannel("momo");
        
        if($request->get('user')){
            $transaction->setUser($this->userRepository->find($request->get('user')));
        }
        $transaction->setReference($myUuid);
        $transaction->setMontant($montant);
        $transaction->setReferenceChannel($myUuid);
        $transaction->setType("OUVERTURE D'EXPLOITATION");
        $transaction->setTypeUser('etablissement');
        $transaction->setState(0);
        $transaction->setCreatedAtValue(new \DateTime());
        $transaction->setUpdatedAt(new \DateTime());

        $this->transactionRepository->add($transaction, true);

        return [
            'code' => 200,
            'url' => null,
            'reference' => $myUuid,
            'type' => 'etablissement'
        ];
    }
    
    // Webhook Handlers
    public function methodeWebHook(Request $request): array
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $reference = $request->headers->get('X-Reference-Id') ?? ($data['referenceId'] ?? $data['externalId'] ?? null);
        
        if (!$reference) return ['message' => 'Reference missing', 'code' => 400];
        
        $statusResult = $this->verifierStatutPaiementPro($reference);
        if (isset($statusResult['status']) && $statusResult['status'] === 'SUCCESSFUL') {
            return ['code' => 200, 'message' => 'Success']; // verifierStatutPaiementPro triggers updateProfessionnel
        }
        return ['code' => 400, 'message' => 'Failure'];
    }

    public function methodeWebHookOep(Request $request): array
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $reference = $request->headers->get('X-Reference-Id') ?? ($data['referenceId'] ?? $data['externalId'] ?? null);
        
        if (!$reference) return ['message' => 'Reference missing', 'code' => 400];
        
        $statusResult = $this->verifierStatutPaiementPro($reference); // same verifier
        if (isset($statusResult['status']) && $statusResult['status'] === 'SUCCESSFUL') {
            $this->paiementService->updateDocumentOep($reference); // Execute specific OEP action
            
            $transaction = $this->transactionRepository->findOneBy(['reference' => $reference]);
            $etablissement = $this->em->getRepository(\App\Entity\Etablissement::class)->findOneBy(['id' => $transaction->getUser()->getPersonne()]);
            if ($etablissement) {
                $etablissement->setStatus('oep_demande_initie');
                $this->em->persist($etablissement);
                $this->em->flush();
            }

            return ['code' => 200, 'message' => 'Success'];
        }
        return ['code' => 400, 'message' => 'Failure'];
    }

    public function methodeWebHookRenouvellement(Request $request): array
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $reference = $request->headers->get('X-Reference-Id') ?? ($data['referenceId'] ?? $data['externalId'] ?? null);
        
        if (!$reference) return ['message' => 'Reference missing', 'code' => 400];
        
        $statusResult = $this->verifierStatutPaiementPro($reference); // same verifier
        if (isset($statusResult['status']) && $statusResult['status'] === 'SUCCESSFUL') {
            $transaction = $this->transactionRepository->findOneBy(['reference' => $reference]);
            $professionnel = $this->userRepository->find($transaction->getUser())->getPersonne();
            
            $dernierAbonnement = $this->transactionRepository->findOneBy(
                ['user' => $transaction->getUser(), 'state' => 1],
                ['createdAt' => 'DESC']
            );

            $now = new \DateTime();
            if (!$dernierAbonnement) {
                $dateRenouvellement = $now->add(new \DateInterval('P1Y'));
            } else {
                $expiration = (clone $dernierAbonnement->getCreatedAt())->modify('+1 year');
                if ($expiration < $now) {
                    $dateRenouvellement = $now->add(new \DateInterval('P1Y'));
                } else {
                    $dateRenouvellement = $expiration->add(new \DateInterval('P1Y'));
                }
            }

            $transactionData = json_decode($transaction->getData() ?? "[]", true);
            $yearsToPay = $transactionData['yearsToPay'] ?? 1;

            if ($professionnel) {
                $professionnel->setStatus("a_jour");
                $professionnel->setDateValidation($dateRenouvellement->modify("+" . ($yearsToPay - 1) . " years"));
                $this->em->persist($professionnel);
                $this->em->flush();
            }
            return ['code' => 200, 'message' => 'Success'];
        }
        return ['code' => 400, 'message' => 'Failure'];
    }

}