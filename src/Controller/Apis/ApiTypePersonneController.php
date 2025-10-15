<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\TypePersonneDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\TypePersonne;
use App\Repository\TypePersonneRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/typePersonne')]
class ApiTypePersonneController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des typePersonnes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypePersonne::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typePersonne')]
    // 
    public function index(TypePersonneRepository $typePersonneRepository): Response
    {
        try {

            $typePersonnes = $typePersonneRepository->findAll();

            $context = [AbstractNormalizer::GROUPS => 'group1'];
            $json = $this->serializer->serialize($typePersonnes, 'json', $context);

            return new JsonResponse(['code' => 200, 'data' => json_decode($json)]);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) typePersonne en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) typePersonne en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypePersonne::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'libelle',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'typePersonne')]
    //
    public function getOne(?TypePersonne $typePersonne)
    {
        try {
            if ($typePersonne) {
                $response = $this->response($typePersonne);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($typePersonne);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) typePersonne.
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
    #[OA\Tag(name: 'typePersonne')]
    
    public function create(Request $request, TypePersonneRepository $typePersonneRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $typePersonne = new TypePersonne();
        $typePersonne->setLibelle($data['libelle']);
        $typePersonne->setCode($data['code']);
        $typePersonne->setCreatedBy($this->getUser());
        $typePersonne->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($typePersonne);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $typePersonneRepository->add($typePersonne, true);
        }

        return $this->responseData($typePersonne, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de typePersonne",
        description: "Permet de créer un typePersonne.",
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
    #[OA\Tag(name: 'typePersonne')]
    
    public function update(Request $request, TypePersonne $typePersonne, TypePersonneRepository $typePersonneRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($typePersonne != null) {

                $typePersonne->setLibelle($data->libelle);
                $typePersonne->setCode($data->code);
                $typePersonne->setUpdatedBy($this->getUser());
                $typePersonne->setUpdatedAt(new \DateTime());

                $errorResponse = $this->errorResponse($typePersonne);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $typePersonneRepository->add($typePersonne, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($typePersonne, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) typePersonne.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) typePersonne',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypePersonne::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typePersonne')]
    //
    public function delete(Request $request, TypePersonne $typePersonne, TypePersonneRepository $villeRepository): Response
    {
        try {

            if ($typePersonne != null) {

                $villeRepository->remove($typePersonne, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($typePersonne);
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
     * Permet de supprimer plusieurs typePersonne.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypePersonne::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typePersonne')]
    
    public function deleteAll(Request $request, TypePersonneRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $typePersonne = $villeRepository->find($value['id']);

                if ($typePersonne != null) {
                    $villeRepository->remove($typePersonne);
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
