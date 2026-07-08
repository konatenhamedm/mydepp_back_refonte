<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\TypeOrganisation;
use App\Repository\TypeOrganisationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;

#[Route('/api/typeOrganisation')]
class ApiTypeOrganisationController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des typeOrganisation.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des typeOrganisation',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeOrganisation::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeOrganisation')]
    public function index(TypeOrganisationRepository $typeOrganisationRepository): Response
    {
        try {
            $typeOrganisations = $typeOrganisationRepository->findAll();
            return $this->responseData($typeOrganisations, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) typeOrganisation en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) typeOrganisation en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeOrganisation::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeOrganisation')]
    public function getOne(?TypeOrganisation $typeOrganisation)
    {
        try {
            if ($typeOrganisation) {
                $response = $this->response($typeOrganisation);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($typeOrganisation);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer un(e) typeOrganisation.
     */
    #[OA\Post(
        summary: "Création d'un type d'organisation",
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
    #[OA\Tag(name: 'typeOrganisation')]
    public function create(Request $request, TypeOrganisationRepository $typeOrganisationRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $typeOrganisation = new TypeOrganisation();
        $typeOrganisation->setLibelle($data['libelle']);
        $typeOrganisation->setCode($data['code'] ?? null);
        $typeOrganisation->setCreatedBy($this->getUser());
        $typeOrganisation->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($typeOrganisation);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $typeOrganisationRepository->add($typeOrganisation, true);
        }

        return $this->responseData($typeOrganisation, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Mise à jour d'un type d'organisation",
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
    #[OA\Tag(name: 'typeOrganisation')]
    public function update(Request $request, TypeOrganisation $typeOrganisation, TypeOrganisationRepository $typeOrganisationRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($typeOrganisation != null) {
                $typeOrganisation->setLibelle($data->libelle);
                $typeOrganisation->setCode($data->code ?? null);
                $typeOrganisation->setUpdatedBy($this->getUser());
                $typeOrganisation->setUpdatedAt();

                $errorResponse = $this->errorResponse($typeOrganisation);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $typeOrganisationRepository->add($typeOrganisation, true);
                }

                $response = $this->responseData($typeOrganisation, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) typeOrganisation.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) typeOrganisation',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeOrganisation::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeOrganisation')]
    public function delete(Request $request, TypeOrganisation $typeOrganisation, TypeOrganisationRepository $typeOrganisationRepository): Response
    {
        try {
            if ($typeOrganisation != null) {
                $typeOrganisationRepository->remove($typeOrganisation, true);

                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($typeOrganisation);
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
