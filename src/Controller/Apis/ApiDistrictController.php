<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\DistrictDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\District;
use App\Repository\DistrictRepository;
use App\Repository\RegionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/district')]
class ApiDistrictController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des districts.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: District::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'district')]
    // 
    public function index(DistrictRepository $districtRepository): Response
    {
        try {

            $districts = $districtRepository->findAll();

          

            $response =  $this->responseData($districts, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/{region}', methods: ['GET'])]
    /**
     * Retourne la liste des districts.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: District::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'district')]
    // 
    public function indexByRegion(DistrictRepository $districtRepository, $region): Response
    {
        try {

            $districts = $districtRepository->findBy(['region' => $region]);

          

            $response =  $this->responseData($districts, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) district en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) district en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: District::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'district')]
    //
    public function getOne(?District $district)
    {
        try {
            if ($district) {
                $response = $this->response($district);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($district);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) district.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "region", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'district')]
    
    public function create(Request $request,RegionRepository $regionRepository, DistrictRepository $districtRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $district = new District();
        $district->setLibelle($data['libelle']);
        $district->setRegion($regionRepository->find($data['region']));
        $district->setCreatedBy($this->getUser());
        $district->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($district);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $districtRepository->add($district, true);
        }

        return $this->responseData($district, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de district",
        description: "Permet de créer un district.",
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
    #[OA\Tag(name: 'district')]
    
    public function update(Request $request, District $district,RegionRepository $regionRepository, DistrictRepository $districtRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($district != null) {

                $district->setLibelle($data->libelle);
                $district->setRegion($regionRepository->find($data->region));
                $district->setUpdatedBy($this->getUser());
                $district->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($district);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $districtRepository->add($district, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($district, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) district.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) district',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: District::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'district')]
    //
    public function delete(Request $request, District $district, DistrictRepository $villeRepository): Response
    {
        try {

            if ($district != null) {

                $villeRepository->remove($district, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($district);
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
     * Permet de supprimer plusieurs district.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: District::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'district')]
    
    public function deleteAll(Request $request, DistrictRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $district = $villeRepository->find($value['id']);

                if ($district != null) {
                    $villeRepository->remove($district);
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
