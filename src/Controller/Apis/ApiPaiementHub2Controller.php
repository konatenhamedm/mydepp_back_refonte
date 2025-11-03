<?php

namespace App\Controller\Apis;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\Apis\Config\ApiInterface;
use App\Entity\DocumentTemporaire;
use App\Entity\LibelleGroupe;
use App\Entity\TempEtablissement;
use App\Entity\TempProfessionnel;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\ProfessionRepository;
use App\Repository\TransactionRepository;
use App\Service\PaiementServiceHub2;
use App\Service\PaiementBusinessLogicService;
use DateTimeImmutable;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/api/paiement/hub2')]
class ApiPaiementHub2Controller extends ApiInterface
{

    
   /**
     * ROUTE 1: Initier un paiement (inscription professionnel ou établissement)
     * Remplace l'ancienne route /paiement
     */
    #[Route('/paiement', name: 'paiement_hub2', methods: ['POST'])]
    #[OA\Post(
        summary: "Initier un paiement Hub2",
        description: "Crée un Payment Intent Hub2 pour une nouvelle inscription",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "type", type: "string", description: "professionnel ou etablissement"),
                    new OA\Property(property: "nom", type: "string"),
                    new OA\Property(property: "prenoms", type: "string"),
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "numero", type: "string"),
                    new OA\Property(property: "profession", type: "string", description: "Pour professionnel"),
                    new OA\Property(property: "niveauIntervention", type: "string", description: "Pour établissement"),
                ],
                type: "object"
            )
        ),
    )]
    #[OA\Tag(name: 'paiements')]
    public function doPaiement(Request $request): Response
    {
        try {
            // Étape 1: Créer Payment Intent Hub2
            $result = $this->paiementService->initPaymentIntent($request);
            
            $data = json_decode($request->getContent(), true);
            
            // Étape 2: Créer les données temporaires selon le type
            if ($result['type'] == "professionnel") {
                $this->createProfessionnelTemp($request, $result);
            } else {
                $this->createEtablissemntTemp($request, $result);
            }

            return $this->json([
                'message' => 'Payment Intent créé avec succès',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * ROUTE 2: Initier l'OEP (Ouverture d'Exploitation)
     * Remplace l'ancienne route /inite/oep
     */
    #[Route('/inite/oep', name: 'initie_ope_hub2', methods: ['POST'])]
    #[OA\Post(
        summary: "Initier paiement OEP",
        description: "Crée un Payment Intent Hub2 pour une ouverture d'exploitation",
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
    )]
    #[OA\Tag(name: 'paiements')]
    public function initieOpe(Request $request): Response
    {
        try {
            // Créer le Payment Intent
            $result = $this->paiementService->initPaymentIntent($request);
            
            // Gérer les documents comme dans l'ancien système
            // ... (votre logique de gestion de documents OEP)
            
            return $this->json([
                'message' => 'OEP Payment Intent créé',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * ROUTE 3: Renouvellement
     * Remplace l'ancienne route /renouvellement
     */
    #[Route('/renouvellement', name: 'renouvellement_hub2', methods: ['POST'])]
    #[OA\Post(
        summary: "Initier renouvellement",
        description: "Crée un Payment Intent Hub2 pour un renouvellement",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "user", type: "integer", description: "ID de l'utilisateur"),
                    new OA\Property(property: "type", type: "string", description: "professionnel ou etablissement"),
                    new OA\Property(property: "nom", type: "string"),
                    new OA\Property(property: "prenoms", type: "string"),
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "numero", type: "string"),
                ],
                type: "object"
            )
        ),
    )]
    #[OA\Tag(name: 'paiements')]
    public function doRenouvellement(Request $request): Response
    {
        try {
            $result = $this->paiementService->initPaymentIntent($request);
            
            return $this->json([
                'message' => 'Renouvellement Payment Intent créé',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * WEBHOOK 1: Pour les paiements normaux (inscription)
     * Remplace l'ancienne route /info-paiement
     */
    #[Route('/info-paiement', name: 'webhook_paiement_hub2', methods: ['POST'])]
    #[OA\Post(
        summary: "Webhook Hub2 - Paiements",
        description: "Reçoit les notifications de Hub2 pour les paiements d'inscription",
    )]
    #[OA\Tag(name: 'paiements')]
    public function webHook(Request $request): Response
    {
        try {
            // Récupérer le secret depuis l'environnement
            $webhookSecret = $this->params->get('HUB2_WEBHOOK_SECRET') ?? null;
            
            $response = $this->paiementService->handleWebhook($request, $webhookSecret);
            
            // Si paiement réussi, exécuter la logique métier
            if ($response['state'] == 1) {
                $this->executerLogiqueMetier($response['reference']);
            }
            
            return $this->json($response);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * WEBHOOK 2: Pour les paiements OEP
     * Remplace l'ancienne route /info-paiement-oep
     */
    #[Route('/info-paiement-oep', name: 'webhook_paiement_oep_hub2', methods: ['POST'])]
    #[OA\Post(
        summary: "Webhook Hub2 - OEP",
        description: "Reçoit les notifications de Hub2 pour les paiements OEP",
    )]
    #[OA\Tag(name: 'paiements')]
    public function webHookOep(Request $request): Response
    {
        try {
            $webhookSecret = $this->params->get('HUB2_WEBHOOK_SECRET') ?? null;
            $response = $this->paiementService->handleWebhook($request, $webhookSecret);
            
            // Si paiement réussi, traiter les documents OEP
            if ($response['state'] == 1) {
                $this->businessLogicService->updateDocumentOep($response['reference']);
            }
            
            return $this->json($response);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * WEBHOOK 3: Pour les renouvellements
     * Remplace l'ancienne route /info-paiement-renouvellement
     */
    #[Route('/info-paiement-renouvellement', name: 'webhook_paiement_renouvellement_hub2', methods: ['POST'])]
    #[OA\Post(
        summary: "Webhook Hub2 - Renouvellements",
        description: "Reçoit les notifications de Hub2 pour les renouvellements",
    )]
    #[OA\Tag(name: 'paiements')]
    public function webHookRenouvellement(Request $request): Response
    {
        try {
            $webhookSecret = $this->params->get('HUB2_WEBHOOK_SECRET') ?? null;
            $response = $this->paiementService->handleWebhook($request, $webhookSecret);
            
            // Si paiement réussi, traiter le renouvellement
            if ($response['state'] == 1) {
                // Trouver la transaction pour obtenir l'utilisateur
                // et appeler la méthode de renouvellement appropriée
                $this->traiterRenouvellement($response['reference']);
            }
            
            return $this->json($response);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * ROUTE BONUS: Créer des webhooks Hub2
     */
    #[Route('/webhooks/create', name: 'create_webhooks_hub2', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer les webhooks Hub2",
        description: "Enregistre automatiquement vos endpoints webhook auprès de Hub2",
    )]
    #[OA\Tag(name: 'paiements')]
    public function createWebhooks(Request $request): Response
    {
        try {
            $baseUrl = $this->params->get('APP_URL') ?? 'https://backend.leadagro.net';
            
            $webhooks = [];
            
            // Webhook 1: Paiements inscription
            $webhooks[] = $this->paiementService->createWebhook(
                $baseUrl . '/api/paiement/info-paiement',
                ['payment_intent.successful', 'payment_intent.failed'],
                'Webhook pour les paiements d\'inscription'
            );
            
            // Webhook 2: Paiements OEP
            $webhooks[] = $this->paiementService->createWebhook(
                $baseUrl . '/api/paiement/info-paiement-oep',
                ['payment_intent.successful', 'payment_intent.failed'],
                'Webhook pour les paiements OEP'
            );
            
            // Webhook 3: Renouvellements
            $webhooks[] = $this->paiementService->createWebhook(
                $baseUrl . '/api/paiement/info-paiement-renouvellement',
                ['payment_intent.successful', 'payment_intent.failed'],
                'Webhook pour les renouvellements'
            );
            
            return $this->json([
                'code' => 200,
                'message' => 'Webhooks créés avec succès',
                'webhooks' => $webhooks,
                'important' => 'SAUVEGARDEZ LES SECRETS dans votre .env comme HUB2_WEBHOOK_SECRET'
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * ROUTE BONUS: Lister les webhooks
     */
    #[Route('/webhooks/list', name: 'list_webhooks_hub2', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister les webhooks Hub2",
        description: "Récupère la liste de tous vos webhooks enregistrés",
    )]
    #[OA\Tag(name: 'paiements')]
    public function listWebhooks(): Response
    {
        try {
            $webhooks = $this->paiementService->listWebhooks();
            
            return $this->json([
                'code' => 200,
                'webhooks' => $webhooks
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'code' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Exécute la logique métier après paiement réussi
     */
    private function executerLogiqueMetier(string $reference): void
    {
        // Récupérer les données temporaires
        $tempProf = $this->em->getRepository(\App\Entity\TempProfessionnel::class)
            ->findOneBy(['reference' => $reference]);
        
        if ($tempProf) {
            $this->businessLogicService->updateProfessionnel($reference);
            return;
        }
        
        $tempEtab = $this->em->getRepository(\App\Entity\TempEtablissement::class)
            ->findOneBy(['reference' => $reference]);
        
        if ($tempEtab) {
            $this->businessLogicService->updateEtablissement($reference);
        }
    }

    /**
     * Traite le renouvellement selon le type d'utilisateur
     */
    private function traiterRenouvellement(string $reference): void
    {
        $transaction = $this->em->getRepository(\App\Entity\Transaction::class)
            ->findOneBy(['reference' => $reference]);
        
        if (!$transaction || !$transaction->getUser()) {
            return;
        }
        
        $user = $transaction->getUser();
        
        if ($user->getTypeUser() === 'PROFESSIONNEL') {
            $this->businessLogicService->renouvelerProfessionnel($user);
        } else {
            $this->businessLogicService->renouvelerEtablissement($user);
        }
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


        $documents = $request->get('documents');


        $uploadedFiles = $request->files->get('documents');

        /*    dd($documents); */

        if ($documents) {
            foreach ($documents as $index => $doc) {

                $newDocument = new DocumentTemporaire();
                $newDocument->setLibelle($doc['libelle'])
                    ->setLibelleGroupe($this->em->getRepository(LibelleGroupe::class)->find($doc['libelleGroupe']));

                if (isset($uploadedFiles[$index])) {
                    /* $fileKeys = [
                        'path',
                    ]; */

                    /*   foreach ($fileKeys as $key) { */
                    if (!empty($uploadedFiles[$index]['path'])) {
                        $uploadedFile = $uploadedFiles[$index]['path'];
                        $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
                        if ($fichier) {
                            /*    $setter = 'set' . ucfirst($key); */
                            $newDocument->setPath($fichier);
                        }
                    }
                    /*   } */
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
