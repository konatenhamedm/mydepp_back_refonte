<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\RegionDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Region;
use App\Repository\DirectionRepository;
use App\Repository\RegionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/region')]
class ApiRegionController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des regions.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Region::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'region')]
    // 
    public function index(RegionRepository $regionRepository): Response
    {
        try {

            $regions = $regionRepository->findAll();

            $response =  $this->responseData($regions, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) region en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) region en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Region::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'region')]
    //
    public function getOne(?Region $region)
    {
        try {
            if ($region) {
                $response = $this->response($region);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($region);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) region.
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
                    new OA\Property(property: "direction", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'region')]
    
    public function create(Request $request, RegionRepository $regionRepository,DirectionRepository $directionRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $region = new Region();
        $region->setLibelle($data['libelle']);
        $region->setCode($data['code']);
        $region->setDirection($directionRepository->find($data['direction']));
        $region->setCreatedBy($this->getUser());
        $region->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($region);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $regionRepository->add($region, true);
        }

        return $this->responseData($region, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de region",
        description: "Permet de créer un region.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "direction", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'region')]
    
    public function update(Request $request, Region $region, RegionRepository $regionRepository,DirectionRepository $directionRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($region != null) {

                $region->setLibelle($data->libelle);
                $region->setCode($data->code);
                $region->setDirection($directionRepository->find($data->direction));
                $region->setUpdatedBy($this->getUser());
                $region->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($region);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $regionRepository->add($region, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($region, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) region.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) region',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Region::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'region')]
    //
    public function delete(Request $request, Region $region, RegionRepository $villeRepository): Response
    {
        try {

            if ($region != null) {

                $villeRepository->remove($region, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($region);
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
     * Permet de supprimer plusieurs region.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Region::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'region')]
    
    public function deleteAll(Request $request, RegionRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $region = $villeRepository->find($value['id']);

                if ($region != null) {
                    $villeRepository->remove($region);
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
