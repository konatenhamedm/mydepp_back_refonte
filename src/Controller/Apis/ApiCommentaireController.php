<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\CommentaireDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Commentaire;
use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/commentaire')]
class ApiCommentaireController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des commentaires.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commentaire::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'commentaire')]
    // 
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        try {

            $commentaires = $commentaireRepository->findAll();

            $context = [AbstractNormalizer::GROUPS => 'group1'];
            $json = $this->serializer->serialize($commentaires, 'json', $context);

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
     * Affiche un(e) commentaire en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) commentaire en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commentaire::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'commentaire')]
    //
    public function getOne(?Commentaire $commentaire)
    {
        try {
            if ($commentaire) {
                $response = $this->response($commentaire);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($commentaire);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) commentaire.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "article", type: "string"),
                    new OA\Property(property: "user", type: "string"),
                    
                    new OA\Property(property: "commentaire", type: "text"),


                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'commentaire')]
    
    public function create(Request $request, CommentaireRepository $commentaireRepository,ArticleRepository $articleRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $commentaire = new Commentaire();
        $commentaire->setArticle($articleRepository->find($data['article']));
        $commentaire->setUser($this->userRepository->find($data['user']));
        $commentaire->setCommentaire($data["commentaire"]);
        $commentaire->setCreatedBy($this->getUser());
        $commentaire->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($commentaire);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $commentaireRepository->add($commentaire, true);
        }

        return $this->responseData($commentaire, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de commentaire",
        description: "Permet de créer un commentaire.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "article", type: "string"),
                    new OA\Property(property: "user", type: "string"),
                    
                    new OA\Property(property: "commentaire", type: "text"),

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'commentaire')]
    
    public function update(Request $request, Commentaire $commentaire,ArticleRepository $articleRepository, CommentaireRepository $commentaireRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($commentaire != null) {

                $commentaire->setArticle($articleRepository->find($data['article']));
                 $commentaire->setUser($this->userRepository->find($data->user));
                 $commentaire->setCommentaire($data->commentaire);
                $commentaire->setUpdatedBy($this->getUser());
                $commentaire->setUpdatedAt(new \DateTime());

                $errorResponse = $this->errorResponse($commentaire);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $commentaireRepository->add($commentaire, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($commentaire, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) commentaire.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) commentaire',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commentaire::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'commentaire')]
    //
    public function delete(Request $request, Commentaire $commentaire, CommentaireRepository $villeRepository): Response
    {
        try {

            if ($commentaire != null) {

                $villeRepository->remove($commentaire, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($commentaire);
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
     * Permet de supprimer plusieurs commentaire.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commentaire::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'commentaire')]
    
    public function deleteAll(Request $request, CommentaireRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $commentaire = $villeRepository->find($value['id']);

                if ($commentaire != null) {
                    $villeRepository->remove($commentaire);
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
