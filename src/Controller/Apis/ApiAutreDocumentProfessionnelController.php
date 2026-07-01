<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\AutreDocumentProfessionnel;
use App\Repository\AutreDocumentProfessionnelRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/autre-document-professionnel')]
class ApiAutreDocumentProfessionnelController extends ApiInterface
{
    #[Route('/professionnel/{id}', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des documents supplémentaires demandés pour un professionnel',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: AutreDocumentProfessionnel::class, groups: ['group1', 'fichier']))
        )
    )]
    #[OA\Tag(name: 'autre_document_professionnel')]
    public function getByProfessionnel(int $id, AutreDocumentProfessionnelRepository $repository): Response
    {
        try {
            $documents = $repository->findBy(['professionnel' => $id]);
            
            $formatted = array_map(function($doc) {
                $fichier = $doc->getDocument();
                return [
                    'id' => $doc->getId(),
                    'typeLibelle' => $doc->getTypeAutreDocument() ? $doc->getTypeAutreDocument()->getLibelle() : '',
                    'etape' => $doc->getEtape(),
                    'document' => $fichier ? ['path' => $fichier->getPath(), 'alt' => $fichier->getAlt()] : null,
                ];
            }, $documents);

            return $this->response(json_encode($formatted), Response::HTTP_OK, ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }
}
