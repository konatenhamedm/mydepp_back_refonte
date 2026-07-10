<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\NiveauFormation;
use App\Repository\NiveauFormationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;

#[Route('/api/niveauFormation')]
class ApiNiveauFormationController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des niveauFormation.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des niveauFormation',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NiveauFormation::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'niveauFormation')]
    public function index(NiveauFormationRepository $niveauFormationRepository): Response
    {
        try {
            $niveauFormations = $niveauFormationRepository->findAll();
            return $this->responseData($niveauFormations, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) niveauFormation en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) niveauFormation en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NiveauFormation::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'niveauFormation')]
    public function getOne(?NiveauFormation $niveauFormation)
    {
        try {
            if ($niveauFormation) {
                $response = $this->response($niveauFormation);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($niveauFormation);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer un(e) niveauFormation.
     */
    #[OA\Post(
        summary: "Création d'un niveau de formation",
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
    #[OA\Tag(name: 'niveauFormation')]
    public function create(Request $request, NiveauFormationRepository $niveauFormationRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $niveauFormation = new NiveauFormation();
        $niveauFormation->setLibelle($data['libelle']);
        $niveauFormation->setCode($data['code'] ?? null);
        $niveauFormation->setCreatedBy($this->getUser());
        $niveauFormation->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($niveauFormation);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $niveauFormationRepository->add($niveauFormation, true);
        }

        return $this->responseData($niveauFormation, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Mise à jour d'un niveau de formation",
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
    #[OA\Tag(name: 'niveauFormation')]
    public function update(Request $request, NiveauFormation $niveauFormation, NiveauFormationRepository $niveauFormationRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($niveauFormation != null) {
                $niveauFormation->setLibelle($data->libelle);
                $niveauFormation->setCode($data->code ?? null);
                $niveauFormation->setUpdatedBy($this->getUser());
                $niveauFormation->setUpdatedAt();

                $errorResponse = $this->errorResponse($niveauFormation);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $niveauFormationRepository->add($niveauFormation, true);
                }

                $response = $this->responseData($niveauFormation, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) niveauFormation.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) niveauFormation',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: NiveauFormation::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'niveauFormation')]
    public function delete(Request $request, NiveauFormation $niveauFormation, NiveauFormationRepository $niveauFormationRepository): Response
    {
        try {
            if ($niveauFormation != null) {
                $niveauFormationRepository->remove($niveauFormation, true);

                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($niveauFormation);
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
