<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\AutreDocumentProfessionnel;
use App\Repository\AutreDocumentProfessionnelRepository;
use App\Repository\ProfessionnelRepository;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\Registry;

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
                    'id'          => $doc->getId(),
                    'typeLibelle' => $doc->getTypeAutreDocument() ? $doc->getTypeAutreDocument()->getLibelle() : '',
                    'etape'       => $doc->getEtape(),
                    'document'    => $fichier ? ['path' => $fichier->getPath(), 'alt' => $fichier->getAlt()] : null,
                ];
            }, $documents);

            return $this->response(json_encode($formatted), Response::HTTP_OK, ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }

    /**
     * Upload le fichier d'un document supplémentaire et déclenche la transition
     * retour_document_supplementaire si tous les docs du professionnel sont soumis.
     */
    #[Route('/{id}/soumettre', methods: ['POST'])]
    #[OA\Tag(name: 'autre_document_professionnel')]
    public function soumettre(
        int $id,
        Request $request,
        AutreDocumentProfessionnelRepository $repository,
        ProfessionnelRepository $professionnelRepository,
        UserRepository $userRepository,
        Registry $workflowRegistry
    ): Response {
        try {
            $doc = $repository->find($id);
            if (!$doc) {
                return $this->json(['error' => 'Document introuvable'], Response::HTTP_NOT_FOUND);
            }

            $uploadedFile = $request->files->get('document');
            if (!$uploadedFile) {
                return $this->json(['error' => 'Fichier manquant'], Response::HTTP_BAD_REQUEST);
            }

            // Sauvegarde du fichier
            $names      = 'autre_doc_' . $id;
            $filePrefix = str_slug($names);
            $filePath   = $this->getUploadDir(self::UPLOAD_PATH, true);
            $fichier    = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);

            if ($fichier) {
                $doc->setDocument($fichier);
                $this->em->persist($doc);
                $this->em->flush();
            }

            // Vérifier si TOUS les docs du professionnel sont maintenant soumis
            $professionnel = $doc->getProfessionnel();
            if ($professionnel) {
                $allDocs    = $repository->findBy(['professionnel' => $professionnel->getId()]);
                $allFilled  = array_reduce($allDocs, fn($carry, $d) => $carry && $d->getDocument() !== null, true);

                if ($allFilled && count($allDocs) > 0) {
                    $workflow = $workflowRegistry->get($professionnel, 'validation_compte');
                    if ($workflow->can($professionnel, 'retour_document_supplementaire')) {
                        $workflow->apply($professionnel, 'retour_document_supplementaire');
                        $professionnelRepository->add($professionnel, true);

                        // Log ValidationWorkflow
                        $validationWorkflow = new \App\Entity\ValidationWorkflow();
                        $validationWorkflow->setEtape('retour_document_supplementaire');
                        $validationWorkflow->setPersonne($professionnel);
                        $validationWorkflow->setCreatedAtValue(new \DateTimeImmutable());
                        $validationWorkflow->setUpdatedAt(new \DateTimeImmutable());
                        $validationWorkflow->setCreatedBy($this->getUser());
                        $validationWorkflow->setUpdatedBy($this->getUser());
                        $this->em->persist($validationWorkflow);
                        $this->em->flush();
                    }
                }
            }

            return $this->json([
                'success'  => true,
                'message'  => 'Document soumis avec succès',
                'etapeCible' => $doc->getEtape(),
            ]);
        } catch (\Exception $exception) {
            return $this->json([
                'error'   => 'Une erreur est survenue',
                'details' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
