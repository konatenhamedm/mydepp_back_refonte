<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\SpecialiteDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Specialite;
use App\Repository\SpecialiteRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/specialite')]
class ApiSpecialiteController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des specialites.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Specialite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'specialite')]
    // 
    public function index(SpecialiteRepository $specialiteRepository): Response
    {
        try {

            $specialites = $specialiteRepository->findAll();

            $context = [AbstractNormalizer::GROUPS => 'group1'];
            $json = $this->serializer->serialize($specialites, 'json', $context);

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
     * Affiche un(e) specialite en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) specialite en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Specialite::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'specialite')]
    //
    public function getOne(?Specialite $specialite)
    {
        try {
            if ($specialite) {
                $response = $this->response($specialite);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($specialite);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }
    #[Route('/get/status/paiement/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) specialite en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche etat paiement de la specialite',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Specialite::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'specialite')]
    //
    public function getPaiementStatus(?Specialite $specialite)
    {
        try {
            if ($specialite) {
                $response = $this->response($specialite->isPaiement());
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response([]);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) specialite.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "paiement", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'specialite')]
    
    public function create(Request $request, SpecialiteRepository $specialiteRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $specialite = new Specialite();
        $specialite->setLibelle($data['libelle']);
        $specialite->setPaiement($data['paiement']);
        $specialite->setCreatedBy($this->getUser());
        $specialite->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($specialite);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $specialiteRepository->add($specialite, true);
        }

        return $this->responseData($specialite, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de specialite",
        description: "Permet de créer un specialite.",
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
    #[OA\Tag(name: 'specialite')]
    
    public function update(Request $request, Specialite $specialite, SpecialiteRepository $specialiteRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($specialite != null) {

                $specialite->setLibelle($data->libelle);
                $specialite->setPaiement($data->paiement);
                $specialite->setUpdatedBy($this->getUser());
                $specialite->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($specialite);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $specialiteRepository->add($specialite, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($specialite, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) specialite.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) specialite',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Specialite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'specialite')]
    //
    public function delete(Request $request, Specialite $specialite, SpecialiteRepository $villeRepository): Response
    {
        try {

            if ($specialite != null) {

                $villeRepository->remove($specialite, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($specialite);
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
     * Permet de supprimer plusieurs specialite.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Specialite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'specialite')]
    
    public function deleteAll(Request $request, SpecialiteRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $specialite = $villeRepository->find($value['id']);

                if ($specialite != null) {
                    $villeRepository->remove($specialite);
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
