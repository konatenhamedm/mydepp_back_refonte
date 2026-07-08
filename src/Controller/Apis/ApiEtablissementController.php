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
use App\Repository\TypeDemandeEtablissementRepository;
use App\Repository\TypeEtablissementRepository;
use App\Repository\NatureEtablissementRepository;
use App\Repository\TypeOrganisationRepository;
use App\Repository\RegionRepository;
use App\Repository\DistrictRepository;
use App\Repository\StatutJuridiqueRepository;
use App\Repository\NiveauFormationRepository;
use App\Repository\ResponsabiliteMedicolegaleRepository;
use App\Repository\StatusProRepository;
use App\Repository\OrganismeEnregistrementRepository;
use App\Repository\ServiceRepository;
use App\Repository\CertificationQualiteRepository;
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
                $etablissement->setUpdatedAt();
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
                if ($etablissement->getNiveauIntervention()->getMontant() != null) {
                    $etablissement->setDateValidation(new \DateTime());
                }

                $etablissement->setCode($this->genererCodeEtablissement());
            }

            $etablissementRepository->add($etablissement, true);

            $validationWorkflow = new ValidationWorkflow();
            $validationWorkflow->setEtape($dto->status);
            $validationWorkflow->setRaison($dto->raison);
            $validationWorkflow->setPersonne($etablissement);
            $validationWorkflow->setCreatedAtValue(new \DateTimeImmutable());
            $validationWorkflow->setUpdatedAt(new \DateTimeImmutable());
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
    public function create(UserPasswordHasherInterface $hasher, NiveauInterventionRepository $niveauInterventionRepository, Utils $utils, LibelleGroupeRepository $libelleGroupeRepository, Request $request, SessionInterface $session, SendMailService $sendMailService, TransactionRepository $transactionRepository, GenreRepository $genreRepository, EtablissementRepository $etablissementRepository, TypePersonneRepository $typePersonneRepository, TypeDemandeEtablissementRepository $typeDemandeEtablissementRepository, TypeEtablissementRepository $typeEtablissementRepository, NatureEtablissementRepository $natureEtablissementRepository, TypeOrganisationRepository $typeOrganisationRepository, RegionRepository $regionRepository, DistrictRepository $districtRepository, StatutJuridiqueRepository $statutJuridiqueRepository, CiviliteRepository $civiliteRepository, ProfessionRepository $professionRepository, ResponsabiliteMedicolegaleRepository $responsabiliteMedicolegaleRepository, NiveauFormationRepository $niveauFormationRepository, StatusProRepository $statusProRepository, OrganismeEnregistrementRepository $organismeEnregistrementRepository, ServiceRepository $serviceRepository, CertificationQualiteRepository $certificationQualiteRepository): Response
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
                if ($request->get('civilite')) {
                    $etablissement->setCivilite($civiliteRepository->find($request->get('civilite')));
                }
                if ($request->get('profession')) {
                    $etablissement->setProfession($professionRepository->find($request->get('profession')));
                }
                $etablissement->setCniNumero($request->get('cniNumero'));
                $etablissement->setWhatsappPersonnel($request->get('whatsappPersonnel'));
            } else {
                $etablissement->setDenomination($request->get('denomination'));
                $etablissement->setTypeSociete($request->get('typeSociete'));
                $etablissement->setAdresse($request->get('adresse'));
                $etablissement->setNomRepresentant($request->get('nomRepresentant'));
                if ($request->get('statutJuridique')) {
                    $etablissement->setStatutJuridique($statutJuridiqueRepository->find($request->get('statutJuridique')));
                }
                if ($request->get('representantCivilite')) {
                    $etablissement->setRepresentantCivilite($civiliteRepository->find($request->get('representantCivilite')));
                }
                $etablissement->setRepresentantQualite($request->get('representantQualite'));
                $etablissement->setRepresentantCni($request->get('representantCni'));
                $etablissement->setRepresentantTelephone($request->get('representantTelephone'));
                $etablissement->setRepresentantWhatsapp($request->get('representantWhatsapp'));
                $etablissement->setRepresentantEmail($request->get('representantEmail'));
            }

            // Responsable médicolégal
            if ($request->get('responsableCivilite')) {
                $etablissement->setResponsableCivilite($civiliteRepository->find($request->get('responsableCivilite')));
            }
            $etablissement->setResponsableNom($request->get('responsableNom'));
            if ($request->get('responsabiliteMedicolegale')) {
                $etablissement->setResponsabiliteMedicolegale($responsabiliteMedicolegaleRepository->find($request->get('responsabiliteMedicolegale')));
            }
            $etablissement->setResponsableProfession($request->get('responsableProfession'));
            $etablissement->setResponsableDiplome($request->get('responsableDiplome'));
            $etablissement->setResponsableSpecialite($request->get('responsableSpecialite'));
            if ($request->get('responsableNiveauFormation')) {
                $etablissement->setResponsableNiveauFormation($niveauFormationRepository->find($request->get('responsableNiveauFormation')));
            }
            if ($request->get('responsableStatutAdministratif')) {
                $etablissement->setResponsableStatutAdministratif($statusProRepository->find($request->get('responsableStatutAdministratif')));
            }
            $etablissement->setResponsableEmail($request->get('responsableEmail'));
            $etablissement->setResponsableTelephone($request->get('responsableTelephone'));
            $etablissement->setResponsableWhatsapp($request->get('responsableWhatsapp'));
            $etablissement->setResponsableNumeroOrdre($request->get('responsableNumeroOrdre'));
            $etablissement->setResponsableCni($request->get('responsableCni'));

            // Structure
            if ($request->get('typeDemandeEtablissement')) {
                $etablissement->setTypeDemandeEtablissement($typeDemandeEtablissementRepository->find($request->get('typeDemandeEtablissement')));
            }
            if ($request->get('typeEtablissement')) {
                $etablissement->setTypeEtablissement($typeEtablissementRepository->find($request->get('typeEtablissement')));
            }
            if ($request->get('natureEtablissement')) {
                $etablissement->setNatureEtablissement($natureEtablissementRepository->find($request->get('natureEtablissement')));
            }
            if ($request->get('typeOrganisation')) {
                $etablissement->setTypeOrganisation($typeOrganisationRepository->find($request->get('typeOrganisation')));
            }
            if ($request->get('accordMinistere') !== null && $request->get('accordMinistere') !== '') {
                $etablissement->setAccordMinistere(filter_var($request->get('accordMinistere'), FILTER_VALIDATE_BOOLEAN));
            }
            if ($request->get('dateValiditeAccord')) {
                $etablissement->setDateValiditeAccord(new DateTime($request->get('dateValiditeAccord')));
            }
            $etablissement->setAnneeCreation($request->get('anneeCreation'));
            if ($request->get('enregistreeDepps') !== null && $request->get('enregistreeDepps') !== '') {
                $etablissement->setEnregistreeDepps(filter_var($request->get('enregistreeDepps'), FILTER_VALIDATE_BOOLEAN));
            }
            $etablissement->setNumeroEnregistrement($request->get('numeroEnregistrement'));
            if ($request->get('organismeEnregistrement')) {
                $etablissement->setOrganismeEnregistrement($organismeEnregistrementRepository->find($request->get('organismeEnregistrement')));
            }
            $etablissement->setAnneeAutorisation($request->get('anneeAutorisation'));
            if ($request->get('aCertificatConformite') !== null && $request->get('aCertificatConformite') !== '') {
                $etablissement->setACertificatConformite(filter_var($request->get('aCertificatConformite'), FILTER_VALIDATE_BOOLEAN));
            }
            if ($request->get('dateValiditeCertificat')) {
                $etablissement->setDateValiditeCertificat(new DateTime($request->get('dateValiditeCertificat')));
            }
            $etablissement->setHoraireOuverture($request->get('horaireOuverture'));
            $etablissement->setAutreHoraireOuverture($request->get('autreHoraireOuverture'));

            // Contrôle Qualité et Services
            if ($request->get('aAccreditation') !== null && $request->get('aAccreditation') !== '') {
                $etablissement->setAAccreditation(filter_var($request->get('aAccreditation'), FILTER_VALIDATE_BOOLEAN));
            }
            if ($request->get('engagementProcessusAccreditation') !== null && $request->get('engagementProcessusAccreditation') !== '') {
                $etablissement->setEngagementProcessusAccreditation(filter_var($request->get('engagementProcessusAccreditation'), FILTER_VALIDATE_BOOLEAN));
            }
            if ($request->get('certificationQualite')) {
                $etablissement->setCertificationQualite($certificationQualiteRepository->find($request->get('certificationQualite')));
            }
            $etablissement->setAutresCertification($request->get('autresCertification'));
            $services = $request->get('services');
            if (is_array($services)) {
                foreach ($services as $serviceId) {
                    $service = $serviceRepository->find($serviceId);
                    if ($service) {
                        $etablissement->addService($service);
                    }
                }
            }

            // Adresses et Contacts
            if ($request->get('region')) {
                $etablissement->setRegion($regionRepository->find($request->get('region')));
            }
            if ($request->get('district')) {
                $etablissement->setDistrict($districtRepository->find($request->get('district')));
            }
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
                $newDocument->setCreatedAtValue();
                $newDocument->setUpdatedAt();


                $etablissement->addDocument($newDocument);
            }



            $etablissement->setCreatedBy($user);
            $etablissement->setUpdatedBy($user);
            $etablissement->setCreatedAtValue();
            $etablissement->setUpdatedAt();

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
                        'latitude' => $personne->getLatitude(),
                        'longitude' => $personne->getLongitude(),
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
                        'typeDemandeEtablissement' => $personne->getTypeDemandeEtablissement() ? $this->formatEntity($personne->getTypeDemandeEtablissement()) : null,
                    'typeEtablissement' => $personne->getTypeEtablissement() ? $this->formatEntity($personne->getTypeEtablissement()) : null,
                        'natureEtablissement' => $personne->getNatureEtablissement() ? $this->formatEntity($personne->getNatureEtablissement()) : null,
                        'typeOrganisation' => $personne->getTypeOrganisation() ? $this->formatEntity($personne->getTypeOrganisation()) : null,
                        'accordMinistere' => $personne->isAccordMinistere(),
                        'dateValiditeAccord' => $personne->getDateValiditeAccord(),
                        'region' => $personne->getRegion() ? $this->formatEntity($personne->getRegion()) : null,
                        'district' => $personne->getDistrict() ? $this->formatEntity($personne->getDistrict()) : null,
                        'villeVillage' => $personne->getVilleVillage(),
                        'commune' => $personne->getCommune(),
                        'quartier' => $personne->getQuartier(),
                        'zoneSecteur' => $personne->getZoneSecteur(),
                        'villaImmeubleEtagePorte' => $personne->getVillaImmeubleEtagePorte(),
                        'ilotNumero' => $personne->getIlotNumero(),
                        'lotNumero' => $personne->getLotNumero(),
                        'rueAvenue' => $personne->getRueAvenue(),
                        'pointDeRepere' => $personne->getPointDeRepere(),
                        'adresseElectronique' => $personne->getAdresseElectronique(),
                        'telephoneFixe' => $personne->getTelephoneFixe(),
                        'whatsapp' => $personne->getWhatsapp(),
                        'telephoneMobile' => $personne->getTelephoneMobile(),
                        'telephoneAutre' => $personne->getTelephoneAutre(),
                        'adressePostale' => $personne->getAdressePostale(),
                    'statutJuridique' => $personne->getStatutJuridique() ? $this->formatEntity($personne->getStatutJuridique()) : null,
                    'civilite' => $personne->getCivilite() ? $this->formatEntity($personne->getCivilite()) : null,
                    'profession' => $personne->getProfession() ? $this->formatEntity($personne->getProfession()) : null,
                    'cniNumero' => $personne->getCniNumero(),
                    'whatsappPersonnel' => $personne->getWhatsappPersonnel(),
                    'representantCivilite' => $personne->getRepresentantCivilite() ? $this->formatEntity($personne->getRepresentantCivilite()) : null,
                    'representantQualite' => $personne->getRepresentantQualite(),
                    'representantCni' => $personne->getRepresentantCni(),
                    'representantTelephone' => $personne->getRepresentantTelephone(),
                    'representantWhatsapp' => $personne->getRepresentantWhatsapp(),
                    'representantEmail' => $personne->getRepresentantEmail(),
                    'responsableCivilite' => $personne->getResponsableCivilite() ? $this->formatEntity($personne->getResponsableCivilite()) : null,
                    'responsableNom' => $personne->getResponsableNom(),
                    'responsabiliteMedicolegale' => $personne->getResponsabiliteMedicolegale() ? $this->formatEntity($personne->getResponsabiliteMedicolegale()) : null,
                    'responsableProfession' => $personne->getResponsableProfession(),
                    'responsableDiplome' => $personne->getResponsableDiplome(),
                    'responsableSpecialite' => $personne->getResponsableSpecialite(),
                    'responsableNiveauFormation' => $personne->getResponsableNiveauFormation() ? $this->formatEntity($personne->getResponsableNiveauFormation()) : null,
                    'responsableStatutAdministratif' => $personne->getResponsableStatutAdministratif() ? $this->formatEntity($personne->getResponsableStatutAdministratif()) : null,
                    'responsableEmail' => $personne->getResponsableEmail(),
                    'responsableTelephone' => $personne->getResponsableTelephone(),
                    'responsableWhatsapp' => $personne->getResponsableWhatsapp(),
                    'responsableNumeroOrdre' => $personne->getResponsableNumeroOrdre(),
                    'responsableCni' => $personne->getResponsableCni(),
                    'anneeCreation' => $personne->getAnneeCreation(),
                    'enregistreeDepps' => $personne->isEnregistreeDepps(),
                    'numeroEnregistrement' => $personne->getNumeroEnregistrement(),
                    'organismeEnregistrement' => $personne->getOrganismeEnregistrement() ? $this->formatEntity($personne->getOrganismeEnregistrement()) : null,
                    'anneeAutorisation' => $personne->getAnneeAutorisation(),
                    'aCertificatConformite' => $personne->isACertificatConformite(),
                    'dateValiditeCertificat' => $personne->getDateValiditeCertificat(),
                    'horaireOuverture' => $personne->getHoraireOuverture(),
                    'autreHoraireOuverture' => $personne->getAutreHoraireOuverture(),
                    'aAccreditation' => $personne->isAAccreditation(),
                    'engagementProcessusAccreditation' => $personne->isEngagementProcessusAccreditation(),
                    'certificationQualite' => $personne->getCertificationQualite() ? $this->formatEntity($personne->getCertificationQualite()) : null,
                    'autresCertification' => $personne->getAutresCertification(),
                    'services' => array_map(function ($service) {
                        return $this->formatEntity($service);
                    }, $personne->getServices()->toArray()),
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
                    'latitude' => $personne->getLatitude(),
                    'longitude' => $personne->getLongitude(),
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
                    'typeDemandeEtablissement' => $personne->getTypeDemandeEtablissement() ? $this->formatEntity($personne->getTypeDemandeEtablissement()) : null,
                    'typeEtablissement' => $personne->getTypeEtablissement() ? $this->formatEntity($personne->getTypeEtablissement()) : null,
                    'natureEtablissement' => $personne->getNatureEtablissement() ? $this->formatEntity($personne->getNatureEtablissement()) : null,
                    'typeOrganisation' => $personne->getTypeOrganisation() ? $this->formatEntity($personne->getTypeOrganisation()) : null,
                    'accordMinistere' => $personne->isAccordMinistere(),
                    'dateValiditeAccord' => $personne->getDateValiditeAccord(),
                    'region' => $personne->getRegion() ? $this->formatEntity($personne->getRegion()) : null,
                    'district' => $personne->getDistrict() ? $this->formatEntity($personne->getDistrict()) : null,
                    'villeVillage' => $personne->getVilleVillage(),
                    'commune' => $personne->getCommune(),
                    'quartier' => $personne->getQuartier(),
                    'zoneSecteur' => $personne->getZoneSecteur(),
                    'villaImmeubleEtagePorte' => $personne->getVillaImmeubleEtagePorte(),
                    'ilotNumero' => $personne->getIlotNumero(),
                    'lotNumero' => $personne->getLotNumero(),
                    'rueAvenue' => $personne->getRueAvenue(),
                    'pointDeRepere' => $personne->getPointDeRepere(),
                    'adresseElectronique' => $personne->getAdresseElectronique(),
                    'telephoneFixe' => $personne->getTelephoneFixe(),
                    'whatsapp' => $personne->getWhatsapp(),
                    'telephoneMobile' => $personne->getTelephoneMobile(),
                    'telephoneAutre' => $personne->getTelephoneAutre(),
                    'adressePostale' => $personne->getAdressePostale(),
                    'statutJuridique' => $personne->getStatutJuridique() ? $this->formatEntity($personne->getStatutJuridique()) : null,
                    'civilite' => $personne->getCivilite() ? $this->formatEntity($personne->getCivilite()) : null,
                    'profession' => $personne->getProfession() ? $this->formatEntity($personne->getProfession()) : null,
                    'cniNumero' => $personne->getCniNumero(),
                    'whatsappPersonnel' => $personne->getWhatsappPersonnel(),
                    'representantCivilite' => $personne->getRepresentantCivilite() ? $this->formatEntity($personne->getRepresentantCivilite()) : null,
                    'representantQualite' => $personne->getRepresentantQualite(),
                    'representantCni' => $personne->getRepresentantCni(),
                    'representantTelephone' => $personne->getRepresentantTelephone(),
                    'representantWhatsapp' => $personne->getRepresentantWhatsapp(),
                    'representantEmail' => $personne->getRepresentantEmail(),
                    'responsableCivilite' => $personne->getResponsableCivilite() ? $this->formatEntity($personne->getResponsableCivilite()) : null,
                    'responsableNom' => $personne->getResponsableNom(),
                    'responsabiliteMedicolegale' => $personne->getResponsabiliteMedicolegale() ? $this->formatEntity($personne->getResponsabiliteMedicolegale()) : null,
                    'responsableProfession' => $personne->getResponsableProfession(),
                    'responsableDiplome' => $personne->getResponsableDiplome(),
                    'responsableSpecialite' => $personne->getResponsableSpecialite(),
                    'responsableNiveauFormation' => $personne->getResponsableNiveauFormation() ? $this->formatEntity($personne->getResponsableNiveauFormation()) : null,
                    'responsableStatutAdministratif' => $personne->getResponsableStatutAdministratif() ? $this->formatEntity($personne->getResponsableStatutAdministratif()) : null,
                    'responsableEmail' => $personne->getResponsableEmail(),
                    'responsableTelephone' => $personne->getResponsableTelephone(),
                    'responsableWhatsapp' => $personne->getResponsableWhatsapp(),
                    'responsableNumeroOrdre' => $personne->getResponsableNumeroOrdre(),
                    'responsableCni' => $personne->getResponsableCni(),
                    'anneeCreation' => $personne->getAnneeCreation(),
                    'enregistreeDepps' => $personne->isEnregistreeDepps(),
                    'numeroEnregistrement' => $personne->getNumeroEnregistrement(),
                    'organismeEnregistrement' => $personne->getOrganismeEnregistrement() ? $this->formatEntity($personne->getOrganismeEnregistrement()) : null,
                    'anneeAutorisation' => $personne->getAnneeAutorisation(),
                    'aCertificatConformite' => $personne->isACertificatConformite(),
                    'dateValiditeCertificat' => $personne->getDateValiditeCertificat(),
                    'horaireOuverture' => $personne->getHoraireOuverture(),
                    'autreHoraireOuverture' => $personne->getAutreHoraireOuverture(),
                    'aAccreditation' => $personne->isAAccreditation(),
                    'engagementProcessusAccreditation' => $personne->isEngagementProcessusAccreditation(),
                    'certificationQualite' => $personne->getCertificationQualite() ? $this->formatEntity($personne->getCertificationQualite()) : null,
                    'autresCertification' => $personne->getAutresCertification(),
                    'services' => array_map(function ($service) {
                        return $this->formatEntity($service);
                    }, $personne->getServices()->toArray()),
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
        NiveauInterventionRepository $niveauInterventionRepository,
        TypeDemandeEtablissementRepository $typeDemandeEtablissementRepository,
        TypeEtablissementRepository $typeEtablissementRepository,
        NatureEtablissementRepository $natureEtablissementRepository,
        TypeOrganisationRepository $typeOrganisationRepository,
        RegionRepository $regionRepository,
        DistrictRepository $districtRepository,
        StatutJuridiqueRepository $statutJuridiqueRepository,
        CiviliteRepository $civiliteRepository,
        ProfessionRepository $professionRepository,
        ResponsabiliteMedicolegaleRepository $responsabiliteMedicolegaleRepository,
        NiveauFormationRepository $niveauFormationRepository,
        StatusProRepository $statusProRepository,
        OrganismeEnregistrementRepository $organismeEnregistrementRepository,
        ServiceRepository $serviceRepository,
        CertificationQualiteRepository $certificationQualiteRepository
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
                    if ($request->get('civilite') !== null && $request->get('civilite') !== '') {
                        $etablissement->setCivilite($civiliteRepository->find($request->get('civilite')));
                    }
                    if ($request->get('profession') !== null && $request->get('profession') !== '') {
                        $etablissement->setProfession($professionRepository->find($request->get('profession')));
                    }
                    if ($request->get('cniNumero') !== null && $request->get('cniNumero') !== '') {
                        $etablissement->setCniNumero($request->get('cniNumero'));
                    }
                    if ($request->get('whatsappPersonnel') !== null && $request->get('whatsappPersonnel') !== '') {
                        $etablissement->setWhatsappPersonnel($request->get('whatsappPersonnel'));
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
                    if ($request->get('statutJuridique') !== null && $request->get('statutJuridique') !== '') {
                        $etablissement->setStatutJuridique($statutJuridiqueRepository->find($request->get('statutJuridique')));
                    }
                    if ($request->get('representantCivilite') !== null && $request->get('representantCivilite') !== '') {
                        $etablissement->setRepresentantCivilite($civiliteRepository->find($request->get('representantCivilite')));
                    }
                    if ($request->get('representantQualite') !== null && $request->get('representantQualite') !== '') {
                        $etablissement->setRepresentantQualite($request->get('representantQualite'));
                    }
                    if ($request->get('representantCni') !== null && $request->get('representantCni') !== '') {
                        $etablissement->setRepresentantCni($request->get('representantCni'));
                    }
                    if ($request->get('representantTelephone') !== null && $request->get('representantTelephone') !== '') {
                        $etablissement->setRepresentantTelephone($request->get('representantTelephone'));
                    }
                    if ($request->get('representantWhatsapp') !== null && $request->get('representantWhatsapp') !== '') {
                        $etablissement->setRepresentantWhatsapp($request->get('representantWhatsapp'));
                    }
                    if ($request->get('representantEmail') !== null && $request->get('representantEmail') !== '') {
                        $etablissement->setRepresentantEmail($request->get('representantEmail'));
                    }
                }
            }
        }

        // Responsable médicolégal
        if ($request->get('responsableCivilite') !== null && $request->get('responsableCivilite') !== '') {
            $etablissement->setResponsableCivilite($civiliteRepository->find($request->get('responsableCivilite')));
        }
        if ($request->get('responsableNom') !== null && $request->get('responsableNom') !== '') {
            $etablissement->setResponsableNom($request->get('responsableNom'));
        }
        if ($request->get('responsabiliteMedicolegale') !== null && $request->get('responsabiliteMedicolegale') !== '') {
            $etablissement->setResponsabiliteMedicolegale($responsabiliteMedicolegaleRepository->find($request->get('responsabiliteMedicolegale')));
        }
        if ($request->get('responsableProfession') !== null && $request->get('responsableProfession') !== '') {
            $etablissement->setResponsableProfession($request->get('responsableProfession'));
        }
        if ($request->get('responsableDiplome') !== null && $request->get('responsableDiplome') !== '') {
            $etablissement->setResponsableDiplome($request->get('responsableDiplome'));
        }
        if ($request->get('responsableSpecialite') !== null && $request->get('responsableSpecialite') !== '') {
            $etablissement->setResponsableSpecialite($request->get('responsableSpecialite'));
        }
        if ($request->get('responsableNiveauFormation') !== null && $request->get('responsableNiveauFormation') !== '') {
            $etablissement->setResponsableNiveauFormation($niveauFormationRepository->find($request->get('responsableNiveauFormation')));
        }
        if ($request->get('responsableStatutAdministratif') !== null && $request->get('responsableStatutAdministratif') !== '') {
            $etablissement->setResponsableStatutAdministratif($statusProRepository->find($request->get('responsableStatutAdministratif')));
        }
        if ($request->get('responsableEmail') !== null && $request->get('responsableEmail') !== '') {
            $etablissement->setResponsableEmail($request->get('responsableEmail'));
        }
        if ($request->get('responsableTelephone') !== null && $request->get('responsableTelephone') !== '') {
            $etablissement->setResponsableTelephone($request->get('responsableTelephone'));
        }
        if ($request->get('responsableWhatsapp') !== null && $request->get('responsableWhatsapp') !== '') {
            $etablissement->setResponsableWhatsapp($request->get('responsableWhatsapp'));
        }
        if ($request->get('responsableNumeroOrdre') !== null && $request->get('responsableNumeroOrdre') !== '') {
            $etablissement->setResponsableNumeroOrdre($request->get('responsableNumeroOrdre'));
        }
        if ($request->get('responsableCni') !== null && $request->get('responsableCni') !== '') {
            $etablissement->setResponsableCni($request->get('responsableCni'));
        }

        // Structure
        if ($request->get('typeDemandeEtablissement') !== null && $request->get('typeDemandeEtablissement') !== '') {
            $etablissement->setTypeDemandeEtablissement($typeDemandeEtablissementRepository->find($request->get('typeDemandeEtablissement')));
        }
        if ($request->get('typeEtablissement') !== null && $request->get('typeEtablissement') !== '') {
            $etablissement->setTypeEtablissement($typeEtablissementRepository->find($request->get('typeEtablissement')));
        }
        if ($request->get('natureEtablissement') !== null && $request->get('natureEtablissement') !== '') {
            $etablissement->setNatureEtablissement($natureEtablissementRepository->find($request->get('natureEtablissement')));
        }
        if ($request->get('typeOrganisation') !== null && $request->get('typeOrganisation') !== '') {
            $etablissement->setTypeOrganisation($typeOrganisationRepository->find($request->get('typeOrganisation')));
        }
        if ($request->get('accordMinistere') !== null && $request->get('accordMinistere') !== '') {
            $etablissement->setAccordMinistere(filter_var($request->get('accordMinistere'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->get('dateValiditeAccord') !== null && $request->get('dateValiditeAccord') !== '') {
            $etablissement->setDateValiditeAccord(new DateTime($request->get('dateValiditeAccord')));
        }
        if ($request->get('anneeCreation') !== null && $request->get('anneeCreation') !== '') {
            $etablissement->setAnneeCreation($request->get('anneeCreation'));
        }
        if ($request->get('enregistreeDepps') !== null && $request->get('enregistreeDepps') !== '') {
            $etablissement->setEnregistreeDepps(filter_var($request->get('enregistreeDepps'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->get('numeroEnregistrement') !== null && $request->get('numeroEnregistrement') !== '') {
            $etablissement->setNumeroEnregistrement($request->get('numeroEnregistrement'));
        }
        if ($request->get('organismeEnregistrement') !== null && $request->get('organismeEnregistrement') !== '') {
            $etablissement->setOrganismeEnregistrement($organismeEnregistrementRepository->find($request->get('organismeEnregistrement')));
        }
        if ($request->get('anneeAutorisation') !== null && $request->get('anneeAutorisation') !== '') {
            $etablissement->setAnneeAutorisation($request->get('anneeAutorisation'));
        }
        if ($request->get('aCertificatConformite') !== null && $request->get('aCertificatConformite') !== '') {
            $etablissement->setACertificatConformite(filter_var($request->get('aCertificatConformite'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->get('dateValiditeCertificat') !== null && $request->get('dateValiditeCertificat') !== '') {
            $etablissement->setDateValiditeCertificat(new DateTime($request->get('dateValiditeCertificat')));
        }
        if ($request->get('horaireOuverture') !== null && $request->get('horaireOuverture') !== '') {
            $etablissement->setHoraireOuverture($request->get('horaireOuverture'));
        }
        if ($request->get('autreHoraireOuverture') !== null && $request->get('autreHoraireOuverture') !== '') {
            $etablissement->setAutreHoraireOuverture($request->get('autreHoraireOuverture'));
        }

        // Contrôle Qualité et Services
        if ($request->get('aAccreditation') !== null && $request->get('aAccreditation') !== '') {
            $etablissement->setAAccreditation(filter_var($request->get('aAccreditation'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->get('engagementProcessusAccreditation') !== null && $request->get('engagementProcessusAccreditation') !== '') {
            $etablissement->setEngagementProcessusAccreditation(filter_var($request->get('engagementProcessusAccreditation'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->get('certificationQualite') !== null && $request->get('certificationQualite') !== '') {
            $etablissement->setCertificationQualite($certificationQualiteRepository->find($request->get('certificationQualite')));
        }
        if ($request->get('autresCertification') !== null && $request->get('autresCertification') !== '') {
            $etablissement->setAutresCertification($request->get('autresCertification'));
        }
        $services = $request->get('services');
        if (is_array($services)) {
            foreach ($etablissement->getServices() as $existingService) {
                $etablissement->removeService($existingService);
            }
            foreach ($services as $serviceId) {
                $service = $serviceRepository->find($serviceId);
                if ($service) {
                    $etablissement->addService($service);
                }
            }
        }

        // Adresses et Contacts
        if ($request->get('region') !== null && $request->get('region') !== '') {
            $etablissement->setRegion($regionRepository->find($request->get('region')));
        }
        if ($request->get('district') !== null && $request->get('district') !== '') {
            $etablissement->setDistrict($districtRepository->find($request->get('district')));
        }
        if ($request->get('villeVillage') !== null && $request->get('villeVillage') !== '') {
            $etablissement->setVilleVillage($request->get('villeVillage'));
        }
        if ($request->get('commune') !== null && $request->get('commune') !== '') {
            $etablissement->setCommune($request->get('commune'));
        }
        if ($request->get('quartier') !== null && $request->get('quartier') !== '') {
            $etablissement->setQuartier($request->get('quartier'));
        }
        if ($request->get('zoneSecteur') !== null && $request->get('zoneSecteur') !== '') {
            $etablissement->setZoneSecteur($request->get('zoneSecteur'));
        }
        if ($request->get('villaImmeubleEtagePorte') !== null && $request->get('villaImmeubleEtagePorte') !== '') {
            $etablissement->setVillaImmeubleEtagePorte($request->get('villaImmeubleEtagePorte'));
        }
        if ($request->get('ilotNumero') !== null && $request->get('ilotNumero') !== '') {
            $etablissement->setIlotNumero($request->get('ilotNumero'));
        }
        if ($request->get('lotNumero') !== null && $request->get('lotNumero') !== '') {
            $etablissement->setLotNumero($request->get('lotNumero'));
        }
        if ($request->get('rueAvenue') !== null && $request->get('rueAvenue') !== '') {
            $etablissement->setRueAvenue($request->get('rueAvenue'));
        }
        if ($request->get('pointDeRepere') !== null && $request->get('pointDeRepere') !== '') {
            $etablissement->setPointDeRepere($request->get('pointDeRepere'));
        }
        if ($request->get('adresseElectronique') !== null && $request->get('adresseElectronique') !== '') {
            $etablissement->setAdresseElectronique($request->get('adresseElectronique'));
        }
        if ($request->get('telephoneFixe') !== null && $request->get('telephoneFixe') !== '') {
            $etablissement->setTelephoneFixe($request->get('telephoneFixe'));
        }
        if ($request->get('whatsapp') !== null && $request->get('whatsapp') !== '') {
            $etablissement->setWhatsapp($request->get('whatsapp'));
        }
        if ($request->get('telephoneMobile') !== null && $request->get('telephoneMobile') !== '') {
            $etablissement->setTelephoneMobile($request->get('telephoneMobile'));
        }
        if ($request->get('telephoneAutre') !== null && $request->get('telephoneAutre') !== '') {
            $etablissement->setTelephoneAutre($request->get('telephoneAutre'));
        }
        if ($request->get('adressePostale') !== null && $request->get('adressePostale') !== '') {
            $etablissement->setAdressePostale($request->get('adressePostale'));
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
                                $fichier = $utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH, $document->getPath());
                                if ($fichier) {
                                    $document->setPath($fichier);
                                }
                            }
                        }

                        $document->setUpdatedAt();
                        if ($this->getUser()) {
                            $document->setUpdatedBy($this->getUser());;
                        }
                    }
                }
            }
        }


        $etablissement->setUpdatedAt();
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
