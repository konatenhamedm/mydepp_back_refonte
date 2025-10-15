<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\StatusProDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\StatusPro;
use App\Repository\StatusProRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/statusPro')]
class ApiStatusProController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des statusPros.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: StatusPro::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'statusPro')]
    // 
    public function index(StatusProRepository $statusProRepository): Response
    {
        try {

            $statusPros = $statusProRepository->findAll();

          

            $response =  $this->responseData($statusPros, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) statusPro en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) statusPro en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: StatusPro::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'statusPro')]
    //
    public function getOne(?StatusPro $statusPro)
    {
        try {
            if ($statusPro) {
                $response = $this->response($statusPro);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($statusPro);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) statusPro.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'statusPro')]
    
    public function create(Request $request, StatusProRepository $statusProRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $statusPro = new StatusPro();
        $statusPro->setLibelle($data['libelle']);
        $statusPro->setCreatedBy($this->getUser());
        $statusPro->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($statusPro);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $statusProRepository->add($statusPro, true);
        }

        return $this->responseData($statusPro, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de statusPro",
        description: "Permet de créer un statusPro.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'statusPro')]
    
    public function update(Request $request, StatusPro $statusPro, StatusProRepository $statusProRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($statusPro != null) {

                $statusPro->setLibelle($data->libelle);
                $statusPro->setUpdatedBy($this->getUser());
                $statusPro->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($statusPro);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $statusProRepository->add($statusPro, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($statusPro, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) statusPro.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) statusPro',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: StatusPro::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'statusPro')]
    //
    public function delete(Request $request, StatusPro $statusPro, StatusProRepository $villeRepository): Response
    {
        try {

            if ($statusPro != null) {

                $villeRepository->remove($statusPro, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($statusPro);
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
     * Permet de supprimer plusieurs statusPro.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: StatusPro::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'statusPro')]
    
    public function deleteAll(Request $request, StatusProRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $statusPro = $villeRepository->find($value['id']);

                if ($statusPro != null) {
                    $villeRepository->remove($statusPro);
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
