<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Reunion;
use App\Repository\ReunionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/reunion')]
class ApiReunionController extends ApiInterface
{
    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des réunions.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des réunions',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Reunion::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'reunion')]
    public function index(Request $request, ReunionRepository $reunionRepository): Response
    {
        try {
            $startDate = $request->query->get('start_date');
            $endDate = $request->query->get('end_date');
            $type = $request->query->get('type');

            if ($startDate || $endDate || $type) {
                $reunions = $reunionRepository->findByAdvancedFilter($startDate, $endDate, $type);
            } else {
                $reunions = $reunionRepository->findBy([], ['id' => 'DESC']);
            }
            
            return $this->responseData($reunions, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche une réunion en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche une réunion en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Reunion::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'reunion')]
    public function getOne(?Reunion $reunion)
    {
        try {
            if ($reunion) {
                $response = $this->response($reunion);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($reunion);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/public/{token}', methods: ['GET'])]
    /**
     * Endpoint PUBLIC : retourne les infos d'une réunion via son token (pour la page de présence).
     */
    #[OA\Tag(name: 'reunion')]
    public function publicByToken(string $token, ReunionRepository $reunionRepository): Response
    {
        try {
            $reunion = $reunionRepository->findOneBy(['token' => $token]);
            if ($reunion) {
                $response = $this->responseData($reunion, 'group1', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage('Réunion introuvable');
                $this->setStatusCode(404);
                $response = $this->response('[]');
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer une réunion.
     */
    #[OA\Post(
        summary: "Création de réunion",
        description: "Permet de créer une réunion.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "objet", type: "string"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'reunion')]
    public function create(Request $request, ReunionRepository $reunionRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $reunion = new Reunion();
        $reunion->setObjet($data['objet'] ?? '');
        $type = $data['type'] ?? 'presentiel';
        $reunion->setType($type);
        // Le lien n'a de sens que pour une réunion en ligne
        $reunion->setLien($type === 'en_ligne' ? ($data['lien'] ?? null) : null);
        $reunion->setJour(!empty($data['jour']) ? new \DateTime($data['jour']) : null);
        $reunion->setToken(bin2hex(random_bytes(16)));
        $reunion->setCreatedBy($this->getUser());
        $reunion->setUpdatedBy($this->getUser());

        $errorResponse = $this->errorResponse($reunion);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $reunionRepository->add($reunion, true);
        }

        return $this->responseData($reunion, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Mise à jour de réunion",
        description: "Permet de modifier une réunion.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "objet", type: "string"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'reunion')]
    public function update(Request $request, Reunion $reunion, ReunionRepository $reunionRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($reunion != null) {

                $reunion->setObjet($data->objet);
                $type = $data->type ?? 'presentiel';
                $reunion->setType($type);
                $reunion->setLien($type === 'en_ligne' ? ($data->lien ?? null) : null);
                $reunion->setJour(!empty($data->jour) ? new \DateTime($data->jour) : null);
                $reunion->setUpdatedBy($this->getUser());
                $reunion->setUpdatedAt();

                $errorResponse = $this->errorResponse($reunion);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $reunionRepository->add($reunion, true);
                }

                $response = $this->responseData($reunion, 'group1', ['Content-Type' => 'application/json']);
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
     * Permet de supprimer une réunion.
     */
    #[OA\Response(
        response: 200,
        description: 'Permet de supprimer une réunion',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Reunion::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'reunion')]
    public function delete(Request $request, Reunion $reunion, ReunionRepository $reunionRepository): Response
    {
        try {
            if ($reunion != null) {
                $reunionRepository->remove($reunion, true);

                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($reunion);
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

    #[Route('/delete/all', methods: ['DELETE'])]
    /**
     * Permet de supprimer plusieurs réunions.
     */
    #[OA\Response(
        response: 200,
        description: 'Permet de supprimer plusieurs réunions',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Reunion::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'reunion')]
    public function deleteAll(Request $request, ReunionRepository $reunionRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $reunion = $reunionRepository->find($value['id']);

                if ($reunion != null) {
                    $reunionRepository->remove($reunion);
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
