<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\VilleDTO;
use App\Entity\Professionnel;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Ville;
use App\Form\UploadType;
use App\Repository\DistrictRepository;
use App\Repository\EntiteRepository;
use App\Repository\GenreRepository;
use App\Repository\PaysRepository;
use App\Repository\ProfessionnelRepository;
use App\Repository\VilleRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/ville')]
class ApiVilleController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des villes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Ville::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'ville')]
    // 
    public function index(VilleRepository $villeRepository): Response
    {
        try {

            $villes = $villeRepository->findAll();



            $response =  $this->responseData($villes, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/{district}', methods: ['GET'])]
    /**
     * Retourne la liste des villes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Ville::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'ville')]
    // 
    public function indexByDistrict(VilleRepository $villeRepository, $district): Response
    {
        try {

            $villes = $villeRepository->findBy(['district' => $district]);



            $response =  $this->responseData($villes, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) ville en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) ville en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Ville::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'ville')]
    //
    public function getOne(?Ville $ville)
    {
        try {
            if ($ville) {
                $response = $this->response($ville);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($ville);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) ville.
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
                    new OA\Property(property: "district", type: "string"),


                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'ville')]

    public function create(Request $request, VilleRepository $villeRepository, DistrictRepository $districtRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $ville = new Ville();
        $ville->setLibelle($data['libelle']);
        $ville->setCode($data['code']);
        $ville->setDistrict($districtRepository->find($data['district']));
        $ville->setCreatedBy($this->getUser());
        $ville->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($ville);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $villeRepository->add($ville, true);
        }

        return $this->responseData($ville, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de ville",
        description: "Permet de créer un ville.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "district", type: "string"),


                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'ville')]

    public function update(Request $request, Ville $ville, VilleRepository $villeRepository, DistrictRepository $districtRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($ville != null) {

                $ville->setLibelle($data->libelle);
                $ville->setCode($data->code);
                $ville->setDistrict($districtRepository->find($data->district));
                $ville->setUpdatedBy($this->getUser());
                $ville->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($ville);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $villeRepository->add($ville, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($ville, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) ville.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) ville',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Ville::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'ville')]
    //
    public function delete(Request $request, Ville $ville, VilleRepository $villeRepository): Response
    {
        try {

            if ($ville != null) {

                $villeRepository->remove($ville, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($ville);
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
     * Permet de supprimer plusieurs ville.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Ville::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'ville')]
    public function deleteAll(Request $request, VilleRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $ville = $villeRepository->find($value['id']);

                if ($ville != null) {
                    $villeRepository->remove($ville);
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

