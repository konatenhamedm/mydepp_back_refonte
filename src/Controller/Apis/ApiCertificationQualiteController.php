<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\CertificationQualite;
use App\Repository\CertificationQualiteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;

#[Route('/api/certificationQualite')]
class ApiCertificationQualiteController extends ApiInterface
{

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des certificationQualite.
     */
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des certificationQualite',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: CertificationQualite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'certificationQualite')]
    public function index(CertificationQualiteRepository $certificationQualiteRepository): Response
    {
        try {
            $certificationQualites = $certificationQualiteRepository->findAll();
            return $this->responseData($certificationQualites, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) certificationQualite en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) certificationQualite en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: CertificationQualite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'certificationQualite')]
    public function getOne(?CertificationQualite $certificationQualite)
    {
        try {
            if ($certificationQualite) {
                $response = $this->response($certificationQualite);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($certificationQualite);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/create', methods: ['POST'])]
    /**
     * Permet de créer un(e) certificationQualite.
     */
    #[OA\Post(
        summary: "Création d'une certification qualité",
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
    #[OA\Tag(name: 'certificationQualite')]
    public function create(Request $request, CertificationQualiteRepository $certificationQualiteRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $certificationQualite = new CertificationQualite();
        $certificationQualite->setLibelle($data['libelle']);
        $certificationQualite->setCode($data['code'] ?? null);
        $certificationQualite->setCreatedBy($this->getUser());
        $certificationQualite->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($certificationQualite);
        if ($errorResponse !== null) {
            return $errorResponse;
        } else {
            $certificationQualiteRepository->add($certificationQualite, true);
        }

        return $this->responseData($certificationQualite, 'group1', ['Content-Type' => 'application/json']);
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Mise à jour d'une certification qualité",
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
    #[OA\Tag(name: 'certificationQualite')]
    public function update(Request $request, CertificationQualite $certificationQualite, CertificationQualiteRepository $certificationQualiteRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($certificationQualite != null) {
                $certificationQualite->setLibelle($data->libelle);
                $certificationQualite->setCode($data->code ?? null);
                $certificationQualite->setUpdatedBy($this->getUser());
                $certificationQualite->setUpdatedAt();

                $errorResponse = $this->errorResponse($certificationQualite);

                if ($errorResponse !== null) {
                    return $errorResponse;
                } else {
                    $certificationQualiteRepository->add($certificationQualite, true);
                }

                $response = $this->responseData($certificationQualite, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) certificationQualite.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) certificationQualite',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: CertificationQualite::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'certificationQualite')]
    public function delete(Request $request, CertificationQualite $certificationQualite, CertificationQualiteRepository $certificationQualiteRepository): Response
    {
        try {
            if ($certificationQualite != null) {
                $certificationQualiteRepository->remove($certificationQualite, true);

                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($certificationQualite);
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
