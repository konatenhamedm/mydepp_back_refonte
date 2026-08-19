<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\TypeAutreDocument;
use App\Repository\TypeAutreDocumentRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/type-autre-document')]
class ApiTypeAutreDocumentController extends ApiInterface
{
    #[Route('', methods: ['GET'])]
    #[Route('/', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des types de documents supplémentaires',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeAutreDocument::class, groups: ['group_libelle', 'group1']))
        )
    )]
    #[OA\Tag(name: 'type_autre_document')]
    public function index(TypeAutreDocumentRepository $repository): Response
    {
        try {
            $types = $repository->findBy([], ['id' => 'DESC']);
            return $this->responseData($types, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }

    #[Route('/get/one/{id}', methods: ['GET'])]
    #[OA\Tag(name: 'type_autre_document')]
    public function getOne(?TypeAutreDocument $typeAutreDocument): Response
    {
        try {
            if ($typeAutreDocument) {
                return $this->responseData($typeAutreDocument, 'group1', ['Content-Type' => 'application/json']);
            }
            $this->setMessage('Cette ressource est inexistante');
            $this->setStatusCode(300);
            return $this->response('[]');
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }

    #[Route('/create', methods: ['POST'])]
    #[OA\Tag(name: 'type_autre_document')]
    public function create(Request $request, TypeAutreDocumentRepository $repository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (empty($data['libelle'])) {
                $this->setMessage("Le libellé est obligatoire");
                return $this->response('[]');
            }

            $typeAutreDoc = new TypeAutreDocument();
            $typeAutreDoc->setLibelle(trim($data['libelle']));
            $typeAutreDoc->setCreatedAtValue();
            $typeAutreDoc->setUpdatedAt();
            if ($this->getUser()) {
                $typeAutreDoc->setCreatedBy($this->getUser());
                $typeAutreDoc->setUpdatedBy($this->getUser());
            }

            $errorResponse = $this->errorResponse($typeAutreDoc);
            if ($errorResponse !== null) {
                return $errorResponse;
            }

            $repository->add($typeAutreDoc, true);
            $this->setMessage("Type de document supplémentaire créé avec succès");
            return $this->responseData($typeAutreDoc, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }

    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Tag(name: 'type_autre_document')]
    public function update(Request $request, ?TypeAutreDocument $typeAutreDocument, TypeAutreDocumentRepository $repository): Response
    {
        try {
            if (!$typeAutreDocument) {
                $this->setMessage("Cette ressource est inexistante");
                $this->setStatusCode(300);
                return $this->response('[]');
            }

            $data = json_decode($request->getContent(), true);
            if (!empty($data['libelle'])) {
                $typeAutreDocument->setLibelle(trim($data['libelle']));
            }
            $typeAutreDocument->setUpdatedAt();
            if ($this->getUser()) {
                $typeAutreDocument->setUpdatedBy($this->getUser());
            }

            $errorResponse = $this->errorResponse($typeAutreDocument);
            if ($errorResponse !== null) {
                return $errorResponse;
            }

            $repository->add($typeAutreDocument, true);
            $this->setMessage("Type de document supplémentaire modifié avec succès");
            return $this->responseData($typeAutreDocument, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }

    #[Route('/delete/{id}', methods: ['DELETE'])]
    #[OA\Tag(name: 'type_autre_document')]
    public function delete(?TypeAutreDocument $typeAutreDocument, TypeAutreDocumentRepository $repository): Response
    {
        try {
            if ($typeAutreDocument) {
                $repository->remove($typeAutreDocument, true);
                $this->setMessage("Type de document supplémentaire supprimé avec succès");
                return $this->response($typeAutreDocument);
            }
            $this->setMessage("Cette ressource est inexistante");
            $this->setStatusCode(300);
            return $this->response('[]');
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }

    #[Route('/delete/all', methods: ['DELETE'])]
    #[OA\Tag(name: 'type_autre_document')]
    public function deleteAll(Request $request, TypeAutreDocumentRepository $repository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if (isset($data->ids) && is_array($data->ids)) {
                foreach ($data->ids as $value) {
                    $id = is_array($value) ? ($value['id'] ?? null) : ($value->id ?? $value);
                    if ($id) {
                        $entity = $repository->find($id);
                        if ($entity) {
                            $repository->remove($entity);
                        }
                    }
                }
                $repository->getEntityManager()->flush();
            }
            $this->setMessage("Suppression multiple effectuée avec succès");
            return $this->response('[]');
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }
}
