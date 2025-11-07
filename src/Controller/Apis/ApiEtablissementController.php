<?php


namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\ActiveProfessionnelRequest;
use App\DTO\ActiveProfessionnelRequestEtablissement;
use App\Entity\Document;
use App\Entity\Etablissement;
use App\Entity\LibelleGroupe;
use App\Entity\Organisation;
use App\Entity\TypePersonne;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\EtablissementRepository;
use App\Entity\User;
use App\Entity\ValidationWorkflow;
use App\Repository\CiviliteRepository;
use App\Repository\DocumentRepository;
use App\Repository\GenreRepository;
use App\Repository\LibelleGroupeRepository;
use App\Repository\NiveauInterventionRepository;
use App\Repository\OrganisationRepository;
use App\Repository\PaysRepository;
use App\Repository\ProfessionRepository;
use App\Repository\SpecialiteRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Repository\TypePersonneRepository;
use App\Service\SendMailService;
use App\Service\Utils;
use DateTime;
use DateTimeImmutable;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/etablissement')]
class ApiEtablissementController extends ApiInterface
{






    #[Route('/update/imputation/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de pro",
        description: "Permet de créer un pro.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "imputation", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'etablissement')]
    /*  */
    public function updateImputation(Request $request, SendMailService $sendMailService, Etablissement $etablissement, EtablissementRepository $etablissementRepository, UserRepository $userRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($etablissement != null) {

                $etablissement->setImputation($userRepository->find($data->imputation));

                $etablissement->setUpdatedBy($this->getUser());
                $etablissement->setUpdatedAt(new \DateTime());
                $etablissement->setStatus("oep_dossier_imputer");
                $errorResponse = $this->errorResponse($etablissement);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $etablissementRepository->add($etablissement, true);
                }


                /*   $info_user = [
                'user' => $user->getUserIdentifier(),
              
                'profession' => "",
                'etape' => $dto->status,
                'message' => $message,
                'annee' => $etablissement->getCreatedAt()->format('Y'),
                // Ajouter la date de visite dans le contexte pour l'email
                'date_visite' => $dto->status === "programmation_visite" ? $dto->dateVisite : null
            ];


                     $context = compact('info_user');

            $sendMailService->send(
                'depps@leadagro.net',
                $etablissement->getEmail(),
                'Imputation',
                'content_validation',
                $context
            );

            $sendMailService->sendNotification(
                "Votre dossier viens d'être imputé " ,
                $userRepository->findOneBy(['personne' => $etablissement->getId()]),
                $userRepository->find($data->userUpdate)
            ); */


                // On retourne la confirmation
                $response = $this->responseData([
                    'error' => $errorResponse,
                    'id' => $etablissement->getId(),
                    'code' => $etablissement->getCode(),
                    'status' => $etablissement->getStatus(),
                    'email' => $etablissement->getEmail(),


                ], 'group_pro', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage("Cette ressource est inexsitante");
                $this->setStatusCode(300);
                $response = $this->response('[]');
            }
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }

        public function genererCodeEtablissement(): string
    {
        $query = $this->em->createQueryBuilder();
        $query->select("count(a.id)")
            ->from(Etablissement::class, 'a');

        $nb = $query->getQuery()->getSingleScalarResult();
        return ('DEPPS' . date("y") . date("m") . date("d") . date("H") . date("i") . date("s") . str_pad($nb + 1, 3, '0', STR_PAD_LEFT));
    }

    #[Route('/active/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Accepter ou refuser un etablissement",
        description: "Permet d'accepter ou de refuser un etablissement.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "status", type: "string"),
                        new OA\Property(property: "raison", type: "string", nullable: true),
                        new OA\Property(property: "dateVisite", type: "string", format: "date", nullable: true),
                        
