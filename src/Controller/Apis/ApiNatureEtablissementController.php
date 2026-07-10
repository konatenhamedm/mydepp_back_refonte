<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\NatureEtablissement;
use App\Repository\NatureEtablissementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;

#[Route('/api/natureEtablissement')]
class ApiNatureEtablissementController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des natureEtablissement.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des natureEtablissement',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NatureEtablissement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'natureEtablissement')]
    public function index(NatureEtablissementRepository $natureEtablissementRepository): Response
    {
        try {
            $natureEtablissements = $natureEtablissementRepository->findAll();
            return $this->responseData($natureEtablissements, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) natureEtablissement en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) natureEtablissement en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NatureEtablissement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'natureEtablissement')]
    public function getOne(?NatureEtablissement $natureEtablissement)
    {
        try {
            if ($natureEtablissement) {
                $response = $this->response($natureEtablissement);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($natureEtablissement);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer un(e) natureEtablissement.
     */
    #[OA\Post(
        summary: "Création d'une nature d'établissement",
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
    #[OA\Tag(name: 'natureEtablissement')]
    public function create(Request $request, NatureEtablissementRepository $natureEtablissementRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $code = trim($data['code'] ?? '');
        if ($code === '') {
            $code = $this->utils->generateShortCodeFromLibelle($data['libelle'], $natureEtablissementRepository);
        }
        $natureEtablissement = new NatureEtablissement();
        $natureEtablissement->setLibelle($data['libelle']);
        $natureEtablissement->setCode($code);
        $natureEtablissement->setCreatedBy($this->getUser());
        $natureEtablissement->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($natureEtablissement);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $natureEtablissementRepository->add($natureEtablissement, true);
        }

        return $this->responseData($natureEtablissement, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Mise à jour d'une nature d'établissement",
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
    #[OA\Tag(name: 'natureEtablissement')]
    public function update(Request $request, NatureEtablissement $natureEtablissement, NatureEtablissementRepository $natureEtablissementRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($natureEtablissement != null) {
                $code = trim($data->code ?? '');
                if ($code === '') {
                    $code = $natureEtablissement->getCode() ?: $this->utils->generateShortCodeFromLibelle($data->libelle, $natureEtablissementRepository);
                }
                $natureEtablissement->setLibelle($data->libelle);
                $natureEtablissement->setCode($code);
                $natureEtablissement->setUpdatedBy($this->getUser());
                $natureEtablissement->setUpdatedAt();

                $errorResponse = $this->errorResponse($natureEtablissement);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $natureEtablissementRepository->add($natureEtablissement, true);
                }

                $response = $this->responseData($natureEtablissement, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) natureEtablissement.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) natureEtablissement',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NatureEtablissement::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'natureEtablissement')]
    public function delete(Request $request, NatureEtablissement $natureEtablissement, NatureEtablissementRepository $natureEtablissementRepository): Response
    {
        try {
            if ($natureEtablissement != null) {
                $natureEtablissementRepository->remove($natureEtablissement, true);

                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($natureEtablissement);
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
