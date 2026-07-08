<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\ResponsabiliteMedicolegale;
use App\Repository\ResponsabiliteMedicolegaleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;

#[Route('/api/responsabiliteMedicolegale')]
class ApiResponsabiliteMedicolegaleController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des responsabiliteMedicolegale.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des responsabiliteMedicolegale',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ResponsabiliteMedicolegale::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'responsabiliteMedicolegale')]
    public function index(ResponsabiliteMedicolegaleRepository $responsabiliteMedicolegaleRepository): Response
    {
        try {
            $responsabiliteMedicolegales = $responsabiliteMedicolegaleRepository->findAll();
            return $this->responseData($responsabiliteMedicolegales, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) responsabiliteMedicolegale en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) responsabiliteMedicolegale en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ResponsabiliteMedicolegale::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'responsabiliteMedicolegale')]
    public function getOne(?ResponsabiliteMedicolegale $responsabiliteMedicolegale)
    {
        try {
            if ($responsabiliteMedicolegale) {
                $response = $this->response($responsabiliteMedicolegale);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($responsabiliteMedicolegale);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer un(e) responsabiliteMedicolegale.
     */
    #[OA\Post(
        summary: "Création d'une responsabilité médicolégale",
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
    #[OA\Tag(name: 'responsabiliteMedicolegale')]
    public function create(Request $request, ResponsabiliteMedicolegaleRepository $responsabiliteMedicolegaleRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $responsabiliteMedicolegale = new ResponsabiliteMedicolegale();
        $responsabiliteMedicolegale->setLibelle($data['libelle']);
        $responsabiliteMedicolegale->setCode($data['code'] ?? null);
        $responsabiliteMedicolegale->setCreatedBy($this->getUser());
        $responsabiliteMedicolegale->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($responsabiliteMedicolegale);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $responsabiliteMedicolegaleRepository->add($responsabiliteMedicolegale, true);
        }

        return $this->responseData($responsabiliteMedicolegale, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Mise à jour d'une responsabilité médicolégale",
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
    #[OA\Tag(name: 'responsabiliteMedicolegale')]
    public function update(Request $request, ResponsabiliteMedicolegale $responsabiliteMedicolegale, ResponsabiliteMedicolegaleRepository $responsabiliteMedicolegaleRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($responsabiliteMedicolegale != null) {
                $responsabiliteMedicolegale->setLibelle($data->libelle);
                $responsabiliteMedicolegale->setCode($data->code ?? null);
                $responsabiliteMedicolegale->setUpdatedBy($this->getUser());
                $responsabiliteMedicolegale->setUpdatedAt();

                $errorResponse = $this->errorResponse($responsabiliteMedicolegale);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $responsabiliteMedicolegaleRepository->add($responsabiliteMedicolegale, true);
                }

                $response = $this->responseData($responsabiliteMedicolegale, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) responsabiliteMedicolegale.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) responsabiliteMedicolegale',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ResponsabiliteMedicolegale::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'responsabiliteMedicolegale')]
    public function delete(Request $request, ResponsabiliteMedicolegale $responsabiliteMedicolegale, ResponsabiliteMedicolegaleRepository $responsabiliteMedicolegaleRepository): Response
    {
        try {
            if ($responsabiliteMedicolegale != null) {
                $responsabiliteMedicolegaleRepository->remove($responsabiliteMedicolegale, true);

                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($responsabiliteMedicolegale);
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
