<?php


namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\ActiveProfessionnelRequest;
use App\DTO\ProfessionnelDTO;
use App\Entity\Civilite;
use App\Entity\Etablissement;
use App\Entity\Organisation;
use App\Entity\Profession;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Professionnel;
use App\Entity\SituationProfessionnelle;
use App\Entity\User;
use App\Entity\ValidationWorkflow;
use App\Repository\CiviliteRepository;
use App\Repository\CodeGenerateurRepository;
use App\Repository\CommuneRepository;
use App\Repository\DistrictRepository;
use App\Repository\GenreRepository;
use App\Repository\LieuDiplomeRepository;
use App\Repository\OrganisationRepository;
use App\Repository\PaysRepository;
use App\Repository\ProfessionnelRepository;
use App\Repository\ProfessionRepository;
use App\Repository\RacineSequenceRepository;
use App\Repository\RegionRepository;
use App\Repository\SituationProfessionnelleRepository;
use App\Repository\SpecialiteRepository;
use App\Repository\StatusProRepository;
use App\Repository\TransactionRepository;
use App\Repository\TypeDiplomeRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use App\Service\PaiementService;
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

#[Route('/api/professionnel')]
class ApiProfessionnelController extends ApiInterface
{


    #[Route('/check/code/existe/{code}', methods: ['GET'])]
    /**
     * Affiche un(e) specialite en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche etat paiement de la specialite',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'specialite')]
    //
    public function codeExiste($code, ProfessionnelRepository $professionnelRepository)
    {
        try {

            $pro = $professionnelRepository->findOneBy(['code' => $code]);
            if ($pro != null) {
                $response = $this->response([
                    'statut'=>true,
                    'id'=>$pro->getId()
                ]);
            } else {

                $response = $this->response([
                    'statut'=>false,
                    'id'=>null
                ]);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }

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
    #[OA\Tag(name: 'professionnel')]
    /*  */
    public function updateImputation(Request $request, Professionnel $professionnel, ProfessionnelRepository $professionnelRepository, UserRepository $userRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($professionnel != null) {

                $professionnel->setImputation($userRepository->find($data->imputation));

                $professionnel->setUpdatedBy($userRepository->find($data->userUpdate));
                $professionnel->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($professionnel);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $professionnelRepository->add($professionnel, true);
                }

                // On retourne la confirmation
                $response = $this->responseData([
                    'error' => $errorResponse,
                    'id' => $professionnel->getId(),
                    'code' => $professionnel->getCode(),
                    'status' => $professionnel->getStatus(),
                    'nom' => $professionnel->getNom(),
                    'prenom' => $professionnel->getPrenoms(),
                    'email' => $professionnel->getEmail(),
                    'professionnel' => $professionnel->getProfessionnel(),

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



    #[Route('/existe/code/{code}', methods: ['GET'])]
    /**
     * Affiche un(e) specialite en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche etat paiement de la specialite',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Profession::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'professionnel')]
    //
    public function getExisteCode(
        string $code,
        ProfessionnelRepository $professionnelRepository,
        CodeGenerateurRepository $codeGenerateurRepository
    ): Response {
        try {
            // Utilisation de deux appels booléens pour alléger la logique
            $existsInCodeGenerateur = $codeGenerateurRepository->findOneBy(['code' => $code]) !== null;
            $existsInProfessionnel = $professionnelRepository->findOneBy(['code' => $code]) !== null;

            $this->setStatusCode(200);

            return $this->response([
                'verif' => $code != '' ? true : false,
                'exsiteInProfessionnel' => $existsInProfessionnel,
                'exsiteInCodeGenerateur' => $existsInCodeGenerateur,
            ]);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response([]);
        }
    }

    /* public function getExisteCode($code, ProfessionnelRepository $professionnelRepository, CodeGenerateurRepository $codeGenerateurRepository): Response
    {
        try {
            $codeGenerateur = $codeGenerateurRepository->findOneBy(['code' => $code]);
            $profession = $professionnelRepository->findOneBy(['code' => $code]);

            if ($codeGenerateur) {
                if ($profession) {
                    $response = $this->response([
                        "exsiteInProfessionnel" => true,
                        "exsiteInCodeGenerateur" => true,
                    ]);
                } else {

                    $this->setStatusCode(200);
                    $response = $this->response([
                        "exsiteInProfessionnel" => false,
                        "exsiteInCodeGenerateur" => true,
                    ]);
                }
            } else {
                if ($profession) {

                    $this->setStatusCode(200);
                    $response = $this->response([
                        "exsiteInProfessionnel" => true,
                        "exsiteInCodeGenerateur" => false,
                    ]);
                } else {

                    $this->setStatusCode(200);
                    $response = $this->response([
                        "exsiteInProfessionnel" => false,
                        "exsiteInCodeGenerateur" => false,
                    ]);
                }
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    } */



    #[Route('/imputation/list/{id}', name: 'app_professionnel_list_by_imputation', methods: ['GET'])]
    /**
     * Retourne la liste des professionnels liés à une imputation.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des professionnels',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'object') // détailler si nécessaire
        )
    )]
    #[OA\Tag(name: 'professionnel')]
    public function indexByImputation(
        ProfessionnelRepository $professionnelRepository,
        int $id,
        UserRepository $userRepository,
        ProfessionRepository $professionRepository
    ): Response {
        try {
            $professionnels = $userRepository->findActiveProfessionnelsByImputation($id);


            $formattedProfessionnels = array_filter(array_map(function ($professionnel) use ($professionRepository, $id) {
                $personne = $professionnel->getPersonne();
                if (!$personne || !$personne->getImputation()) return null;
                if ($personne->getImputation()->getId() !== $id) return null;

                // $profession = $personne->getProfession() ? $professionRepository->findOneByCode($personne->getProfession()) : null;

                return [
                    'username' => $professionnel->getUsername(),
                    'id' => $professionnel->getId(),
                    'email' => $professionnel->getEmail(),
                    'typeUser' => $professionnel->getTypeUser(),
                    'personne' => [
                        'profession' => $this->formatEntityProfession($personne->getProfession()),
                        'id' => $personne->getId(),
                        'imputation' => $personne->getImputation() ? $personne->getImputation()->getId() : null,
                        'imputationData' => $personne->getImputation() ? [
                            'id' =>  $personne->getImputation()->getId(),
                            'username' =>  $personne->getImputation()->getUsername(),
                            'email' =>  $personne->getImputation()->getEmail(),
                        ] : null,
                        'appartenirOrdre' => $personne->getAppartenirOrdre() ?? "",
                        'numeroInscription' => $personne->getNumeroInscription() ?? "",
                        'emailPro' => $personne->getEmailPro(),
                        'nom' => $personne->getNom(),
                        'lieuDiplome' => $personne->getLieuDiplome(),
                        'code' => $personne->getCode(),
                        'prenoms' => $personne->getPrenoms(),
                        'number' => $personne->getNumber(),
                        'email' => $personne->getEmail(),
                        'type' => "professionnel",
                        'status' => $personne->getStatus(),
                        'quartier' => $personne->getQuartier(),
                        'reason' => $personne->getReason() ?? "",
                        'professionnel' => $personne->getProfessionnel() ?? "",
                        'civilite' => $this->formatEntity($personne->getCivilite()),
                        'region' => $this->formatEntity($personne->getRegion()),
                        'typeDiplome' => $this->formatEntity($personne->getTypeDiplome()) ?? "",
                        'district' => $this->formatEntity($personne->getDistrict()),
                        'commune' => $this->formatEntity($personne->getCommune()),
                        'ville' => $this->formatEntity($personne->getVille()),
                        'nationate' => $this->formatEntity($personne->getNationate()),
                        'situationPro' => $this->formatEntity($personne->getSituationPro()),
                        'dateNaissance' => $this->formatDate($personne->getDateNaissance()),
                        'dateDiplome' => $this->formatDate($personne->getDateDiplome()),
                        'diplome' => $personne->getDiplome() ?? "",
                        'poleSanitaire' => $personne->getPoleSanitaire() ?? "",
                        'organisationNom' => $personne->getOrganisationNom() ?? "",
                        'poleSanitairePro' => $personne->getPoleSanitairePro() ?? "",
                        'lieuExercicePro' => $personne->getLieuExercicePro() ?? "",
                        'datePremierDiplome' => $this->formatDate($personne->getDatePremierDiplome()),
                        'situation' => $personne->getSituation() ?? "",
                        'appartenirOrganisation' => $personne->getAppartenirOrganisation() ?? "",
                        'photo' => $this->formatFile($personne->getPhoto()),
                        'cv' => $this->formatFile($personne->getCv()),
                        'casier' => $this->formatFile($personne->getCasier()),
                        'certificat' => $this->formatFile($personne->getCertificat()),
                        'diplomeFile' => $this->formatFile($personne->getDiplomeFile()),
                        'cni' => $this->formatFile($personne->getCni()),
                    ]

                ];
            }, $professionnels));

            return $this->responseData(array_values($formattedProfessionnels), 'group_pro', ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Une erreur est survenue'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }






    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des professionnels.
     * 
     */
    #[OA\Response(
        response: 200,
        description: ' Retourne la liste des professionnels',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Professionnel::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'professionnel')]
    // 
    public function index(ProfessionnelRepository $professionnelRepository, UserRepository $userRepository, ProfessionRepository $professionRepository): Response
    {

        try {
            //$professionnels = $userRepository->findActiveProfessionnelsByImputationWithouParam();
            $professionnels = $userRepository->findBy(['typeUser' => 'PROFESSIONNEL'], ['id' => 'DESC']);

            // dd($professionnels);

            $formattedProfessionnels = array_map(function ($professionnel) use ($professionRepository) {
                $personne = $professionnel->getPersonne();
                // $profession = $personne->getProfession() ? $professionRepository->findOneByCode($personne->getProfession()) : null;

                return [
                    'username' => $professionnel->getUsername(),
                    'id' => $professionnel->getId(),
                    'email' => $professionnel->getEmail(),
                    'typeUser' => $professionnel->getTypeUser(),
                    'personne' => [
                        'profession' => $this->formatEntityProfession($personne->getProfession()),
                        'id' => $personne->getId(),
                        'imputation' => $personne->getImputation() ? $personne->getImputation()->getId() : null,
                        'imputationData' => $personne->getImputation() ? [
                            'id' =>  $personne->getImputation()->getId(),
                            'username' =>  $personne->getImputation()->getUsername(),
                            'email' =>  $personne->getImputation()->getEmail(),
                        ] : null,
                        'appartenirOrdre' => $personne->getAppartenirOrdre() ?? "",
                        'numeroInscription' => $personne->getNumeroInscription() ?? "",
                        'emailPro' => $personne->getEmailPro(),
                        'nom' => $personne->getNom(),
                        'lieuDiplome' => $personne->getLieuDiplome(),
                        'code' => $personne->getCode(),
                        'prenoms' => $personne->getPrenoms(),
                        'number' => $personne->getNumber(),
                        'email' => $personne->getEmail(),
                        'type' => "professionnel",
                        'status' => $personne->getStatus(),
                        'quartier' => $personne->getQuartier(),
                        'reason' => $personne->getReason() ?? "",
                        'professionnel' => $personne->getProfessionnel() ?? "",
                        'civilite' => $this->formatEntity($personne->getCivilite()),
                        'region' => $this->formatEntity($personne->getRegion()),
                        'district' => $this->formatEntity($personne->getDistrict()),
                        'commune' => $this->formatEntity($personne->getCommune()),
                        'ville' => $this->formatEntity($personne->getVille()),
                        'nationate' => $this->formatEntity($personne->getNationate()),
                        'typeDiplome' => $this->formatEntity($personne->getTypeDiplome()) ?? "",
                        'situationPro' => $this->formatEntity($personne->getSituationPro()),
                        'dateNaissance' => $this->formatDate($personne->getDateNaissance()),
                        'dateDiplome' => $this->formatDate($personne->getDateDiplome()),
                        'diplome' => $personne->getDiplome() ?? "",
                        'poleSanitaire' => $personne->getPoleSanitaire() ?? "",
                        'organisationNom' => $personne->getOrganisationNom() ?? "",
                        'poleSanitairePro' => $personne->getPoleSanitairePro() ?? "",
                        'lieuExercicePro' => $personne->getLieuExercicePro() ?? "",
                        'datePremierDiplome' => $this->formatDate($personne->getDatePremierDiplome()),
                        'situation' => $personne->getSituation() ?? "",
                        'appartenirOrganisation' => $personne->getAppartenirOrganisation() ?? "",
                        'photo' => $this->formatFile($personne->getPhoto()),
                        'cv' => $this->formatFile($personne->getCv()),
                        'casier' => $this->formatFile($personne->getCasier()),
                        'certificat' => $this->formatFile($personne->getCertificat()),
                        'diplomeFile' => $this->formatFile($personne->getDiplomeFile()),
                        'cni' => $this->formatFile($personne->getCni()),
                    ]

                ];
            }, $professionnels);

            // Pour retourner en JSON (dans un contrôleur Symfony par exemple)

            $response = $this->responseData($formattedProfessionnels, 'group_pro', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $this->response('[]');
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/{status}', methods: ['GET'])]
    /**
     * Retourne la liste des professionnels par status.
     * 
     */
    #[OA\Response(
        response: 200,
        description: ' Retourne la liste des professionnels',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Professionnel::class, groups: ['full']))
        )
    )]

    #[OA\Tag(name: 'professionnel')]
    // 
    public function indexEtat(ProfessionnelRepository $professionnelRepository, $status, UserRepository $userRepository, ProfessionRepository $professionRepository): Response
    {
        try {


            // $professionnels = $userRepository->findActiveProfessionnelsByImputationWithouParam();
            $professionnels = $userRepository->getProfessionnelByetat($status);

            //$professionnels = $userRepository->findBy(['typeUser' => 'PROFESSIONNEL'], ['id' => 'DESC']);

            // dd($professionnels);

            $formattedProfessionnels = array_map(function ($professionnel) use ($professionRepository) {
                $personne = $professionnel->getPersonne();
                //$profession = $personne->getProfession() ? $professionRepository->findOneByCode($personne->getProfession()) : null;

                return [
                    'username' => $professionnel->getUsername(),
                    'id' => $professionnel->getId(),
                    'email' => $professionnel->getEmail(),
                    'typeUser' => $professionnel->getTypeUser(),
                    'personne' => [
                        'profession' => $this->formatEntityProfession($personne->getProfession()),
                        'id' => $personne->getId(),
                        'imputation' => $personne->getImputation() ? $personne->getImputation()->getId() : null,
                        'imputationData' => $personne->getImputation() ? [
                            'id' =>  $personne->getImputation()->getId(),
                            'username' =>  $personne->getImputation()->getUsername(),
                            'email' =>  $personne->getImputation()->getEmail(),
                        ] : null,
                        'appartenirOrdre' => $personne->getAppartenirOrdre() ?? "",
                        'numeroInscription' => $personne->getNumeroInscription() ?? "",
                        'emailPro' => $personne->getEmailPro(),
                        'nom' => $personne->getNom(),
                        'lieuDiplome' => $personne->getLieuDiplome(),
                        'code' => $personne->getCode(),
                        'prenoms' => $personne->getPrenoms(),
                        'number' => $personne->getNumber(),
                        'email' => $personne->getEmail(),
                        'type' => "professionnel",
                        'status' => $personne->getStatus(),
                        'quartier' => $personne->getQuartier(),
                        'reason' => $personne->getReason() ?? "",
                        'professionnel' => $personne->getProfessionnel() ?? "",
                        'typeDiplome' => $this->formatEntity($personne->getTypeDiplome()) ?? "",
                        'civilite' => $this->formatEntity($personne->getCivilite()),
                        'region' => $this->formatEntity($personne->getRegion()),
                        'district' => $this->formatEntity($personne->getDistrict()),
                        'commune' => $this->formatEntity($personne->getCommune()),
                        'ville' => $this->formatEntity($personne->getVille()),
                        'nationate' => $this->formatEntity($personne->getNationate()),
                        'situationPro' => $this->formatEntity($personne->getSituationPro()),
                        'dateNaissance' => $this->formatDate($personne->getDateNaissance()),
                        'dateDiplome' => $this->formatDate($personne->getDateDiplome()),
                        'diplome' => $personne->getDiplome() ?? "",
                        'poleSanitaire' => $personne->getPoleSanitaire() ?? "",
                        'organisationNom' => $personne->getOrganisationNom() ?? "",
                        'poleSanitairePro' => $personne->getPoleSanitairePro() ?? "",
                        'lieuExercicePro' => $personne->getLieuExercicePro() ?? "",
                        'datePremierDiplome' => $this->formatDate($personne->getDatePremierDiplome()),
                        'situation' => $personne->getSituation() ?? "",
                        'appartenirOrganisation' => $personne->getAppartenirOrganisation() ?? "",
                        'photo' => $this->formatFile($personne->getPhoto()),
                        'cv' => $this->formatFile($personne->getCv()),
                        'casier' => $this->formatFile($personne->getCasier()),
                        'certificat' => $this->formatFile($personne->getCertificat()),
                        'diplomeFile' => $this->formatFile($personne->getDiplomeFile()),
                        'cni' => $this->formatFile($personne->getCni()),
                    ]

                ];
            }, $professionnels);

            $response = $this->responseData($formattedProfessionnels, 'group_pro', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

    public function numeroGeneration($professionnel, $professionCode, $racine)
    {

        $civilite = $professionnel->getCivilite()->getCodeGeneration();
        $anneeInscription = $professionnel->getCreatedAt()->format('y');
        $jour = $professionnel->getDateNaissance()->format('d');
        $annee = $professionnel->getDateNaissance()->format('y');


        $query = $this->em->createQueryBuilder();
        $query->select("COUNT(a.id) as total")
            ->from(Professionnel::class, 'a');

        $nb = intval($query->getQuery()->getSingleScalarResult());


        $nb = ($nb + 1) % 10000;
        if ($nb == 0) {
            $nb = 1;
        }

        return sprintf(
            "%s%s0%s%s%s%s.%04d",
            $racine,
            $civilite,
            $anneeInscription,
            $professionCode,
            $jour,
            $annee,
            $nb
        );
    }



    #[Route('/active/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Accepter ou refuser un professionnel",
        description: "Permet d'accepter ou de refuser un professionnel.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string"),
                    new OA\Property(property: "raison", type: "string", nullable: true),
                    new OA\Property(property: "email", type: "string", nullable: true),
                  
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 400, description: "Données invalides"),
            new OA\Response(response: 404, description: "Professionnel non trouvé"),
            new OA\Response(response: 200, description: "Mise à jour réussie")
        ]
    )]
    #[OA\Tag(name: 'professionnel')]
    public function active(
        Request $request,
        Professionnel $professionnel,
        ProfessionnelRepository $professionnelRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        Registry $workflowRegistry,
        ProfessionRepository $professionRepository,
        Utils $utils,
        RacineSequenceRepository $racineSequenceRepository,
        SendMailService $sendMailService // Injecter le Registry
    ): Response {
        try {


            $data = json_decode($request->getContent(), true);

            $dto = new ActiveProfessionnelRequest();
            $dto->status = $data['status'] ?? null;
            $dto->raison = $data['raison'] ?? null;

            $errors = $validator->validate($dto);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $validationCompteWorkflow = $workflowRegistry->get($professionnel);

            // Vérifier la transition du workflow
            if (!$validationCompteWorkflow->can($professionnel, $dto->status)) {
                return new JsonResponse([
                    'error' => "Transition non valide depuis l'état actuel"
                ], Response::HTTP_BAD_REQUEST);
            }

            $validationCompteWorkflow->apply($professionnel, $dto->status);

            if ($dto->status == "validation") {
                $profession = $professionnel->getProfession();
                $professionCode = $profession->getCodeGeneration();
                $professionChronoMax = $profession->getChronoMax();
                $professionMaxCode = $profession->getMaxCode();
                $code = $profession->getCode();

                $numeroGenere = $utils->numeroGeneration($professionnel->getCivilite()->getCodeGeneration(), $professionnel->getDateNaissance() ?? new \DateTime(), $professionnel->getCreatedAt() ?? new \DateTime(), $racineSequenceRepository->findOneBySomeField()->getCode(), $professionMaxCode, "new", $professionCode, $code);

                $professionnel->setCode($numeroGenere);
                $professionnel->setDateValidation(new DateTime());
                //$professionnel->setCode($this->numeroGeneration($professionnel, $professionCode, $racineSequenceRepository->findOneBySomeField()->getCode()));
            }
            $professionnel->setReason($dto->raison);
            $professionnelRepository->add($professionnel, true);
            $validationWorkflow = new ValidationWorkflow();
            $validationWorkflow->setEtape($dto->status);
            $validationWorkflow->setRaison($dto->raison);
            $validationWorkflow->setPersonne($professionnel);
            $validationWorkflow->setCreatedAtValue(new DateTime());
            $validationWorkflow->setUpdatedAt(new DateTime());
            $validationWorkflow->setCreatedBy($this->getUser());
            $validationWorkflow->setUpdatedBy($this->getUser());

            $this->em->persist($validationWorkflow);
            $this->em->flush();

            if ($dto->status == "validation") {
                $profession->setMaxCode(substr($numeroGenere, -4));
                $this->em->persist($profession);
                $this->em->flush();
            }

            $message = "";

            if ($dto->status == "acceptation") {
                $message = "Votre dossier vient de passer l'etape d'acceptation et est en séance d'analyse";
            } elseif ($dto->status == "rejet") {
                $message = "Votre dossier vient de passer d'être réjeté pour la raison suivante: " . $dto->raison;
            } elseif ($dto->status == "refuse") {

                $message = "Votre dossier vient de passer d'être réfusé pour la raison suivante: " . $dto->raison;
            } elseif ($dto->status == "validation") {
                $message = "Votre dossier a été jugé conforme et est désormais en attente de validation finale. Vous recevrez une notification dès que le processus sera complété.";
            }
            $user = $userRepository->find($data['userUpdate']);


            $info_user = [
                'user' => $user->getUserIdentifier(),
                'nom' => $professionnel->getNom() . ' ' . $professionnel->getPrenoms(),
                'profession' => $profession->getLibelle(),
                'etape' => $dto->status,
                'message' => $message,
                'annee' => $professionnel->getCreatedAt()->format('Y'),
            ];

            //  dd($info_user);

            $context = compact('info_user');

            // TO DO


            $sendMailService->send(
                'depps@leadagro.net',
                $data['email'],
                'Validaton du dossier',
                'content_validation',
                $context
            );



            $sendMailService->sendNotification("votre compte vient d'être valider pour l'etape " . $dto->status, $userRepository->findOneBy(['personne' => $professionnel->getId()]), $userRepository->find($data['userUpdate']));

            return $this->responseData($info_user, 'group_pro', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {

           // dd($exception->getMessage());
            return $this->json(["message" => "Une erreur est survenue"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) professionnel en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Professionnel::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'professionnel')]
    public function getOne(
        ProfessionnelRepository $professionnelRepository,
        UserRepository $userRepository,
        ProfessionRepository $professionRepository,
        $id
    ) {
        try {
            $professionnel = $userRepository->findOneBy(['personne' => $id]);

            if (!$professionnel) {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                return $this->response('[]');
            }

            $personne = $professionnel->getPersonne();
            $profession = $personne->getProfession();

            $responseData = [
                'username' => $professionnel->getUsername(),
                'id' => $professionnel->getId(),
                'email' => $professionnel->getEmail(),
                'typeUser' => $professionnel->getTypeUser(),
                'personne' => [
                    'profession' => $this->formatEntityProfession($profession),
                    'id' => $personne->getId(),
                    /* 'organisationNom' => $personne->getOrganisationNom(), */
                    'emailPro' => $personne->getEmailPro(),
                    'nom' => $personne->getNom(),
                    'lieuDiplome' => $personne->getLieuDiplome(),
                    'code' => $personne->getCode(),
                    'prenoms' => $personne->getPrenoms(),
                    'number' => $personne->getNumber(),
                    'email' => $personne->getEmail(),
                    'type' => "professionnel",
                    'status' => $personne->getStatus(),
                    'quartier' => $personne->getQuartier(),
                    'reason' => $personne->getReason() ?? "",
                    'professionnel' => $personne->getProfessionnel() ?? "",
                    'civilite' => $this->formatEntity($personne->getCivilite()),
                    'region' => $this->formatEntity($personne->getRegion()),
                    'district' => $this->formatEntity($personne->getDistrict()),
                    'lieuObtentionDiplome' => $personne->getLieuObtentionDiplome() ?  $this->formatEntity($personne->getLieuObtentionDiplome()) : null,
                    'commune' => $personne->getCommune() ?  $this->formatEntity($personne->getCommune()) : null,
                    'ville' => $this->formatEntity($personne->getVille()),
                    'nationate' => $this->formatEntity($personne->getNationate()),
                    'situationPro' => $this->formatEntity($personne->getSituationPro()),
                    'dateNaissance' => $this->formatDate($personne->getDateNaissance()),
                    'dateDiplome' => $this->formatDate($personne->getDateDiplome()),
                    'diplome' => $personne->getDiplome() ?? "",
                    'typeDiplome' => $this->formatEntity($personne->getTypeDiplome()) ?? "",
                    'poleSanitaire' => $personne->getPoleSanitaire() ?? "",
                    'specialiteAutre' => $personne->getSpecialiteAutre() ?? "",
                    'organisationNom' => $personne->getOrganisationNom() ?? "",
                    'poleSanitairePro' => $personne->getPoleSanitairePro() ?? "",
                    'lieuExercicePro' => $personne->getLieuExercicePro() ?? "",
                    'datePremierDiplome' => $this->formatDate($personne->getDatePremierDiplome()),
                    'situation' => $personne->getSituation() ?? "",
                    'appartenirOrganisation' => $personne->getAppartenirOrganisation() ?? "",
                    'appartenirOrdre' => $personne->getAppartenirOrdre() ?? "",
                    'numeroInscription' => $personne->getNumeroInscription() ?? "",
                    'photo' => $personne->getPhoto() ? $this->formatFile($personne->getPhoto()) : null,
                    'cv' => $personne->getCv() ? $this->formatFile($personne->getCv()) : null,
                    'casier' => $personne->getCasier() ? $this->formatFile($personne->getCasier()) : null,
                    'certificat' => $personne->getCertificat() ? $this->formatFile($personne->getCertificat()) : null,
                    'diplomeFile' => $personne->getDiplomeFile() ? $this->formatFile($personne->getDiplomeFile()) : null,
                    'cni' => $personne->getCni() ? $this->formatFile($personne->getCni()) : null,
                ]
            ];

            return $this->responseData($responseData, 'group_pro', ['Content-Type' => 'application/json']);
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

    private function formatFile($file): ?array
    {
        return $file ? [
            'path' => $file->getPath(),
            'alt' => $file->getAlt(),
            'url' => $file->getPath() . "/" . $file->getAlt(),
        ] : null;
    }


    private function formatDate($date): ?string
    {
        return $date ? $date->format('Y-m-d') : "";
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


    #[Route('/create', name: 'create_professionnel', methods: ['POST'])]
    /**
     * Permet de créer un(e) professionnel.
     */
    #[OA\Post(
        summary: "Creation de professionnel",
        description: "Permet de crtéer un professionnel.",

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        // etatpe 1
                        new OA\Property(property: "password", type: "string"), // username
                        new OA\Property(property: "confirmPassword", type: "string"), // username
                        new OA\Property(property: "email", type: "string"),


                        // etatpe 2


                        new OA\Property(property: "code", type: "string"), //pole sanitaire
                        new OA\Property(property: "poleSanitaire", type: "string"), //pole sanitaire
                        new OA\Property(property: "region", type: "string"), //pole sanitaire
                        new OA\Property(property: "district", type: "string"), //pole sanitaire
                        new OA\Property(property: "ville", type: "string"), //pole sanitaire
                        new OA\Property(property: "commune", type: "string"), //pole sanitaire
                        new OA\Property(property: "quartier", type: "string"), //pole sanitaire

                        new OA\Property(property: "nom", type: "string"), //first_name 
                        new OA\Property(property: "professionnel", type: "string"), //professionnel
                        new OA\Property(property: "prenoms", type: "string"),
                        new OA\Property(property: "lieuExercicePro", type: "string"), //lieu_exercice_pro
                        new OA\Property(property: "emailAutre", type: "string"), //email

                        // etatpe 3

                        new OA\Property(property: "profession", type: "string"), //profession bouton radio
                        new OA\Property(property: "civilite", type: "string"), //civilite select
                        new OA\Property(property: "emailPro", type: "string"), //email_pro
                        new OA\Property(property: "dateDiplome", type: "string"), //dateDiplome date
                        new OA\Property(property: "dateNaissance", type: "string"), //dateNaissance date
                        new OA\Property(property: "numero", type: "string"), //contact
                        new OA\Property(property: "lieuDiplome", type: "string"), //lieu au obtention premier diplome
                        new OA\Property(property: "nationalite", type: "string"), //nationalite select
                        new OA\Property(property: "situation", type: "string"), //situation matrimonial
                        new OA\Property(property: "datePremierDiplome", type: "string"), //datePremierDiplome
                        new OA\Property(property: "poleSanitairePro", type: "string"), //contactPerso
                        new OA\Property(property: "diplome", type: "string"), //diplome
                        new OA\Property(property: "situationPro", type: "string"), //situation_pro


                        // etatpe 4


                        new OA\Property(property: "photo", type: "string", format: "binary"), //photo
                        new OA\Property(property: "cni", type: "string", format: "binary"), //cni
                        new OA\Property(property: "casier", type: "string", format: "binary"), //casier
                        new OA\Property(property: "diplomeFile", type: "string", format: "binary"), //diplomeFile
                        new OA\Property(property: "certificat", type: "string", format: "binary"), //certificat
                        new OA\Property(property: "cv", type: "string", format: "binary"), //cv

                        // etatpe 5


                        new OA\Property(property: "appartenirOrganisation", type: "boolean"), // oui ou non
                        new OA\Property(property: "organisationNom", type: "string"),
                        /*  new OA\Property(property: "organisationNumero", type: "string"),
                        new OA\Property(property: "organisationAnnee", type: "string"), */
                        new OA\Property(property: "reference", type: "string"),
                        new OA\Property(property: "specialiteAutre", type: "string"),


                    ],
                    type: "object"
                )
            )

        ),


        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'professionnel')]

    public function create(
        Request $request,
        SessionInterface $session,
        SendMailService $sendMailService,
        TransactionRepository $transactionRepository,
        VilleRepository $villeRepository,
        CiviliteRepository $civiliteRepository,
        SpecialiteRepository $specialiteRepository,
        GenreRepository $genreRepository,
        ProfessionnelRepository $professionnelRepository,
        PaysRepository $paysRepository,
        OrganisationRepository $organisationRepository,
        PaiementService $paiementService,
        SituationProfessionnelleRepository $situationProfessionnelleRepository,
        RegionRepository $regionRepository,
        DistrictRepository $districtRepository,
        CommuneRepository $communeRepository,
        TypeDiplomeRepository $typeDiplomeRepository,
        StatusProRepository $statusProRepository,
        LieuDiplomeRepository $lieuDiplomeRepository,
        CodeGenerateurRepository $codeGenerateurRepository,
        ProfessionRepository $professionRepository
    ): Response {


        $names = 'document_' . '01';
        $filePrefix  = str_slug($names);
        $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);



        $user = new User();
        $user->setEmail($request->get('email'));
        $user->setPassword($this->hasher->hashPassword($user, $request->get('password')));
        $user->setRoles(['ROLE_MEMBRE']);
        $user->setTypeUser(User::TYPE['PROFESSIONNEL']);
        $user->setPayement(User::PAYEMENT['payed']);
        $user->setUpdatedAt(new DateTime());
        $user->setCreatedAtValue(new DateTime());


        $errorResponse1 = $request->get('password') !== $request->get('confirmPassword') ?  $this->errorResponse($user, "Les mots de passe ne sont pas identiques") :  $this->errorResponse($user);
        if ($errorResponse1 !== null) {
            return $errorResponse1; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $this->userRepository->add($user, true);

            $professionnel = new Professionnel();

            //ETAPE 2
            if ($request->get('code') && $codeGenerateurRepository->findOneBy(['code' => $request->get('code')])) {
                $professionnel->setCode($request->get('code'));
                $professionnel->setStatus("renouvellement");
            } else {
                $professionnel->setStatus("attente");
            }
            $professionnel->setPoleSanitaire($request->get('poleSanitaire'));
            $professionnel->setSpecialiteAutre($request->get('specialiteAutre'));
            $professionnel->setRegion($regionRepository->find($request->get('region')));
            $professionnel->setDistrict($districtRepository->find($request->get('district')));
            $professionnel->setVille($villeRepository->find($request->get('ville')));
            $professionnel->setCommune($communeRepository->find($request->get('commune')));
            $professionnel->setQuartier($request->get('quartier'));
            $professionnel->setNom($request->get('nom'));
            $professionnel->setProfessionnel($request->get('professionnel'));
            $professionnel->setPrenoms($request->get('prenoms'));
            $professionnel->setEmail($request->get('emailAutre'));
            $professionnel->setLieuExercicePro($request->get('lieuExercicePro'));

            //ETAPE 3

            $professionnel->setProfession($professionRepository->find($request->get('profession')));
            $professionnel->setCivilite($civiliteRepository->find($request->get('civilite')));
            $professionnel->setEmailPro($request->get('emailPro'));
            $professionnel->setDateDiplome(new DateTimeImmutable($request->get('dateDiplome')));
            $professionnel->setDateNaissance(new DateTimeImmutable($request->get('dateNaissance')));
            $professionnel->setNumber($request->get('numero'));
            $professionnel->setLieuDiplome($request->get('lieuDiplome'));
            $professionnel->setLieuObtentionDiplome($lieuDiplomeRepository->find($request->get('lieuObtentionDiplome')));
            $professionnel->setNationate($paysRepository->find($request->get('nationalite')));
            $professionnel->setSituation($request->get('situation'));
            $professionnel->setDatePremierDiplome(new DateTimeImmutable($request->get('datePremierDiplome')));
            $professionnel->setPoleSanitairePro($request->get('poleSanitairePro'));
            $professionnel->setDiplome($request->get('diplome'));
            $professionnel->setSituationPro($situationProfessionnelleRepository->find($request->get('situationPro')));
            $professionnel->setStatusPro($statusProRepository->find($request->get('statusPro')));
            $professionnel->setTypeDiplome($typeDiplomeRepository->find($request->get('typeDiplome')));
            $professionnel->setAppartenirOrganisation($request->get('appartenirOrganisation'));
            $professionnel->setAppartenirOrdre($request->get('appartenirOrdre'));
            if ($request->get('appartenirOrganisation') == "oui") {


                $professionnel->setOrganisationNom($request->get('organisationNom'));
            }
            if ($request->get('appartenirOrdre') == "oui") {


                $professionnel->setNumeroInscription($request->get('numeroInscription'));
            }


            $professionnel->setUpdatedAt(new DateTime());
            $professionnel->setCreatedAtValue(new DateTime());


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


            /* $professionnel->setUser($user); */


            $professionnel->setCreatedBy($user);
            $professionnel->setUpdatedBy($user);

            $errorResponse = $this->errorResponse($professionnel);
            $errorResponseUser = $this->errorResponse($user);
            if ($errorResponse !== null) {
                return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
            } else {

                if ($errorResponseUser !== null) {
                    return $errorResponseUser;
                } else {
                    $professionnelRepository->add($professionnel, true);
                    $user->setPersonne($professionnel);
                    $this->userRepository->add($user, true);
                }
                $info_user = [
                    'login' => $request->get('email'),

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


        return $this->responseData([
            'id' => $professionnel->getId(),
            'code' => $professionnel->getCode(),
            'status' => $professionnel->getStatus(),
            'nom' => $professionnel->getNom(),
            'prenom' => $professionnel->getPrenoms(),
            'email' => $professionnel->getEmail(),
            'professionnel' => $professionnel->getProfessionnel(),

        ], 'group_pro', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Update de professionnel",
        description: "Permet de créer un professionnel.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        // etatpe 1
                        new OA\Property(property: "password", type: "string"), // username
                        new OA\Property(property: "confirmPassword", type: "string"), // username
                        new OA\Property(property: "email", type: "string"),


                        // etatpe 2


                        new OA\Property(property: "poleSanitaire", type: "string"), //pole sanitaire
                        new OA\Property(property: "region", type: "string"), //pole sanitaire
                        new OA\Property(property: "district", type: "string"), //pole sanitaire
                        new OA\Property(property: "ville", type: "string"), //pole sanitaire
                        new OA\Property(property: "commune", type: "string"), //pole sanitaire
                        new OA\Property(property: "quartier", type: "string"), //pole sanitaire
                        new OA\Property(property: "nom", type: "string"), //first_name 
                        new OA\Property(property: "professionnel", type: "string"), //professionnel
                        new OA\Property(property: "prenoms", type: "string"),
                        new OA\Property(property: "lieuExercicePro", type: "string"), //lieu_exercice_pro
                        new OA\Property(property: "emailAutre", type: "string"), //email

                        // etatpe 3

                        new OA\Property(property: "profession", type: "string"), //profession bouton radio
                        new OA\Property(property: "civilite", type: "string"), //civilite select
                        new OA\Property(property: "emailPro", type: "string"), //email_pro
                        new OA\Property(property: "dateDiplome", type: "string"), //dateDiplome date
                        new OA\Property(property: "dateNaissance", type: "string"), //dateNaissance date
                        new OA\Property(property: "numero", type: "string"), //contact
                        new OA\Property(property: "lieuDiplome", type: "string"), //lieu au obtention premier diplome
                        new OA\Property(property: "nationalite", type: "string"), //nationalite select
                        new OA\Property(property: "situation", type: "string"), //situation matrimonial
                        new OA\Property(property: "datePremierDiplome", type: "string"), //datePremierDiplome
                        new OA\Property(property: "poleSanitairePro", type: "string"), //contactPerso
                        new OA\Property(property: "diplome", type: "string"), //diplome
                        new OA\Property(property: "situationPro", type: "string"), //situation_pro


                        // etatpe 4


                        new OA\Property(property: "photo", type: "string", format: "binary"), //photo
                        new OA\Property(property: "cni", type: "string", format: "binary"), //cni
                        new OA\Property(property: "casier", type: "string", format: "binary"), //casier
                        new OA\Property(property: "diplomeFile", type: "string", format: "binary"), //diplomeFile
                        new OA\Property(property: "certificat", type: "string", format: "binary"), //certificat
                        new OA\Property(property: "cv", type: "string", format: "binary"), //cv

                        // etatpe 5


                        new OA\Property(property: "appartenirOrganisation", type: "boolean"), // oui ou non
                        new OA\Property(property: "organisationNom", type: "string"),

                        new OA\Property(property: "reference", type: "string"),
                        new OA\Property(property: "specialiteAutre", type: "string"),




                    ],
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'professionnel')]
    public function update(
        Request $request,
        SituationProfessionnelleRepository $situationProfessionnelleRepository,
        RegionRepository $regionRepository,
        DistrictRepository $districtRepository,
        CommuneRepository $communeRepository,
        VilleRepository $villeRepository,
        PaysRepository $paysRepository,
        CiviliteRepository $civiliteRepository,
        Professionnel $professionnel,
        SpecialiteRepository $specialiteRepository,
        GenreRepository $genreRepository,
        ProfessionnelRepository $professionnelRepository,
        OrganisationRepository $organisationRepository,
        LieuDiplomeRepository $lieuDiplomeRepository,
        TypeDiplomeRepository $typeDiplomeRepository,
        StatusProRepository $statusProRepository
    ): Response {
        try {
            $names = 'document_' . '01';
            $filePrefix  = str_slug($names);
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);

            //return $this->responseData($professionnel, 'group_pro', ['Content-Type' => 'application/json']);
            if ($professionnel) {
                //ETAPE 2
                /* $professionnel->setCode($request->get('code')); */
                if (!empty($request->get('poleSanitaire'))) {
                    $professionnel->setPoleSanitaire($request->get('poleSanitaire'));
                }
                if (!empty($request->get('region'))) {
                    $professionnel->setRegion($regionRepository->find($request->get('region')));
                }
                if (!empty($request->get('district'))) {
                    $professionnel->setDistrict($districtRepository->find($request->get('district')));
                }
                if (!empty($request->get('ville'))) {
                    $professionnel->setVille($villeRepository->find($request->get('ville')));
                }
                if (!empty($request->get('commune'))) {
                    $professionnel->setCommune($communeRepository->find($request->get('commune')));
                }
                if (!empty($request->get('quartier'))) {
                    $professionnel->setQuartier($request->get('quartier'));
                }
                if (!empty($request->get('nom'))) {
                    $professionnel->setNom($request->get('nom'));
                }
                if (!empty($request->get('professionnel'))) {
                    $professionnel->setProfessionnel($request->get('professionnel'));
                }
                if (!empty($request->get('prenoms'))) {
                    $professionnel->setPrenoms($request->get('prenoms'));
                }
                if (!empty($request->get('emailAutre'))) {
                    $professionnel->setEmail($request->get('emailAutre'));
                }
                if (!empty($request->get('lieuExercicePro'))) {
                    $professionnel->setLieuExercicePro($request->get('lieuExercicePro'));
                }
                if (!empty($request->get('civilite'))) {
                    $professionnel->setCivilite($civiliteRepository->find($request->get('civilite')));
                }
                if (!empty($request->get('emailPro'))) {
                    $professionnel->setEmailPro($request->get('emailPro'));
                }
                if (!empty($request->get('dateDiplome'))) {
                    $professionnel->setDateDiplome(new DateTimeImmutable($request->get('dateDiplome')));
                }
                if (!empty($request->get('dateNaissance'))) {
                    $professionnel->setDateNaissance(new DateTimeImmutable($request->get('dateNaissance')));
                }
                if (!empty($request->get('numero'))) {
                    $professionnel->setNumber($request->get('numero'));
                }
                if (!empty($request->get('lieuDiplome'))) {
                    $professionnel->setLieuDiplome($request->get('lieuDiplome'));
                }
                if (!empty($request->get('typeDiplome'))) {

                    $professionnel->setTypeDiplome($typeDiplomeRepository->find($request->get('typeDiplome')));
                }
                if (!empty($request->get('statusPro'))) {
                    $professionnel->setStatusPro($statusProRepository->find($request->get('statusPro')));
                }


                if (!empty($request->get('lieuObtentionDiplome'))) {
                    $professionnel->setLieuObtentionDiplome($lieuDiplomeRepository->find($request->get('lieuObtentionDiplome')));
                }
                if (!empty($request->get('nationalite'))) {
                    $professionnel->setNationate($paysRepository->find($request->get('nationalite')));
                }
                if (!empty($request->get('situation'))) {
                    $professionnel->setSituation($request->get('situation'));
                }
                if (!empty($request->get('datePremierDiplome'))) {
                    $professionnel->setDatePremierDiplome(new DateTimeImmutable($request->get('datePremierDiplome')));
                }
                if (!empty($request->get('poleSanitairePro'))) {
                    $professionnel->setPoleSanitairePro($request->get('poleSanitairePro'));
                }
                if (!empty($request->get('diplome'))) {
                    $professionnel->setDiplome($request->get('diplome'));
                }
                if (!empty($request->get('diplospecialiteAutreme'))) {
                    $professionnel->setSpecialiteAutre($request->get('specialiteAutre'));
                }
                if (!empty($request->get('situationPro'))) {
                    $professionnel->setSituationPro($situationProfessionnelleRepository->find($request->get('situationPro')));
                }



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

                $professionnel->setAppartenirOrganisation($request->get('appartenirOrganisation'));
                $professionnel->setAppartenirOrdre($request->get('appartenirOrdre'));

                /* $professionnel->setCreatedBy($this->getUser());
                $professionnel->setUpdatedBy($this->getUser()); */

                $errorResponse = $this->errorResponse($professionnel);

                if ($request->get('appartenirOrganisation') == "oui") {
                    $professionnel->setOrganisationNom($request->get('organisationNom'));
                } else {
                    $professionnel->setOrganisationNom("");
                }

                if ($request->get('appartenirOrdre') == "oui") {
                    $professionnel->setNumeroInscription($request->get('numeroInscription'));
                } else {
                    $professionnel->setOrganisationNom("");
                }

                $professionnelRepository->add($professionnel, true);
                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $professionnelRepository->add($professionnel, true);
                }
                $response = $this->responseData([
                    'error' => $errorResponse,
                    'id' => $professionnel->getId(),
                    'code' => $professionnel->getCode(),
                    'status' => $professionnel->getStatus(),
                    'nom' => $professionnel->getNom(),
                    'prenom' => $professionnel->getPrenoms(),
                    'email' => $professionnel->getEmail(),
                    'professionnel' => $professionnel->getProfessionnel(),

                ], 'group_pro', ['Content-Type' => 'application/json']);
            }
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }

    //const TAB_ID = 'parametre-tabs';

    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) professionnel.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) professionnel',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Professionnel::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'professionnel')]
    //
    public function delete(Request $request, Professionnel $professionnel, ProfessionnelRepository $professionnelRepository): Response
    {
        try {

            if ($professionnel != null) {

                $professionnelRepository->remove($professionnel, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->responseData([

                    'id' => $professionnel->getId(),
                    'code' => $professionnel->getCode(),
                    'status' => $professionnel->getStatus(),
                    'nom' => $professionnel->getNom(),
                    'prenom' => $professionnel->getPrenoms(),
                    'email' => $professionnel->getEmail(),
                    'professionnel' => $professionnel->getProfessionnel(),

                ], 'group_pro', ['Content-Type' => 'application/json']);
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
    #[Route('/desactive/{id}',  methods: ['PUT', 'POST'])]
    /**
     * permet de supprimer un(e) professionnel.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) professionnel',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Professionnel::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'professionnel')]
    //
    public function desactive(Request $request, Professionnel $professionnel, ProfessionnelRepository $professionnelRepository): Response
    {
        try {

            if ($professionnel != null) {

                $professionnel->setActived(false);

                $professionnelRepository->add($professionnel, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");

                $response = $this->responseData([

                    'id' => $professionnel->getId(),
                    'code' => $professionnel->getCode(),
                    'status' => $professionnel->getStatus(),
                    'nom' => $professionnel->getNom(),
                    'prenom' => $professionnel->getPrenoms(),
                    'email' => $professionnel->getEmail(),
                    'professionnel' => $professionnel->getProfessionnel(),

                ], 'group_pro', ['Content-Type' => 'application/json']);
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

    #[Route('/delete/all',  methods: ['DELETE'])]
    /**
     * Permet de supprimer plusieurs professionnel.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Professionnel::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'professionnel')]

    public function deleteAll(Request $request, ProfessionnelRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $professionnel = $villeRepository->find($value['id']);

                if ($professionnel != null) {
                    $villeRepository->remove($professionnel);
                }
            }
            $this->setMessage("Operation effectuées avec success");
            $response = $this->response('[]');
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }
}
