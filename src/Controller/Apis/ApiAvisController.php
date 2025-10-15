<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\AvisDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Avis;
use App\Entity\Forum;
use App\Repository\AvisRepository;
use App\Repository\ForumRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/avis')]
class ApiAvisController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des avis.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Avis::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'avis')]
    // 
    public function index(AvisRepository $avisRepository): Response
    {
        try {

            $avis = $avisRepository->findAll();



            $response =  $this->responseData($avis, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/avis/by/forum/{idForum}', methods: ['GET'])]
    /**
     * Retourne la liste des avis dun forum'.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Avis::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'avis')]
    // 
    public function avisForum(AvisRepository $avisRepository, Forum $forum): Response
    {
        try {

            $avis = $avisRepository->findBy(['forum' => $forum]);



            $response =  $this->responseData($avis, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) avis en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) avis en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Avis::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'avis')]
    //
    public function getOne(?Avis $avis)
    {
        try {
            if ($avis) {
                $response = $this->response($avis);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($avis);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) avis.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "contenu", type: "string"),
                    new OA\Property(property: "forum", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'avis')]
    
    public function create(Request $request, AvisRepository $avisRepository,ForumRepository $forumRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $avis = new Avis();
        $avis->setContenu($data['contenu']);
        $avis->setUpdatedAt(new \DateTime());
        $avis->setCreatedAtValue(new \DateTime());
        $avis->setForum($forumRepository->find($data['forum']));
        $avis->setCreatedBy($this->getUser());
        $avis->setUser($this->userRepository->find($data['userUpdate']));
        $avis->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($avis);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $avisRepository->add($avis, true);
        }

        return $this->responseData($avis, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de avis",
        description: "Permet de créer un avis.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "contenu", type: "string"),
                    new OA\Property(property: "forum", type: "string"),
                    
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'avis')]
    
    public function update(Request $request, Avis $avis, AvisRepository $avisRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($avis != null) {

                $avis->setContenu($data->contenu);
                $avis->setForum($data->forum);
                $avis->setUpdatedBy($this->getUser());
                $avis->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($avis);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $avisRepository->add($avis, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($avis, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) avis.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) avis',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Avis::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'avis')]
    //
    public function delete(Request $request, Avis $avis, AvisRepository $villeRepository): Response
    {
        try {

            if ($avis != null) {

                $villeRepository->remove($avis, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($avis);
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
     * Permet de supprimer plusieurs avis.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Avis::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'avis')]
    
    public function deleteAll(Request $request, AvisRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $avis = $villeRepository->find($value['id']);

                if ($avis != null) {
                    $villeRepository->remove($avis);
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
