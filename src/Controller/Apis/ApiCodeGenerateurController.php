<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\CodeGenerateurDTO;
use App\Entity\Civilite;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\CodeGenerateur;
use App\Repository\CiviliteRepository;
use App\Repository\CodeGenerateurRepository;
use App\Repository\ProfessionRepository;
use App\Repository\RacineSequenceRepository;
use App\Repository\UserRepository;
use App\Service\Utils;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/codeGenerateur')]
class ApiCodeGenerateurController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des codeGenerateurs.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: CodeGenerateur::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'codeGenerateur')]
    // 
    public function index(CodeGenerateurRepository $codeGenerateurRepository): Response
    {
        try {

            $codeGenerateurs = $codeGenerateurRepository->findBy([],["id"=>"ASC"]);

          

            $response =  $this->responseData($codeGenerateurs, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) codeGenerateur en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) codeGenerateur en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: CodeGenerateur::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'codeGenerateur')]
    //
    public function getOne(?CodeGenerateur $codeGenerateur)
    {
        try {
            if ($codeGenerateur) {
                $response = $this->response($codeGenerateur);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($codeGenerateur);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) codeGenerateur.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "dateNaissance", type: "string"),
                    new OA\Property(property: "profession", type: "string"),
                    new OA\Property(property: "civilite", type: "string"),
                    new OA\Property(property: "dateCreation", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'codeGenerateur')]
    
    public function create(Request $request,Utils $utils,RacineSequenceRepository $racineSequenceRepository, CodeGenerateurRepository $codeGenerateurRepository,CiviliteRepository $civiliteRepository,ProfessionRepository $professionRepository): Response
    {
        $data = json_decode($request->getContent(), true);

        $profession = $professionRepository->find($data['profession']);
        $civilite = $civiliteRepository->find($data['civilite']);
        $dataNaissance = new DateTimeImmutable($data['dateNaissance']);
        $dateCreation = new DateTimeImmutable($data['dateCreation']);
        $racine = $racineSequenceRepository->findOneBySomeField()->getCode();

        $codeGenerateur = new CodeGenerateur();
        $codeGenerateur->setDateNaissance($dataNaissance);
        $codeGenerateur->setCivilite($civilite);
        $codeGenerateur->setProfession($profession);
        $codeGenerateur->setDateCreation($dateCreation);
        $codeGenerateur->setCode($utils->numeroGeneration($civilite->getCodeGeneration(),$dataNaissance,$dateCreation,$racine,null,"old",$profession->getCodeGeneration(),$profession->getCode()));
        $codeGenerateur->setCreatedBy($this->getUser());
        $codeGenerateur->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($codeGenerateur);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $codeGenerateurRepository->add($codeGenerateur, true);
        }

        return $this->responseData($codeGenerateur, 'group1', ['Content-Type' => 'application/json']);
    }



    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) codeGenerateur.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) codeGenerateur',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: CodeGenerateur::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'codeGenerateur')]
    //
    public function delete(Request $request, CodeGenerateur $codeGenerateur, CodeGenerateurRepository $villeRepository): Response
    {
        try {

            if ($codeGenerateur != null) {

                $villeRepository->remove($codeGenerateur, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($codeGenerateur);
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
     * Permet de supprimer plusieurs codeGenerateur.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: CodeGenerateur::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'codeGenerateur')]
    
    public function deleteAll(Request $request, CodeGenerateurRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $codeGenerateur = $villeRepository->find($value['id']);

                if ($codeGenerateur != null) {
                    $villeRepository->remove($codeGenerateur);
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
