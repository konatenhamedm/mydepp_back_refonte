<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Service;
use App\Repository\ServiceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;

#[Route('/api/service')]
class ApiServiceController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des services.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des services',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Service::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'service')]
    public function index(ServiceRepository $serviceRepository): Response
    {
        try {
            $services = $serviceRepository->findAll();
            return $this->responseData($services, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) service en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) service en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Service::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'service')]
    public function getOne(?Service $service)
    {
        try {
            if ($service) {
                $response = $this->response($service);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($service);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer un(e) service.
     */
    #[OA\Post(
        summary: "Création d'un service",
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
    #[OA\Tag(name: 'service')]
    public function create(Request $request, ServiceRepository $serviceRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $service = new Service();
        $service->setLibelle($data['libelle']);
        $service->setCode($data['code'] ?? null);
        $service->setCreatedBy($this->getUser());
        $service->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($service);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $serviceRepository->add($service, true);
        }

        return $this->responseData($service, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Mise à jour d'un service",
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
    #[OA\Tag(name: 'service')]
    public function update(Request $request, Service $service, ServiceRepository $serviceRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($service != null) {
                $service->setLibelle($data->libelle);
                $service->setCode($data->code ?? null);
                $service->setUpdatedBy($this->getUser());
                $service->setUpdatedAt();

                $errorResponse = $this->errorResponse($service);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $serviceRepository->add($service, true);
                }

                $response = $this->responseData($service, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) service.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) service',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Service::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'service')]
    public function delete(Request $request, Service $service, ServiceRepository $serviceRepository): Response
    {
        try {
            if ($service != null) {
                $serviceRepository->remove($service, true);

                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($service);
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
