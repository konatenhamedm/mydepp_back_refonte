<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\OrganismeEnregistrement;
use App\Repository\OrganismeEnregistrementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;

#[Route('/api/organismeEnregistrement')]
class ApiOrganismeEnregistrementController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des organismeEnregistrement.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des organismeEnregistrement',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: OrganismeEnregistrement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'organismeEnregistrement')]
    public function index(OrganismeEnregistrementRepository $organismeEnregistrementRepository): Response
    {
        try {
            $organismeEnregistrements = $organismeEnregistrementRepository->findAll();
            return $this->responseData($organismeEnregistrements, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) organismeEnregistrement en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) organismeEnregistrement en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: OrganismeEnregistrement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'organismeEnregistrement')]
    public function getOne(?OrganismeEnregistrement $organismeEnregistrement)
    {
        try {
            if ($organismeEnregistrement) {
                $response = $this->response($organismeEnregistrement);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($organismeEnregistrement);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer un(e) organismeEnregistrement.
     */
    #[OA\Post(
        summary: "Création d'un organisme d'enregistrement",
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
    #[OA\Tag(name: 'organismeEnregistrement')]
    public function create(Request $request, OrganismeEnregistrementRepository $organismeEnregistrementRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $organismeEnregistrement = new OrganismeEnregistrement();
        $organismeEnregistrement->setLibelle($data['libelle']);
        $organismeEnregistrement->setCode($data['code'] ?? null);
        $organismeEnregistrement->setCreatedBy($this->getUser());
        $organismeEnregistrement->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($organismeEnregistrement);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $organismeEnregistrementRepository->add($organismeEnregistrement, true);
        }

        return $this->responseData($organismeEnregistrement, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Mise à jour d'un organisme d'enregistrement",
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
    #[OA\Tag(name: 'organismeEnregistrement')]
    public function update(Request $request, OrganismeEnregistrement $organismeEnregistrement, OrganismeEnregistrementRepository $organismeEnregistrementRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($organismeEnregistrement != null) {
                $organismeEnregistrement->setLibelle($data->libelle);
                $organismeEnregistrement->setCode($data->code ?? null);
                $organismeEnregistrement->setUpdatedBy($this->getUser());
                $organismeEnregistrement->setUpdatedAt();

                $errorResponse = $this->errorResponse($organismeEnregistrement);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $organismeEnregistrementRepository->add($organismeEnregistrement, true);
                }

                $response = $this->responseData($organismeEnregistrement, 'group1', ['Content-Type' => 'application/json']);
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

    #[Route('/delete/{id}', methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) organismeEnregistrement.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) organismeEnregistrement',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: OrganismeEnregistrement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'organismeEnregistrement')]
    public function delete(Request $request, OrganismeEnregistrement $organismeEnregistrement, OrganismeEnregistrementRepository $organismeEnregistrementRepository): Response
    {
        try {
            if ($organismeEnregistrement != null) {
                $organismeEnregistrementRepository->remove($organismeEnregistrement, true);

                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($organismeEnregistrement);
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
