<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\SituationProfessionnelleDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\SituationProfessionnelle;
use App\Repository\SituationProfessionnelleRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/situationProfessionnelle')]
class ApiSituationProfessionnelleController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des situationProfessionnelles.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SituationProfessionnelle::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'situationProfessionnelle')]
    // 
    public function index(SituationProfessionnelleRepository $situationProfessionnelleRepository): Response
    {
        try {

            $situationProfessionnelles = $situationProfessionnelleRepository->findAll();

          

            $response =  $this->responseData($situationProfessionnelles, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) situationProfessionnelle en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) situationProfessionnelle en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SituationProfessionnelle::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'situationProfessionnelle')]
    //
    public function getOne(?SituationProfessionnelle $situationProfessionnelle)
    {
        try {
            if ($situationProfessionnelle) {
                $response = $this->response($situationProfessionnelle);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($situationProfessionnelle);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) situationProfessionnelle.
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
    #[OA\Tag(name: 'situationProfessionnelle')]
    
    public function create(Request $request, SituationProfessionnelleRepository $situationProfessionnelleRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $situationProfessionnelle = new SituationProfessionnelle();
        $situationProfessionnelle->setLibelle($data['libelle']);
        $situationProfessionnelle->setCreatedBy($this->getUser());
        $situationProfessionnelle->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($situationProfessionnelle);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $situationProfessionnelleRepository->add($situationProfessionnelle, true);
        }

        return $this->responseData($situationProfessionnelle, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de situationProfessionnelle",
        description: "Permet de créer un situationProfessionnelle.",
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
    #[OA\Tag(name: 'situationProfessionnelle')]
    
    public function update(Request $request, SituationProfessionnelle $situationProfessionnelle, SituationProfessionnelleRepository $situationProfessionnelleRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($situationProfessionnelle != null) {

                $situationProfessionnelle->setLibelle($data->libelle);
                $situationProfessionnelle->setUpdatedBy($this->getUser());
                $situationProfessionnelle->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($situationProfessionnelle);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $situationProfessionnelleRepository->add($situationProfessionnelle, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($situationProfessionnelle, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) situationProfessionnelle.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) situationProfessionnelle',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SituationProfessionnelle::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'situationProfessionnelle')]
    //
    public function delete(Request $request, SituationProfessionnelle $situationProfessionnelle, SituationProfessionnelleRepository $villeRepository): Response
    {
        try {

            if ($situationProfessionnelle != null) {

                $villeRepository->remove($situationProfessionnelle, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($situationProfessionnelle);
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
     * Permet de supprimer plusieurs situationProfessionnelle.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: SituationProfessionnelle::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'situationProfessionnelle')]
    
    public function deleteAll(Request $request, SituationProfessionnelleRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $situationProfessionnelle = $villeRepository->find($value['id']);

                if ($situationProfessionnelle != null) {
                    $villeRepository->remove($situationProfessionnelle);
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
