<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\ValidationWorkflowDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\ValidationWorkflow;
use App\Repository\ValidationWorkflowRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/ValidationWorkflow')]
class ApiValidationWorkflowController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des validationWorkflow.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ValidationWorkflow::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'ValidationWorkflow')]
    // 
    public function index(ValidationWorkflowRepository $civiliteRepository): Response
    {
        try {

            $validationWorkflow = $civiliteRepository->findAll();

          

            $response =  $this->responseData($validationWorkflow, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/{idPersoone}', methods: ['GET'])]
    /**
     * Retourne la liste des validationWorkflow.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ValidationWorkflow::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'ValidationWorkflow')]
    // 
    public function suiviByUser(ValidationWorkflowRepository $validationWorkflowRepository, string $idPersoone): Response
    {
        try {

            $validationWorkflow = $validationWorkflowRepository->findBy(['personne' => $idPersoone]);

          

            $response =  $this->responseData($validationWorkflow, 'group_pro_validate_', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) ValidationWorkflow en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) ValidationWorkflow en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ValidationWorkflow::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'ValidationWorkflow')]
    //
    public function getOne(?ValidationWorkflow $ValidationWorkflow)
    {
        try {
            if ($ValidationWorkflow) {
                $response = $this->response($ValidationWorkflow);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($ValidationWorkflow);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) ValidationWorkflow.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "personne", type: "string"),
                    new OA\Property(property: "etape", type: "string"),
                    new OA\Property(property: "raison", type: "string"),
                    
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'ValidationWorkflow')]
    
    public function create(Request $request, ValidationWorkflowRepository $civiliteRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $ValidationWorkflow = new ValidationWorkflow();
        $ValidationWorkflow->setEtape($data['etape']);
        $ValidationWorkflow->setPersonne($data['personne']);
        $ValidationWorkflow->setRaison($data['raison']);
        $ValidationWorkflow->setCreatedAtValue(new DateTime());
        $ValidationWorkflow->setUpdatedAt(new DateTime());
        $ValidationWorkflow->setCreatedBy($this->getUser());
        $ValidationWorkflow->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($ValidationWorkflow);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $civiliteRepository->add($ValidationWorkflow, true);
        }

        return $this->responseData($ValidationWorkflow, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de ValidationWorkflow",
        description: "Permet de créer un ValidationWorkflow.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "personne", type: "string"),
                    new OA\Property(property: "etape", type: "string"),
                    new OA\Property(property: "raison", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'ValidationWorkflow')]
    
    public function update(Request $request, ValidationWorkflow $ValidationWorkflow, ValidationWorkflowRepository $civiliteRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($ValidationWorkflow != null) {

                $ValidationWorkflow->setPersonne($data->personne);
                $ValidationWorkflow->setEtape($data->etape);
                $ValidationWorkflow->setRaison($data->raison);
                $ValidationWorkflow->setUpdatedBy($this->getUser());
                $ValidationWorkflow->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($ValidationWorkflow);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $civiliteRepository->add($ValidationWorkflow, true);
                }
                // On retourne la confirmation
                $response = $this->responseData($ValidationWorkflow, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) ValidationWorkflow.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) ValidationWorkflow',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ValidationWorkflow::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'ValidationWorkflow')]
    //
    public function delete(Request $request, ValidationWorkflow $ValidationWorkflow, ValidationWorkflowRepository $villeRepository): Response
    {
        try {

            if ($ValidationWorkflow != null) {

                $villeRepository->remove($ValidationWorkflow, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($ValidationWorkflow);
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
     * Permet de supprimer plusieurs ValidationWorkflow.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ValidationWorkflow::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'ValidationWorkflow')]
    
    public function deleteAll(Request $request, ValidationWorkflowRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $ValidationWorkflow = $villeRepository->find($value['id']);

                if ($ValidationWorkflow != null) {
                    $villeRepository->remove($ValidationWorkflow);
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
