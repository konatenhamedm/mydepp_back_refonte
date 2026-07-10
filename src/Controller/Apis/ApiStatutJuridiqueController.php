<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\StatutJuridique;
use App\Repository\StatutJuridiqueRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;

#[Route('/api/statutJuridique')]
class ApiStatutJuridiqueController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des statutJuridique.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des statutJuridique',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: StatutJuridique::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'statutJuridique')]
    public function index(StatutJuridiqueRepository $statutJuridiqueRepository): Response
    {
        try {
            $statutJuridiques = $statutJuridiqueRepository->findAll();
            return $this->responseData($statutJuridiques, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) statutJuridique en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) statutJuridique en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: StatutJuridique::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'statutJuridique')]
    public function getOne(?StatutJuridique $statutJuridique)
    {
        try {
            if ($statutJuridique) {
                $response = $this->response($statutJuridique);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($statutJuridique);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer un(e) statutJuridique.
     */
    #[OA\Post(
        summary: "Création d'un statut juridique",
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
    #[OA\Tag(name: 'statutJuridique')]
    public function create(Request $request, StatutJuridiqueRepository $statutJuridiqueRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $statutJuridique = new StatutJuridique();
        $statutJuridique->setLibelle($data['libelle']);
        $statutJuridique->setCode($data['code'] ?? null);
        $statutJuridique->setCreatedBy($this->getUser());
        $statutJuridique->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($statutJuridique);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $statutJuridiqueRepository->add($statutJuridique, true);
        }

        return $this->responseData($statutJuridique, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Mise à jour d'un statut juridique",
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
    #[OA\Tag(name: 'statutJuridique')]
    public function update(Request $request, StatutJuridique $statutJuridique, StatutJuridiqueRepository $statutJuridiqueRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($statutJuridique != null) {
                $statutJuridique->setLibelle($data->libelle);
                $statutJuridique->setCode($data->code ?? null);
                $statutJuridique->setUpdatedBy($this->getUser());
                $statutJuridique->setUpdatedAt();

                $errorResponse = $this->errorResponse($statutJuridique);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $statutJuridiqueRepository->add($statutJuridique, true);
                }

                $response = $this->responseData($statutJuridique, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) statutJuridique.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) statutJuridique',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: StatutJuridique::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'statutJuridique')]
    public function delete(Request $request, StatutJuridique $statutJuridique, StatutJuridiqueRepository $statutJuridiqueRepository): Response
    {
        try {
            if ($statutJuridique != null) {
                $statutJuridiqueRepository->remove($statutJuridique, true);

                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($statutJuridique);
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
