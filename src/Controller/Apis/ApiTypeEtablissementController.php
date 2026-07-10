<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\TypeEtablissement;
use App\Repository\TypeEtablissementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;

#[Route('/api/typeEtablissement')]
class ApiTypeEtablissementController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des typeEtablissement.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des typeEtablissement',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeEtablissement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeEtablissement')]
    public function index(TypeEtablissementRepository $typeEtablissementRepository): Response
    {
        try {
            $typeEtablissements = $typeEtablissementRepository->findAll();
            return $this->responseData($typeEtablissements, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) typeEtablissement en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) typeEtablissement en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeEtablissement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeEtablissement')]
    public function getOne(?TypeEtablissement $typeEtablissement)
    {
        try {
            if ($typeEtablissement) {
                $response = $this->response($typeEtablissement);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($typeEtablissement);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer un(e) typeEtablissement.
     */
    #[OA\Post(
        summary: "Création d'un type d'établissement",
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
    #[OA\Tag(name: 'typeEtablissement')]
    public function create(Request $request, TypeEtablissementRepository $typeEtablissementRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $code = trim($data['code'] ?? '');
        if ($code === '') {
            $code = $this->utils->generateShortCodeFromLibelle($data['libelle'], $typeEtablissementRepository);
        }
        $typeEtablissement = new TypeEtablissement();
        $typeEtablissement->setLibelle($data['libelle']);
        $typeEtablissement->setCode($code);
        $typeEtablissement->setCreatedBy($this->getUser());
        $typeEtablissement->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($typeEtablissement);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $typeEtablissementRepository->add($typeEtablissement, true);
        }

        return $this->responseData($typeEtablissement, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Mise à jour d'un type d'établissement",
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
    #[OA\Tag(name: 'typeEtablissement')]
    public function update(Request $request, TypeEtablissement $typeEtablissement, TypeEtablissementRepository $typeEtablissementRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($typeEtablissement != null) {
                $code = trim($data->code ?? '');
                if ($code === '') {
                    $code = $typeEtablissement->getCode() ?: $this->utils->generateShortCodeFromLibelle($data->libelle, $typeEtablissementRepository);
                }
                $typeEtablissement->setLibelle($data->libelle);
                $typeEtablissement->setCode($code);
                $typeEtablissement->setUpdatedBy($this->getUser());
                $typeEtablissement->setUpdatedAt();

                $errorResponse = $this->errorResponse($typeEtablissement);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $typeEtablissementRepository->add($typeEtablissement, true);
                }

                $response = $this->responseData($typeEtablissement, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) typeEtablissement.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) typeEtablissement',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeEtablissement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeEtablissement')]
    public function delete(Request $request, TypeEtablissement $typeEtablissement, TypeEtablissementRepository $typeEtablissementRepository): Response
    {
        try {
            if ($typeEtablissement != null) {
                $typeEtablissementRepository->remove($typeEtablissement, true);

                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($typeEtablissement);
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
