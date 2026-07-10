<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\TypeAutreDocument;
use App\Repository\TypeAutreDocumentRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/type-autre-document')]
class ApiTypeAutreDocumentController extends ApiInterface
{
    #[Route('', methods: ['GET'])]
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
            $types = $repository->findAll();
            return $this->responseData($types, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }
}