                        new OA\Property(property: "email", type: "string"),
                        new OA\Property(
                            property: "rapportExamen",
                            type: "string",
                            format: "binary", // Important pour les fichiers
                            nullable: true
                        ),
                    ],
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(response: 400, description: "Données invalides"),
            new OA\Response(response: 404, description: "Professionnel non trouvé"),
            new OA\Response(response: 200, description: "Mise à jour réussie")
        ]
    )]
    #[OA\Tag(name: 'etablissement')]
    public function active(
        Request $request,
        Etablissement $etablissement,
        EtablissementRepository $etablissementRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        Registry $workflowRegistry,
        SendMailService $sendMailService,
        Utils $utils

    ): Response {
        try {

            $names = 'document_' . '01';
            $filePrefix  = str_slug($names);
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);


            $data = json_decode($request->getContent(), true);

            $dto = new ActiveProfessionnelRequestEtablissement();

            $dto->status = $request->get('status');
            $dto->email = $request->get('email');
            $dto->userUpdate = $request->get('userUpdate');
            $dto->raison = $request->get('raison');
            $dto->dateVisite = $request->get('dateVisite');

            $uploaded = $request->files->get('rapportExamen');
            // Gérer l'upload du fichier pour la transition visite_effectuee
            if ($dto->status === "visite_effectuee") {

                if ($uploaded) {
                    $fichier = $utils->sauvegardeFichier($filePath, $filePrefix, $uploaded, self::UPLOAD_PATH);
                    if ($fichier) {
                        // $etablissement->setRapportExamen($fichier);
                        $dto->rapportExamen = $fichier;
                    }
                }
            }

            $errors = $validator->validate($dto);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $validationCompteWorkflow = $workflowRegistry->get($etablissement);

            // Vérifier la transition du workflow
            if (!$validationCompteWorkflow->can($etablissement, $dto->status)) {
                return new JsonResponse([
                    'error' => "Transition non valide depuis l'état actuel"
                ], Response::HTTP_BAD_REQUEST);
            }

            $validationCompteWorkflow->apply($etablissement, $dto->status);

            // Traitement spécifique pour programmation_visite
            if ($dto->status === "programmation_visite") {
                if (!$dto->dateVisite) {
                    return new JsonResponse([
                        'error' => "La date de visite est obligatoire pour cette transition"
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Enregistrer la date de visite dans l'établissement
                $etablissement->setDateVisite(new \DateTime($dto->dateVisite));
                $etablissement->setReason($dto->raison);
            }

            // Traitement spécifique pour visite_effectuee
            if ($dto->status === "visite_effectuee") {
                if (!$dto->rapportExamen) {
                    return new JsonResponse([
                        'error' => "Le rapport d'examen est obligatoire pour cette transition"
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Enregistrer le rapport d'examen dans l'établissement
                $etablissement->setRapportExamen($dto->rapportExamen);
            }
            if ($dto->status === "validation_finale") {
                
                // Enregistrer le rapport d'examen dans l'établissement
                if($etablissement->getNiveauIntervention()->getMontant() != null){
                    $etablissement->setDateValidation(new \DateTime());
                }
            
                $etablissement->setCode($this->genererCodeEtablissement());
            }

            $etablissementRepository->add($etablissement, true);

            $validationWorkflow = new ValidationWorkflow();
            $validationWorkflow->setEtape($dto->status);
            $validationWorkflow->setRaison($dto->raison);
            $validationWorkflow->setPersonne($etablissement);
            $validationWorkflow->setCreatedAtValue(new DateTime());
            $validationWorkflow->setUpdatedAt(new DateTime());
            $validationWorkflow->setCreatedBy($userRepository->find($dto->userUpdate));
            $validationWorkflow->setUpdatedBy($userRepository->find($dto->userUpdate));

            $this->em->persist($validationWorkflow);
            $this->em->flush();

            $message = "";

            if ($dto->status == "acceptation") {
                $message = "Votre dossier vient de passer l'étape d'acceptation et est en séance d'analyse";
            } elseif ($dto->status == "rejet") {
                $message = "Votre dossier vient d'être rejeté pour la raison suivante: " . $dto->raison;
            } elseif ($dto->status == "refuse") {
                $message = "Votre dossier vient d'être refusé pour la raison suivante: " . $dto->raison;
            } elseif ($dto->status == "validation") {
                $message = "Votre dossier a été jugé conforme et est désormais en attente de validation finale.";
            } elseif ($dto->status == "programmation_visite") {
                $message = "Une visite a été programmée dans votre établissement pour le " .
                    (new \DateTime($dto->dateVisite))->format('d/m/Y');
            } elseif ($dto->status == "visite_effectuee") {
                $message = "La visite dans votre établissement a été effectuée. Le rapport d'examen est disponible.";
            }

            $user = $userRepository->find($request->get('userUpdate'));

            $info_user = [
                'user' => $user->getUserIdentifier(),
                'nom' => $etablissement->getTypePersonne()->getCode() == "PHYSIQUE" ? $etablissement->getNom() . ' ' . $etablissement->getPrenoms() : $etablissement->getDenomination(),
                'profession' => "",
                'etape' => $dto->status,
                'message' => $message,
                'annee' => $etablissement->getCreatedAt()->format('Y'),
                // Ajouter la date de visite dans le contexte pour l'email
                'date_visite' => $dto->status === "programmation_visite" ? $dto->dateVisite : null
            ];

            $context = compact('info_user');

            $sendMailService->send(
                'depps@leadagro.net',
                $request->get('email'),
                'Validation du dossier - Étape: ' . $dto->status,
                'content_validation',
                $context
            );

            $sendMailService->sendNotification(
                "Votre compte vient d'être validé pour l'étape " . $dto->status,
                $userRepository->findOneBy(['personne' => $etablissement->getId()]),
                $userRepository->find($dto->userUpdate)
            );

            //$sendMailService->sendNotification("votre compte vient d'être valider pour l'etape " . $dto->status, $userRepository->findOneBy(['personne' => $professionnel->getId()]), $userRepository->find($data['userUpdate']));

            return $this->responseData($info_user, 'group_pro', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->json([
                "message" => "Une erreur est survenue",
                "error" => $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/create', methods: ['POST'])]
    /**
     * Crée un nouvel établissement avec ses documents associés.
     */
    #[OA\Post(
        summary: "Création d'un établissement",
        description: "Permet de créer un nouvel établissement avec toutes les informations requises et documents joints.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "password", type: "string"),
                        new OA\Property(property: "confirmPassword", type: "string"),
                        new OA\Property(property: "email", type: "string"),
                        new OA\Property(property: "nom", type: "string"),
                        new OA\Property(property: "prenoms", type: "string"),
                        new OA\Property(property: "telephone", type: "string"),
                        new OA\Property(property: "typePersonne", type: "string"),
                        new OA\Property(property: "bp", type: "string"),
                        new OA\Property(property: "emailAutre", type: "string"),
                        new OA\Property(property: "adresse", type: "string"),
                        new OA\Property(property: "nomRepresentant", type: "string"),
                        new OA\Property(property: "denomination", type: "string"),
                        new OA\Property(property: "reference", type: "string"),
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
        responses: [
            new OA\Response(response: 201, description: "Établissement créé avec succès"),
            new OA\Response(response: 400, description: "Données invalides"),
            new OA\Response(response: 404, description: "Transaction introuvable")
        ]
    )]
    #[OA\Tag(name: 'etablissement')]
    public function create(UserPasswordHasherInterface $hasher, NiveauInterventionRepository $niveauInterventionRepository, Utils $utils, LibelleGroupeRepository $libelleGroupeRepository, Request $request, SessionInterface $session, SendMailService $sendMailService, TransactionRepository $transactionRepository, GenreRepository $genreRepository, EtablissementRepository $etablissementRepository, TypePersonneRepository $typePersonneRepository): Response
    {

        $names = 'document_' . '01';
        $filePrefix  = str_slug($names);
        $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);



        /*   $transaction = $transactionRepository->findOneBy(['reference' =>  $request->get('reference'), 'user' => null]);

        if (!$transaction) {
            return $this->response("Transaction introuvable");
        } else { */


        $user = new User();
        $user->setUsername($request->get('nomEntreprise') . " " . $this->numero());
        $user->setEmail($request->get('email'));
        $plainPassword = $request->get('password');


        $user->setPassword($hasher->hashPassword($user, $plainPassword));
        // $user->setPassword("test");
        $user->setRoles(['ROLE_MEMBRE']);
        $user->setTypeUser(User::TYPE['ETABLISSEMENT']);
        $user->setPayement(User::PAYEMENT['init_payement']);


        $errorResponse1 = $request->get('password') !== $request->get('confirmPassword') ?  $this->errorResponse($user, "Les mots de passe ne sont pas identiques") :  $this->errorResponse($user);
        if ($errorResponse1 !== null) {
            return $errorResponse1; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $typePersonne = $typePersonneRepository->findOneByCode($request->get('typePersonne'));
            $etablissement = new Etablissement();
            $etablissement->setNiveauIntervention($niveauInterventionRepository->find($request->get('niveauIntervention')));

            if ($typePersonne->getCode() === 'PHYSIQUE') {
                $etablissement->setNom($request->get('nom'));
                $etablissement->setPrenoms($request->get('prenoms'));
                $etablissement->setBp($request->get('bp'));
                $etablissement->setTelephone($request->get('telephone'));
                $etablissement->setEmailAutre($request->get('emailAutre'));
            } else {
                $etablissement->setDenomination($request->get('denomination'));
                $etablissement->setTypeSociete($request->get('typeSociete'));
                $etablissement->setAdresse($request->get('adresse'));
                $etablissement->setNomRepresentant($request->get('nomRepresentant'));
            }

            $etablissement->setTypePersonne($typePersonne);
            $etablissement->setTypePersonne($typePersonne);
            $etablissement->setStatus("acp_attente_dossier_depot_service_courrier");


            $documents = $request->get('documents');


            $uploadedFiles = $request->files->get('documents');

            foreach ($documents as $index => $doc) {

                $newDocument = new Document();
                $newDocument->setLibelle($doc['libelle'])
                    ->setLibelleGroupe($libelleGroupeRepository->find($doc['libelleGroupe']));

                if (isset($uploadedFiles[$index])) {
                    $fileKeys = [
                        'path',
                    ];

                    foreach ($fileKeys as $key) {
                        if (!empty($uploadedFiles[$index][$key])) {
                            $uploadedFile = $uploadedFiles[$index][$key];
                            $fichier = $utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
                            if ($fichier) {
                                $setter = 'set' . ucfirst($key);
                                $newDocument->$setter($fichier);
                            }
                        }
                    }
                }


                $newDocument->setCreatedBy($user);
                $newDocument->setUpdatedBy($user);
                $newDocument->setCreatedAtValue(new \DateTime());
                $newDocument->setUpdatedAt(new \DateTime());


                $etablissement->addDocument($newDocument);
            }



            $etablissement->setCreatedBy($user);
            $etablissement->setUpdatedBy($user);
            $etablissement->setCreatedAtValue(new \DateTime());
            $etablissement->setUpdatedAt(new \DateTime());

            $errorResponse = $this->errorResponse($etablissement);
            if ($errorResponse !== null) {
                return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
            } else {

                $etablissementRepository->add($etablissement, true);
                $user->setPersonne($etablissement);
                $this->userRepository->add($user, true);



                $info_user = [
                    'login' => $request->get('email'),
                    'password' => $request->get('confirmPassword')
                ];

                $context = compact('info_user');

                // TO DO
                $sendMailService->send(
                    'depps@leadagro.net',
                    $request->get('email'),
                    'Informations',
                    'content_mail',
                    $context
                );
            }
        }
        //}

        return $this->responseData([
            'id' => $etablissement->getId(),
            'code' => $etablissement->getCode(),
            'status' => $etablissement->getStatus(),


        ], 'group_pro', ['Content-Type' => 'application/json']);
    }




    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des etablissements.
     * 
     */
    #[OA\Response(
        response: 200,
        description: ' Retourne la liste des etablissements',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Etablissement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'etablissement')]
    // 
    public function index(EtablissementRepository $etablissementRepository, UserRepository $userRepository): Response
    {

        try {
            /* $etablissements = $etablissementRepository->findAll(); */

            $etablissements = $userRepository->findBy(['typeUser' => 'ETABLISSEMENT'], ['id' => 'DESC']);


            $formattedProfessionnels = array_map(function ($etablissement) use ($etablissementRepository) {
                $personne = $etablissement->getPersonne();


                return [
                    'username' => $etablissement->getUsername(),
                    'id' => $etablissement->getId(),
                    'email' => $etablissement->getEmail(),
                    'typeUser' => $etablissement->getTypeUser(),
                    'personne' => [
                        'id' => $personne->getId(),
                        'code' => $personne->getCode(), //
                        'type' => "etablissement",
                        'status' => $personne->getStatus(),
                        'createdAt' => $personne->getCreatedAt(),
                        'dateExamenRapport' => $personne->getDateExamenRapport(),
                        'rapportExamen' => $personne->getRapportExamen() ? $this->formatFile($personne->getRapportExamen()) : null,
                        'niveauIntervention' => $personne->getNiveauIntervention() ? $this->formatEntity($personne->getNiveauIntervention()) : null,
                        'dateVisite' => $personne->getDateVisite(),
                        'typePersonne' => $personne->getTypePersonne() ?  $this->formatEntity($personne->getTypePersonne()) : null,
                        'imputationData' => $personne->getImputation() ? [
                            'id' =>  $personne->getImputation()->getId(),
                            'username' =>  $personne->getImputation()->getUsername(),
                            'email' =>  $personne->getImputation()->getEmail(),
                        ] : null,
                        'denomination' => $personne->getDenomination(),
                        'typeSociete' => $personne->getTypeSociete(),
                        'nomRepresentant' => $personne->getNomRepresentant(),
                        'adresse' => $personne->getAdresse(),
                        'telephone' => $personne->getTelephone(),
                        'emailAutre' => $personne->getEmailAutre(),
                        'bp' => $personne->getBp(),
                        'nom' => $personne->getNom(),
                        'prenoms' => $personne->getPrenoms(),
                        'documents' => array_map(function ($doc) {
                            return [
                                'id' => $doc->getId(),
                                'libelle' => $doc->getLibelle(),
                                'libelleGroupe' => $this->formatEntity($doc->getLibelleGroupe()),
                                'path' => $doc->getPath() ?  $this->formatFile($doc->getPath()) : null,
                            ];
                        }, $personne->getDocuments()->toArray()),
                        'documentsOep' => array_map(function ($doc) {
                            return [
                                'id' => $doc->getId(),
                                'libelle' => $doc->getLibelle(),
                                'libelleGroupe' => $this->formatEntity($doc->getLibelleGroupe()),
                                'path' => $doc->getPath() ?  $this->formatFile($doc->getPath()) : null,
                            ];
                        }, $personne->getDocumentOeps()->toArray())

                    ]

                ];
            }, $etablissements);


            return $this->responseData($formattedProfessionnels, 'group_pro', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }


    private function formatEntity($entity): ?array
    {
        return $entity ? [
            'libelle' => $entity->getLibelle(),
            'id' => $entity->getId(),
        ] : null;
    }
    private function formatFile($file): ?array
    {
        return $file ? [
            'path' => $file->getPath(),
            'alt' => $file->getAlt(),
            'url' => $file->getPath() . "/" . $file->getAlt(),
        ] : null;
    }
    private function formatEntityFichier($entity): ?array
    {
        return $entity ? [
            'alt' => $entity->getAlt(),
            'path' => $entity->getPath(),
        ] : null;
    }




    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) Etablissement en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un etablissement en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Etablissement::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'etablissement')]
    //
    public function getOne(EtablissementRepository $etablissementRepository, UserRepository $userRepository, int $id)
    {
        try {
            $etablissement = $userRepository->findOneBy(['personne' => $id]);

            if (!$etablissement) {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                return $this->response('[]');
            }

            $personne = $etablissement->getPersonne();

            $responseData = [
                'username' => $etablissement->getUsername(),
                'id' => $etablissement->getId(),
                'email' => $etablissement->getEmail(),
                'typeUser' => $etablissement->getTypeUser(),
                'personne' => [
                    'id' => $personne->getId(),
                    'code' => $personne->getCode(), //
                    'type' => "etablissement",
                    'status' => $personne->getStatus(),
                    'createdAt' => $personne->getCreatedAt(),
                    'dateExamenRapport' => $personne->getDateExamenRapport(),
                    'rapportExamen' => $personne->getRapportExamen() ? $this->formatFile($personne->getRapportExamen()) : null,
                    'niveauIntervention' => $personne->getNiveauIntervention() ? $this->formatEntity($personne->getNiveauIntervention()) : null,
                    'dateVisite' => $personne->getDateVisite(),
                    'typePersonne' => $personne->getTypePersonne() ?  $this->formatEntity($personne->getTypePersonne()) : null,
                    'imputationData' => $personne->getImputation() ? [
                        'id' =>  $personne->getImputation()->getId(),
                        'username' =>  $personne->getImputation()->getUsername(),
                        'email' =>  $personne->getImputation()->getEmail(),
                    ] : null,
                    'denomination' => $personne->getDenomination(),
                    'typeSociete' => $personne->getTypeSociete(),
                    'nomRepresentant' => $personne->getNomRepresentant(),
                    'adresse' => $personne->getAdresse(),
                    'telephone' => $personne->getTelephone(),
                    'emailAutre' => $personne->getEmailAutre(),
                    'bp' => $personne->getBp(),
                    'nom' => $personne->getNom(),
                    'prenoms' => $personne->getPrenoms(),
                    'documents' => array_map(function ($doc) {
                        return [
                            'id' => $doc->getId(),
                            'libelle' => $doc->getLibelle(),
                            'libelleGroupe' => $this->formatEntity($doc->getLibelleGroupe()),
                            'path' => $doc->getPath() ?  $this->formatFile($doc->getPath()) : null,
                        ];
                    }, $personne->getDocuments()->toArray()),
                    'documentsOep' => array_map(function ($doc) {
                        return [
                            'id' => $doc->getId(),
                            'libelle' => $doc->getLibelle(),
                            'libelleGroupe' => $this->formatEntity($doc->getLibelleGroupe()),
                            'path' => $doc->getPath() ?  $this->formatFile($doc->getPath()) : null,
                        ];
                    }, $personne->getDocumentOeps()->toArray())

                ]

            ];

            return $this->responseData($responseData, 'group_pro', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }


    private function numero()
    {

        $query = $this->em->createQueryBuilder();
        $query->select("count(a.id)")
            ->from(User::class, 'a');

        $nb = $query->getQuery()->getSingleScalarResult();
        if ($nb == 0) {
            $nb = 1;
        } else {
            $nb = $nb + 1;
        }
        return str_pad($nb, 3, '0', STR_PAD_LEFT);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Création d'un établissement",
        description: "Permet de créer un nouvel établissement avec toutes les informations requises et documents joints.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "password", type: "string"),
                        new OA\Property(property: "confirmPassword", type: "string"),
                        new OA\Property(property: "email", type: "string"),
                        new OA\Property(property: "nom", type: "string"),
                        new OA\Property(property: "prenoms", type: "string"),
                        new OA\Property(property: "telephone", type: "string"),
                        new OA\Property(property: "typePersonne", type: "string"),
                        new OA\Property(property: "bp", type: "string"),
                        new OA\Property(property: "emailAutre", type: "string"),
                        new OA\Property(property: "adresse", type: "string"),
                        new OA\Property(property: "nomRepresentant", type: "string"),
                        new OA\Property(property: "denomination", type: "string"),
                        /*  new OA\Property(property: "reference", type: "string"), */
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
        responses: [
            new OA\Response(response: 201, description: "Établissement créé avec succès"),
            new OA\Response(response: 400, description: "Données invalides"),
            new OA\Response(response: 404, description: "Transaction introuvable")
        ]
    )]
    #[OA\Tag(name: 'etablissement')]
    public function update(
        int $id,
        Utils $utils,
        LibelleGroupeRepository $libelleGroupeRepository,
        Request $request,
        TypePersonneRepository $typePersonneRepository,
        EtablissementRepository $etablissementRepository,
        DocumentRepository $documentRepository,
        NiveauInterventionRepository $niveauInterventionRepository
    ): Response {
        $etablissement = $etablissementRepository->find($id);

        if (!$etablissement) {
            return $this->response("Établissement introuvable", 404);
        }

        // Vérification et mise à jour du typePersonne si fourni
        if ($request->get('typePersonne') !== null && $request->get('typePersonne') !== '') {
            $typePersonne = $typePersonneRepository->find($request->get('typePersonne'));
            if ($typePersonne) {
                $etablissement->setTypePersonne($typePersonne);
                $etablissement->setNiveauIntervention($niveauInterventionRepository->find($request->get('niveauIntervention')));

                // Mise à jour conditionnelle des champs selon le type de personne
                if ($typePersonne->getLibelle() === 'PHYSIQUE') {
                    if ($request->get('nom') !== null && $request->get('nom') !== '') {
                        $etablissement->setNom($request->get('nom'));
                    }
                    if ($request->get('prenoms') !== null && $request->get('prenoms') !== '') {
                        $etablissement->setPrenoms($request->get('prenoms'));
                    }
                    if ($request->get('bp') !== null && $request->get('bp') !== '') {
                        $etablissement->setBp($request->get('bp'));
                    }
                    if ($request->get('emailAutre') !== null && $request->get('emailAutre') !== '') {
                        $etablissement->setEmailAutre($request->get('emailAutre'));
                    }
                    if ($request->get('telephone') !== null && $request->get('telephone') !== '') {
                        $etablissement->setTelephone($request->get('telephone'));
                    }
                } else {
                    if ($request->get('denomination') !== null && $request->get('denomination') !== '') {
                        $etablissement->setDenomination($request->get('denomination'));
                    }
                    if ($request->get('typeSociete') !== null && $request->get('typeSociete') !== '') {
                        $etablissement->setTypeSociete($request->get('typeSociete'));
                    }
                    if ($request->get('adresse') !== null && $request->get('adresse') !== '') {
                        $etablissement->setAdresse($request->get('adresse'));
                    }
                    if ($request->get('nomRepresentant') !== null && $request->get('nomRepresentant') !== '') {
                        $etablissement->setNomRepresentant($request->get('nomRepresentant'));
                    }
                }
            }
        }

        // Gestion des documents existants
        $documentsData = $request->get('documents');
        $uploadedFiles = $request->files->get('documents');

        if ($documentsData && is_array($documentsData)) {
            $names = 'document_' . '01';
            $filePrefix = str_slug($names);
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);

            foreach ($documentsData as $index => $docData) {

                if (!empty($docData['id'])) {
                    $document = $documentRepository->find($docData['id']);

                    if ($document && $document->getEtablissement() === $etablissement) {
                        if (!empty($docData['libelle'])) {
                            $document->setLibelle($docData['libelle']);
                        }

                        if (!empty($docData['libelleGroupe'])) {
                            $libelleGroupe = $libelleGroupeRepository->find($docData['libelleGroupe']);
                            if ($libelleGroupe) {
                                $document->setLibelleGroupe($libelleGroupe);
                            }
                        }

                        // Gestion du fichier uploadé
                        if (isset($uploadedFiles[$index]['path'])) {
                            $uploadedFile = $uploadedFiles[$index]['path'];
                            if ($uploadedFile) {

                                // Sauvegarde du nouveau fichier
                                $fichier = $utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
                                if ($fichier) {
                                    $document->setPath($fichier);
                                }
                            }
                        }

                        $document->setUpdatedAt(new \DateTime());
                        if ($this->getUser()) {
                            $document->setUpdatedBy($this->getUser());;
                        }
                    }
                }
            }
        }


        $etablissement->setUpdatedAt(new \DateTime());
        if ($this->getUser()) {
            $etablissement->setUpdatedBy($this->getUser());;
        }

        $errorResponse = $this->errorResponse($etablissement);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $etablissementRepository->add($etablissement, true);

        return $this->responseData([
            'id' => $etablissement->getId(),
            'code' => $etablissement->getCode(),
            'status' => $etablissement->getStatus(),


        ], 'group_pro', ['Content-Type' => 'application/json']);
    }

    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) etablissement.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) etablissement',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Etablissement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'etablissement')]
    //
    public function delete(Request $request, Etablissement $etablissement, EtablissementRepository $etablissementRepository): Response
    {
        try {

            if ($etablissement != null) {

                $etablissementRepository->remove($etablissement, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($etablissement);
            } else {
                $this->setMessage("Cette ressource est inexistante");
                $this->setStatusCode(300);
                $response = $this->response('[]');
            }
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }
}
