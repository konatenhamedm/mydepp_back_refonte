<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\TypeDemandeEtablissement;
use App\Repository\TypeDemandeEtablissementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;

#[Route('/api/typeDemandeEtablissement')]
class ApiTypeDemandeEtablissementController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des typeDemandeEtablissement.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des typeDemandeEtablissement',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeDemandeEtablissement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeDemandeEtablissement')]
    public function index(TypeDemandeEtablissementRepository $typeDemandeEtablissementRepository): Response
    {
        try {
            $typeDemandeEtablissements = $typeDemandeEtablissementRepository->findAll();
            return $this->responseData($typeDemandeEtablissements, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) typeDemandeEtablissement en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) typeDemandeEtablissement en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeDemandeEtablissement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeDemandeEtablissement')]
    public function getOne(?TypeDemandeEtablissement $typeDemandeEtablissement)
    {
        try {
            if ($typeDemandeEtablissement) {
                $response = $this->response($typeDemandeEtablissement);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($typeDemandeEtablissement);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer un(e) typeDemandeEtablissement.
     */
    #[OA\Post(
        summary: "Création d'un type de demande d'établissement",
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
    #[OA\Tag(name: 'typeDemandeEtablissement')]
    public function create(Request $request, TypeDemandeEtablissementRepository $typeDemandeEtablissementRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $typeDemandeEtablissement = new TypeDemandeEtablissement();
        $typeDemandeEtablissement->setLibelle($data['libelle']);
        $typeDemandeEtablissement->setCode($data['code'] ?? null);
        $typeDemandeEtablissement->setCreatedBy($this->getUser());
        $typeDemandeEtablissement->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($typeDemandeEtablissement);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $typeDemandeEtablissementRepository->add($typeDemandeEtablissement, true);
        }

        return $this->responseData($typeDemandeEtablissement, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Mise à jour d'un type de demande d'établissement",
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
    #[OA\Tag(name: 'typeDemandeEtablissement')]
    public function update(Request $request, TypeDemandeEtablissement $typeDemandeEtablissement, TypeDemandeEtablissementRepository $typeDemandeEtablissementRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($typeDemandeEtablissement != null) {
                $typeDemandeEtablissement->setLibelle($data->libelle);
                $typeDemandeEtablissement->setCode($data->code ?? null);
                $typeDemandeEtablissement->setUpdatedBy($this->getUser());
                $typeDemandeEtablissement->setUpdatedAt();

                $errorResponse = $this->errorResponse($typeDemandeEtablissement);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $typeDemandeEtablissementRepository->add($typeDemandeEtablissement, true);
                }

                $response = $this->responseData($typeDemandeEtablissement, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) typeDemandeEtablissement.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) typeDemandeEtablissement',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeDemandeEtablissement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeDemandeEtablissement')]
    public function delete(Request $request, TypeDemandeEtablissement $typeDemandeEtablissement, TypeDemandeEtablissementRepository $typeDemandeEtablissementRepository): Response
    {
        try {
            if ($typeDemandeEtablissement != null) {
                $typeDemandeEtablissementRepository->remove($typeDemandeEtablissement, true);

                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($typeDemandeEtablissement);
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
