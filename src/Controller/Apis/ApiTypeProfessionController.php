<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\TypeProfessionDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\TypeProfession;
use App\Repository\TypeProfessionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/typeProfession')]
class ApiTypeProfessionController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des typeProfessions.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeProfession::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeProfession')]
    public function index(TypeProfessionRepository $typeProfessionRepository): Response
    {
        try {
            
            $typeProfessions = $typeProfessionRepository->findAll();
            
            $response =  $this->responseData($typeProfessions, 'group2', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) typeProfession en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) typeProfession en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeProfession::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'typeProfession')]
    //
    public function getOne(?TypeProfession $typeProfession)
    {
        try {
            if ($typeProfession) {
                $response = $this->response($typeProfession);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($typeProfession);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) typeProfession.
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
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'typeProfession')]
    
    public function create(Request $request, TypeProfessionRepository $typeProfessionRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $typeProfession = new TypeProfession();
        $typeProfession->setLibelle($data['libelle']);
        $typeProfession->setCode($data['code']);
        $typeProfession->setCreatedAtValue(new \DateTime());
        $typeProfession->setUpdatedAt(new \DateTime());
        $typeProfession->setCreatedBy($this->getUser());
        $typeProfession->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($typeProfession);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $typeProfessionRepository->add($typeProfession, true);
        }

        return $this->responseData($typeProfession, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de typeProfession",
        description: "Permet de créer un typeProfession.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "code", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'typeProfession')]
    
    public function update(Request $request, TypeProfession $typeProfession, TypeProfessionRepository $typeProfessionRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($typeProfession != null) {

                $typeProfession->setLibelle($data->libelle);
                $typeProfession->setCode($data->code);
                $typeProfession->setUpdatedBy($this->getUser());
                $typeProfession->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($typeProfession);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $typeProfessionRepository->add($typeProfession, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($typeProfession, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) typeProfession.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) typeProfession',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeProfession::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeProfession')]
    //
    public function delete(Request $request, TypeProfession $typeProfession, TypeProfessionRepository $villeRepository): Response
    {
        try {

            if ($typeProfession != null) {

                $villeRepository->remove($typeProfession, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($typeProfession);
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
     * Permet de supprimer plusieurs typeProfession.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeProfession::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeProfession')]
    
    public function deleteAll(Request $request, TypeProfessionRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $typeProfession = $villeRepository->find($value['id']);

                if ($typeProfession != null) {
                    $villeRepository->remove($typeProfession);
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
