<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\DirectionDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Direction;
use App\Repository\DirectionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/direction')]
class ApiDirectionController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des directions.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Direction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'direction')]
    // 
    public function index(DirectionRepository $directionRepository): Response
    {
        try {

            $directions = $directionRepository->findAll();

          

            $response =  $this->responseData($directions, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) direction en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) direction en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Direction::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'direction')]
    //
    public function getOne(?Direction $direction)
    {
        try {
            if ($direction) {
                $response = $this->response($direction);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($direction);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) direction.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "codeGeneration", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'direction')]
    
    public function create(Request $request, DirectionRepository $directionRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $direction = new Direction();
        $direction->setLibelle($data['libelle']);
        $direction->setCreatedBy($this->getUser());
        $direction->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($direction);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $directionRepository->add($direction, true);
        }

        return $this->responseData($direction, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de direction",
        description: "Permet de créer un direction.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "codeGeneration", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'direction')]
    
    public function update(Request $request, Direction $direction, DirectionRepository $directionRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($direction != null) {

                $direction->setLibelle($data->libelle);
                $direction->setUpdatedBy($this->getUser());
                $direction->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($direction);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $directionRepository->add($direction, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($direction, 'group1', ['Content-Type' => 'application/json']);
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

    //const TAB_ID = 'parametre-tabs';

    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) direction.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) direction',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Direction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'direction')]
    //
    public function delete(Request $request, Direction $direction, DirectionRepository $villeRepository): Response
    {
        try {

            if ($direction != null) {

                $villeRepository->remove($direction, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($direction);
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
     * Permet de supprimer plusieurs direction.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Direction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'direction')]
    
    public function deleteAll(Request $request, DirectionRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $direction = $villeRepository->find($value['id']);

                if ($direction != null) {
                    $villeRepository->remove($direction);
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
