<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\ArticleDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/article')]
class ApiArticleController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des articles.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Article::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'article')]
    // 
    public function index(ArticleRepository $articleRepository): Response
    {
        try {

            $articles = $articleRepository->findAll();

            $context = [AbstractNormalizer::GROUPS => 'group1'];
            $json = $this->serializer->serialize($articles, 'json', $context);

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
     * Affiche un(e) article en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) article en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Article::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'article')]
    //
    public function getOne(?Article $article)
    {
        try {
            if ($article) {
                $response = $this->response($article);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($article);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) article.
     */
    #[OA\Post(
        summary: "Permet de créer un(e) article.",
        description: "Permet de créer un(e) article.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "titre", type: "string"),
                        new OA\Property(property: "text", type: "string"),
                        new OA\Property(property: "image", type: "string", format: "binary"),
                        
                    ],
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'article')]
    
    public function create(Request $request, ArticleRepository $articleRepository): Response
    {

        $data = json_decode($request->getContent(), true);

        $names = 'document_' . '01';
        $filePrefix  = str_slug($names);
        $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
        $uploadedFile = $request->files->get('image');


        $article = new Article();
        $article->setTitre($request->get('titre'));
        $article->setText($request->get('text'));
        $article->setStatus(0);
        $article->setCreatedBy($this->getUser());
        $article->setUpdatedBy($this->getUser());
        $article->setUser($this->userRepository->find($request->get('userUpdate')));
        if ($uploadedFile) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
            if ($fichier) {
                $article->setImage($fichier);
            }
        }
        $errorResponse = $this->errorResponse($article);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $articleRepository->add($article, true);
        }

        return $this->responseData($article, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Création d'un article",
        description: "Permet de créer un article.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "titre", type: "string"),
                        new OA\Property(property: "text", type: "string"),
                        new OA\Property(property: "image", type: "string", format: "binary"),
                        
                    ],
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'article')]
    
    public function update(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $names = 'document_' . '01';
            $filePrefix  = str_slug($names);
            $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
            $uploadedFile = $request->files->get('image');

            if ($article != null) {

                $article->setTitre($request->get('titre'));
                $article->setText($request->get('text'));
                $article->setUpdatedBy($this->getUser());
                $article->setUpdatedAt(new \DateTime());
                if ($uploadedFile) {
                    $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
                    if ($fichier) {
                        $article->setImage($fichier);
                    }
                }

                $errorResponse = $this->errorResponse($article);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $articleRepository->add($article, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($article, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) article.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) article',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Article::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'article')]
    //
    public function delete(Request $request, Article $article, ArticleRepository $villeRepository): Response
    {
        try {

            if ($article != null) {

                $villeRepository->remove($article, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($article);
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
     * Permet de supprimer plusieurs article.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Article::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'article')]
    
    public function deleteAll(Request $request, ArticleRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $article = $villeRepository->find($value['id']);

                if ($article != null) {
                    $villeRepository->remove($article);
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
