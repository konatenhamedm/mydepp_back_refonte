<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\NiveauInterventionDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\NiveauIntervention;
use App\Repository\NiveauInterventionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/niveauIntervention')]
class ApiNiveauInterventionController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des niveauInterventions.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NiveauIntervention::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'niveauIntervention')]
    // 
    public function index(NiveauInterventionRepository $niveauInterventionRepository): Response
    {
        try {

            $niveauInterventions = $niveauInterventionRepository->findAll();



            $response =  $this->responseData($niveauInterventions, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) niveauIntervention en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) niveauIntervention en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NiveauIntervention::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'niveauIntervention')]
    //
    public function getOne(?NiveauIntervention $niveauIntervention)
    {
        try {
            if ($niveauIntervention) {
                $response = $this->response($niveauIntervention);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($niveauIntervention);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) niveauIntervention.
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
                    new OA\Property(property: "montant", type: "string"),
                    new OA\Property(property: "montantRenouvellement", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'niveauIntervention')]
    
    public function create(Request $request, NiveauInterventionRepository $niveauInterventionRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $niveauIntervention = new NiveauIntervention();
        $niveauIntervention->setLibelle($data['libelle']);
        $niveauIntervention->setMontant($data['montant']);
        $niveauIntervention->setMontantRenouvellement($data['montantRenouvellement']);
        $niveauIntervention->setCode($data['code']);
        $niveauIntervention->setCreatedBy($this->getUser());
        $niveauIntervention->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($niveauIntervention);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $niveauInterventionRepository->add($niveauIntervention, true);
        }

        return $this->responseData($niveauIntervention, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de niveauIntervention",
        description: "Permet de créer un niveauIntervention.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "montant", type: "string"),
                    new OA\Property(property: "montantRenouvellement", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'niveauIntervention')]
    
    public function update(Request $request, NiveauIntervention $niveauIntervention, NiveauInterventionRepository $niveauInterventionRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($niveauIntervention != null) {

                $niveauIntervention->setLibelle($data->libelle);
                $niveauIntervention->setCode($data->code);
                $niveauIntervention->setMontant($data->montant);
                $niveauIntervention->setMontantRenouvellement($data->montantRenouvellement);
                $niveauIntervention->setUpdatedBy($this->getUser());
                $niveauIntervention->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($niveauIntervention);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $niveauInterventionRepository->add($niveauIntervention, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($niveauIntervention, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) niveauIntervention.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) niveauIntervention',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NiveauIntervention::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'niveauIntervention')]
    //
    public function delete(Request $request, NiveauIntervention $niveauIntervention, NiveauInterventionRepository $villeRepository): Response
    {
        try {

            if ($niveauIntervention != null) {

                $villeRepository->remove($niveauIntervention, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($niveauIntervention);
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
     * Permet de supprimer plusieurs niveauIntervention.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NiveauIntervention::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'niveauIntervention')]
    
    public function deleteAll(Request $request, NiveauInterventionRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $niveauIntervention = $villeRepository->find($value['id']);

                if ($niveauIntervention != null) {
                    $villeRepository->remove($niveauIntervention);
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
