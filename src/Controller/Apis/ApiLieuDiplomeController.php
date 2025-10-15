<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\LieuDiplomeDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\LieuDiplome;
use App\Repository\LieuDiplomeRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/lieuDiplome')]
class ApiLieuDiplomeController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des lieuDiplomes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: LieuDiplome::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'lieuDiplome')]
    // 
    public function index(LieuDiplomeRepository $lieuDiplomeRepository): Response
    {
        try {

            $lieuDiplomes = $lieuDiplomeRepository->findAll();

          

            $response =  $this->responseData($lieuDiplomes, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) lieuDiplome en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) lieuDiplome en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: LieuDiplome::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'lieuDiplome')]
    //
    public function getOne(?LieuDiplome $lieuDiplome)
    {
        try {
            if ($lieuDiplome) {
                $response = $this->response($lieuDiplome);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($lieuDiplome);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) lieuDiplome.
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
    #[OA\Tag(name: 'lieuDiplome')]
    
    public function create(Request $request, LieuDiplomeRepository $lieuDiplomeRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $lieuDiplome = new LieuDiplome();
        $lieuDiplome->setLibelle($data['libelle']);
        $lieuDiplome->setCreatedBy($this->getUser());
        $lieuDiplome->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($lieuDiplome);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $lieuDiplomeRepository->add($lieuDiplome, true);
        }

        return $this->responseData($lieuDiplome, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de lieuDiplome",
        description: "Permet de créer un lieuDiplome.",
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
    #[OA\Tag(name: 'lieuDiplome')]
    
    public function update(Request $request, LieuDiplome $lieuDiplome, LieuDiplomeRepository $lieuDiplomeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($lieuDiplome != null) {

                $lieuDiplome->setLibelle($data->libelle);
                $lieuDiplome->setUpdatedBy($this->getUser());
                $lieuDiplome->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($lieuDiplome);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $lieuDiplomeRepository->add($lieuDiplome, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($lieuDiplome, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) lieuDiplome.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) lieuDiplome',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: LieuDiplome::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'lieuDiplome')]
    //
    public function delete(Request $request, LieuDiplome $lieuDiplome, LieuDiplomeRepository $villeRepository): Response
    {
        try {

            if ($lieuDiplome != null) {

                $villeRepository->remove($lieuDiplome, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($lieuDiplome);
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
     * Permet de supprimer plusieurs lieuDiplome.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: LieuDiplome::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'lieuDiplome')]
    
    public function deleteAll(Request $request, LieuDiplomeRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $lieuDiplome = $villeRepository->find($value['id']);

                if ($lieuDiplome != null) {
                    $villeRepository->remove($lieuDiplome);
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
