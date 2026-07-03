<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Actualite;
use App\Repository\ActualiteRepository;
use App\Repository\CommuneRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/actualite')]
class ApiActualiteController extends ApiInterface
{
    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des actualités.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the list of actualites',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Actualite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'actualite')]
    public function index(ActualiteRepository $actualiteRepository): Response
    {
        try {
            $actualites = $actualiteRepository->findBy([], ['createdAt' => 'DESC']);

            $response = $this->responseData($actualites, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche une actualité en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche une actualité en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Actualite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'actualite')]
    public function getOne(?Actualite $actualite)
    {
        try {
            if ($actualite) {
                $response = $this->response($actualite);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($actualite);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer une actualité.
     */
    #[OA\Post(
        summary: "Création d'une actualité",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "hashtag", type: "string"),
                        new OA\Property(property: "titre", type: "string"),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(property: "commune", type: "string"),
                        new OA\Property(property: "lien", type: "string"),
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
    #[OA\Tag(name: 'actualite')]
    public function create(Request $request, ActualiteRepository $actualiteRepository, CommuneRepository $communeRepository): Response
    {
        $filePrefix = 'actualite_' . uniqid();
        $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
        $uploadedFile = $request->files->get('image');

        $actualite = new Actualite();
        $actualite->setHashtag($request->get('hashtag'));
        $actualite->setTitre($request->get('titre'));
        $actualite->setDescription($request->get('description'));
        if ($request->get('commune')) {
            $actualite->setCommune($communeRepository->find($request->get('commune')));
        }
        $actualite->setLien($request->get('lien'));
        $actualite->setCreatedAtValue();
        $actualite->setUpdatedAt();
        $actualite->setCreatedBy($this->getUser());
        $actualite->setUpdatedBy($this->getUser());

        if ($uploadedFile) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
            if ($fichier) {
                $actualite->setImage($fichier);
            }
        }

        $actualiteRepository->add($actualite, true);

        return $this->responseData($actualite, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    /**
     * Permet de mettre à jour une actualité.
     */
    #[OA\Post(
        summary: "Mise à jour d'une actualité",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "hashtag", type: "string"),
                        new OA\Property(property: "titre", type: "string"),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(property: "commune", type: "string"),
                        new OA\Property(property: "lien", type: "string"),
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
    #[OA\Tag(name: 'actualite')]
    public function update(Request $request, Actualite $actualite, ActualiteRepository $actualiteRepository, CommuneRepository $communeRepository): Response
    {
        try {
            if ($actualite != null) {
                $filePrefix = 'actualite_' . uniqid();
                $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
                $uploadedFile = $request->files->get('image');

                $actualite->setHashtag($request->get('hashtag'));
                $actualite->setTitre($request->get('titre'));
                $actualite->setDescription($request->get('description'));
                $actualite->setCommune($request->get('commune') ? $communeRepository->find($request->get('commune')) : null);
                $actualite->setLien($request->get('lien'));
                $actualite->setUpdatedAt();
                $actualite->setUpdatedBy($this->getUser());

                if ($uploadedFile) {
                    $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
                    if ($fichier) {
                        $actualite->setImage($fichier);
                    }
                }

                $actualiteRepository->add($actualite, true);

                $response = $this->responseData($actualite, 'group1', ['Content-Type' => 'application/json']);
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

    #[Route('/delete/{id}', methods: ['DELETE'])]
    /**
     * Permet de supprimer une actualité.
     */
    #[OA\Response(
        response: 200,
        description: 'Permet de supprimer une actualité',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Actualite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'actualite')]
    public function delete(Request $request, Actualite $actualite, ActualiteRepository $actualiteRepository): Response
    {
        try {
            if ($actualite != null) {
                $actualiteRepository->remove($actualite, true);

                $this->setMessage("Operation effectuée avec succès");
                $response = $this->response($actualite);
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
}
