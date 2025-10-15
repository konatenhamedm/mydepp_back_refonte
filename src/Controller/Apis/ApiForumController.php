<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\ForumDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Forum;
use App\Entity\User;
use App\Repository\ForumRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/forum')]
class ApiForumController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des forums.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Forum::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'forum')]
    // 
    public function index(ForumRepository $forumRepository): Response
    {
        try {

            $forums = $forumRepository->findAll();

          

            $response =  $this->responseData($forums, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/actif', methods: ['GET'])]
    /**
     * Retourne la liste des forums actif.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Forum::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'forum')]
    // 
    public function indexActif(ForumRepository $forumRepository): Response
    {
        try {

            $forums = $forumRepository->findBy(['status'=>'Actif']);
            $response =  $this->responseData($forums, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/forum/by/user/{userId}', methods: ['GET'])]
    /**
     * Retourne la liste des forums dun utilisateur.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Forum::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'forum')]
    // 
    public function forumByUser(ForumRepository $forumRepository, $userId): Response
    {
        try {

            $forums = $forumRepository->findBy(['user'=>$userId]);

          

            $response =  $this->responseData($forums, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) forum en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) forum en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Forum::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'forum')]
    //
    public function getOne(ForumRepository $forumRepository,$id)
    {
        try {
            $forum = $forumRepository->find($id);
            if ($forum) {
                $response =  $this->responseData($forum, 'group1', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($forum);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) forum.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "contenu", type: "string"),
                    new OA\Property(property: "user", type: "string"),
                    new OA\Property(property: "status", type: "string"),
                    new OA\Property(property: "titre", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'forum')]
    
    public function create(Request $request, ForumRepository $forumRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $forum = new Forum();
        $forum->setContenu($data['contenu']);
        $forum->setTitre($data['titre']);
        $forum->setStatus($data['status']);
        $forum->setCreatedAtValue(new \DateTime());
        $forum->setUpdatedAt(new \DateTime());
        $forum->setUser($this->userRepository->find($data['user']));
        $forum->setCreatedBy($this->userRepository->find($data['user']));
        $forum->setUpdatedBy($this->userRepository->find($data['user']));
        $errorResponse = $this->errorResponse($forum);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $forumRepository->add($forum, true);
        }

        return $this->responseData($forum, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de forum",
        description: "Permet de créer un forum.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "contenu", type: "string"),
                    new OA\Property(property: "status", type: "string"),
                    new OA\Property(property: "titre", type: "string"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'forum')]
    
    public function update(Request $request, Forum $forum, ForumRepository $forumRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($forum != null) {

                $forum->setContenu($data->contenu);
                $forum->setStatus($data->status);
                $forum->setTitre($data->titre);
              
                $forum->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($forum);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $forumRepository->add($forum, true);
                }

                // On retourne la confirmation
                $response = $this->responseData($forum, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) forum.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) forum',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Forum::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'forum')]
    //
    public function delete(Request $request, Forum $forum, ForumRepository $villeRepository): Response
    {
        try {

            if ($forum != null) {

                $villeRepository->remove($forum, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($forum);
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
     * Permet de supprimer plusieurs forum.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Forum::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'forum')]
    
    public function deleteAll(Request $request, ForumRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $forum = $villeRepository->find($value['id']);

                if ($forum != null) {
                    $villeRepository->remove($forum);
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
