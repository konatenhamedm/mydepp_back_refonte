<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/evenement')]
class ApiEvenementController extends ApiInterface
{
    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des événements.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the list of evenements',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Evenement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'evenement')]
    public function index(EvenementRepository $evenementRepository): Response
    {
        try {
            $evenements = $evenementRepository->findBy([], ['createdAt' => 'DESC']);

            $response = $this->responseData($evenements, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un événement en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un événement en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Evenement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'evenement')]
    public function getOne(?Evenement $evenement)
    {
        try {
            if ($evenement) {
                $response = $this->response($evenement);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($evenement);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer un événement.
     */
    #[OA\Post(
        summary: "Création d'un événement",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "titre", type: "string"),
                        new OA\Property(property: "type", type: "string"),
                        new OA\Property(property: "lien", type: "string"),
                        new OA\Property(property: "dateEvenement", type: "string"),
                        new OA\Property(property: "texte", type: "string"),
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
    #[OA\Tag(name: 'evenement')]
    public function create(Request $request, EvenementRepository $evenementRepository): Response
    {
        $filePrefix = 'evenement_' . uniqid();
        $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
        $uploadedFile = $request->files->get('image');

        $evenement = new Evenement();
        $evenement->setTitre($request->get('titre'));
        $evenement->setType($request->get('type'));
        $evenement->setLien($request->get('lien'));
        $evenement->setTexte($request->get('texte'));
        $dateEvenement = $request->get('dateEvenement');
        if ($dateEvenement) {
            $evenement->setDateEvenement(new \DateTime($dateEvenement));
        }
        $evenement->setCreatedAtValue();
        $evenement->setUpdatedAt();
        $evenement->setCreatedBy($this->getUser());
        $evenement->setUpdatedBy($this->getUser());

        if ($uploadedFile) {
            $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
            if ($fichier) {
                $evenement->setImage($fichier);
            }
        }

        $evenementRepository->add($evenement, true);

        return $this->responseData($evenement, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    /**
     * Permet de mettre à jour un événement.
     */
    #[OA\Post(
        summary: "Mise à jour d'un événement",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "titre", type: "string"),
                        new OA\Property(property: "type", type: "string"),
                        new OA\Property(property: "lien", type: "string"),
                        new OA\Property(property: "dateEvenement", type: "string"),
                        new OA\Property(property: "texte", type: "string"),
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
    #[OA\Tag(name: 'evenement')]
    public function update(Request $request, Evenement $evenement, EvenementRepository $evenementRepository): Response
    {
        try {
            if ($evenement != null) {
                $filePrefix = 'evenement_' . uniqid();
                $filePath = $this->getUploadDir(self::UPLOAD_PATH, true);
                $uploadedFile = $request->files->get('image');

                $evenement->setTitre($request->get('titre'));
                $evenement->setType($request->get('type'));
                $evenement->setLien($request->get('lien'));
                $evenement->setTexte($request->get('texte'));
                $dateEvenement = $request->get('dateEvenement');
                $evenement->setDateEvenement($dateEvenement ? new \DateTime($dateEvenement) : null);
                $evenement->setUpdatedAt();
                $evenement->setUpdatedBy($this->getUser());

                if ($uploadedFile) {
                    $fichier = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);
                    if ($fichier) {
                        $evenement->setImage($fichier);
                    }
                }

                $evenementRepository->add($evenement, true);

                $response = $this->responseData($evenement, 'group1', ['Content-Type' => 'application/json']);
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
     * Permet de supprimer un événement.
     */
    #[OA\Response(
        response: 200,
        description: 'Permet de supprimer un événement',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Evenement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'evenement')]
    public function delete(Request $request, Evenement $evenement, EvenementRepository $evenementRepository): Response
    {
        try {
            if ($evenement != null) {
                $evenementRepository->remove($evenement, true);

                $this->setMessage("Operation effectuée avec succès");
                $response = $this->response($evenement);
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
