<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\AutreDocumentProfessionnel;
use App\Repository\AutreDocumentProfessionnelRepository;
use App\Repository\ProfessionnelRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\Registry;

#[Route('/api/autre-document-professionnel')]
class ApiAutreDocumentProfessionnelController extends ApiInterface
{
    // ─── GET: liste des docs d'un professionnel ──────────────────────────────
    #[Route('/professionnel/{id}', methods: ['GET'])]
    #[OA\Tag(name: 'autre_document_professionnel')]
    public function getByProfessionnel(int $id, AutreDocumentProfessionnelRepository $repository, ProfessionnelRepository $professionnelRepository): Response
    {
        try {
            // L'ID reçu est l'ID de la Personne. On cherche le Professionnel associé.
            $professionnel = $professionnelRepository->findOneBy(['personne' => $id]);
            if (!$professionnel) {
                return $this->json([], Response::HTTP_OK);
            }
            $documents = $repository->findBy(['professionnel' => $professionnel]);

            $formatted = array_map(function ($doc) {
                $fichier = $doc->getDocument();
                return [
                    'id'          => $doc->getId(),
                    'typeLibelle' => $doc->getTypeAutreDocument()?->getLibelle() ?? '',
                    'etape'       => $doc->getEtape(),
                    'statut'      => $doc->getStatut(),   // null | 'valide' | 'invalide'
                    'message'     => $doc->getMessage(),   // message admin
                    'document'    => $fichier
                        ? ['path' => $fichier->getPath(), 'alt' => $fichier->getAlt()]
                        : null,
                ];
            }, $documents);

            return $this->json($formatted, Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->setMessage($e->getMessage());
            return $this->json([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ─── POST: le professionnel soumet (upload) un document ──────────────────
    #[Route('/{id}/soumettre', methods: ['POST'])]
    #[OA\Tag(name: 'autre_document_professionnel')]
    public function soumettre(
        int $id,
        Request $request,
        AutreDocumentProfessionnelRepository $repository,
        ProfessionnelRepository $professionnelRepository,
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

            // Sauvegarde fichier
            $filePrefix = str_slug('autre_doc_' . $id);
            $filePath   = $this->getUploadDir(self::UPLOAD_PATH, true);
            $fichier    = $this->utils->sauvegardeFichier($filePath, $filePrefix, $uploadedFile, self::UPLOAD_PATH);

            if ($fichier) {
                $doc->setDocument($fichier);
                // Reset du statut → admin devra re-valider ce document
                $doc->setStatut(null);
                $doc->setMessage(null);
                $this->em->persist($doc);
                $this->em->flush();
            }

            return $this->json([
                'success' => true,
                'message' => 'Document uploadé avec succès',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ─── POST: Soumission globale par le professionnel ───────────────────────
    #[Route('/professionnel/{id}/soumettre-tout', methods: ['POST'])]
    #[OA\Tag(name: 'autre_document_professionnel')]
    public function soumettreTout(
        int $id,
        AutreDocumentProfessionnelRepository $repository,
        ProfessionnelRepository $professionnelRepository
    ): Response {
        try {
            $professionnel = $professionnelRepository->find($id);
            if (!$professionnel) {
                return $this->json(['error' => 'Professionnel introuvable'], Response::HTTP_NOT_FOUND);
            }

            $docs = $repository->findBy(['professionnel' => $id]);
            if (empty($docs)) {
                return $this->json(['error' => 'Aucun document attendu.'], Response::HTTP_BAD_REQUEST);
            }

            // Vérifier que tous les documents ont un fichier attaché
            foreach ($docs as $doc) {
                if (!$doc->getDocument()) {
                    return $this->json(['error' => 'Veuillez joindre tous les documents requis avant de soumettre.'], Response::HTTP_BAD_REQUEST);
                }
            }

            // Récupérer l'étape précédente depuis l'un des documents
            $etape = null;
            foreach ($docs as $doc) {
                if ($doc->getEtape()) {
                    $etape = $doc->getEtape();
                    break;
                }
            }

            if ($etape) {
                $professionnel->setStatus($etape);
                $this->em->persist($professionnel);
                
                // Mettre à jour tous les documents au statut 'en attente' (null)
                foreach ($docs as $doc) {
                    $doc->setStatut(null);
                    $this->em->persist($doc);
                }

                $this->em->flush();

                // Historiser l'action manuellement
                $this->logWorkflow($professionnel, $etape);
            }

            return $this->json([
                'success' => true,
                'message' => 'Dossier soumis avec succès à l\'administration.'
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ─── PUT: l'admin valide ou invalide un document ─────────────────────────
    #[Route('/{id}/valider', methods: ['PUT', 'PATCH'])]
    #[OA\Tag(name: 'autre_document_professionnel')]
    public function valider(
        int $id,
        Request $request,
        AutreDocumentProfessionnelRepository $repository,
        ProfessionnelRepository $professionnelRepository,
        Registry $workflowRegistry
    ): Response {
        try {
            $doc = $repository->find($id);
            if (!$doc) {
                return $this->json(['error' => 'Document introuvable'], Response::HTTP_NOT_FOUND);
            }

            $body    = json_decode($request->getContent(), true) ?? [];
            $statut  = $body['statut']  ?? null;   // 'valide' | 'invalide'
            $message = $body['message'] ?? null;

            if (!in_array($statut, ['valide', 'invalide'])) {
                return $this->json(['error' => 'Statut invalide (valide|invalide)'], Response::HTTP_BAD_REQUEST);
            }

            $doc->setStatut($statut);
            $doc->setMessage($message);
            $this->em->persist($doc);
            $this->em->flush();

            // Si tous 'valide' → déclencher la transition
            $professionnel       = $doc->getProfessionnel();
            $transitionTriggered = false;

            if ($professionnel && $statut === 'valide') {
                $allDocs   = $repository->findBy(['professionnel' => $professionnel->getId()]);
                $allValide = count($allDocs) > 0 && array_reduce(
                    $allDocs,
                    fn($carry, $d) => $carry && $d->getStatut() === 'valide',
                    true
                );

                if ($allValide) {
                    $workflow = $workflowRegistry->get($professionnel, 'validation_compte');
                    if ($workflow->can($professionnel, 'retour_document_supplementaire')) {
                        $workflow->apply($professionnel, 'retour_document_supplementaire');
                        $professionnelRepository->add($professionnel, true);
                        $this->logWorkflow($professionnel, 'retour_document_supplementaire');
                        $transitionTriggered = true;
                    }
                }
            }

            return $this->json([
                'success'             => true,
                'statut'              => $doc->getStatut(),
                'transitionTriggered' => $transitionTriggered,
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ─── Helper ──────────────────────────────────────────────────────────────
    private function logWorkflow(\App\Entity\Professionnel $professionnel, string $etape): void
    {
        $vw = new \App\Entity\ValidationWorkflow();
        $vw->setEtape($etape);
        $vw->setPersonne($professionnel);
        $vw->setCreatedAtValue(new \DateTimeImmutable());
        $vw->setUpdatedAt(new \DateTimeImmutable());
        $vw->setCreatedBy($this->getUser());
        $vw->setUpdatedBy($this->getUser());
        $this->em->persist($vw);
        $this->em->flush();
    }
}
