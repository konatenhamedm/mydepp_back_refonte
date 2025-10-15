<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\CommuneDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Commune;
use App\Repository\CommuneRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/commune')]
class ApiCommuneController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des communes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commune::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'commune')]
    // 
    public function index(CommuneRepository $communeRepository): Response
    {
        try {

            $communes = $communeRepository->findAll();

          

            $response =  $this->responseData($communes, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/{ville}', methods: ['GET'])]
    /**
     * Retourne la liste des communes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commune::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'commune')]
    // 
    public function indexByVile(CommuneRepository $communeRepository,$ville): Response
    {
        try {

            $communes = $communeRepository->findByVille($ville);

          

            $response =  $this->responseData($communes, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) commune en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) commune en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commune::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'commune')]
    //
    public function getOne(?Commune $commune)
    {
        try {
            if ($commune) {
                $response = $this->response($commune);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($commune);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) commune.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "ville", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'commune')]
    
    public function create(Request $request, CommuneRepository $communeRepository, VilleRepository $villeRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $commune = new Commune();
        $commune->setLibelle($data['libelle']);
        $commune->setVille($villeRepository->find($data['ville']));
        $commune->setCreatedBy($this->getUser());
        $commune->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($commune);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $communeRepository->add($commune, true);
        }

        return $this->responseData($commune, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de commune",
        description: "Permet de créer un commune.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "ville", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'commune')]
    
    public function update(Request $request, Commune $commune, CommuneRepository $communeRepository, VilleRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($commune != null) {

                $commune->setLibelle($data->libelle);
                $commune->setVille($villeRepository->find($data->ville));
               
                $commune->setUpdatedBy($this->getUser());
                $commune->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($commune);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $communeRepository->add($commune, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($commune, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) commune.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) commune',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commune::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'commune')]
    //
    public function delete(Request $request, Commune $commune, CommuneRepository $villeRepository): Response
    {
        try {

            if ($commune != null) {

                $villeRepository->remove($commune, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($commune);
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
     * Permet de supprimer plusieurs commune.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commune::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'commune')]
    
    public function deleteAll(Request $request, CommuneRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $commune = $villeRepository->find($value['id']);

                if ($commune != null) {
                    $villeRepository->remove($commune);
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
