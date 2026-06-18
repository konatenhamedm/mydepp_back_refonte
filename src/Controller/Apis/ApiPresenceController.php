<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Presence;
use App\Repository\PresenceRepository;
use App\Repository\ReunionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/presence')]
class ApiPresenceController extends ApiInterface
{
    #[Route('/public/{token}', methods: ['POST'])]
    /**
     * Endpoint PUBLIC : enregistre la présence d'un participant à une réunion (via le token de la réunion).
     */
    #[OA\Post(
        summary: "Enregistrement de présence (public)",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nomPrenoms", type: "string"),
                    new OA\Property(property: "structure", type: "string"),
                    new OA\Property(property: "fonction", type: "string"),
                    new OA\Property(property: "telephone", type: "string"),
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "signature", type: "string"),
                ],
                type: "object"
            )
        )
    )]
    #[OA\Tag(name: 'presence')]
    public function publicCreate(string $token, Request $request, ReunionRepository $reunionRepository, PresenceRepository $presenceRepository): Response
    {
        try {
            $reunion = $reunionRepository->findOneBy(['token' => $token]);
            if (!$reunion) {
                $this->setMessage('Réunion introuvable');
                $this->setStatusCode(404);
                return $this->response('[]');
            }

            $data = json_decode($request->getContent(), true);

            $presence = new Presence();
            $presence->setReunion($reunion);
            $presence->setNomPrenoms($data['nomPrenoms'] ?? '');
            $presence->setStructure($data['structure'] ?? null);
            $presence->setFonction($data['fonction'] ?? null);
            $presence->setTelephone($data['telephone'] ?? null);
            $presence->setEmail($data['email'] ?? null);
            $presence->setSignature($data['signature'] ?? null);

            $errorResponse = $this->errorResponse($presence);
            if ($errorResponse !== null) {
                return $errorResponse;
            }

            $presenceRepository->add($presence, true);

            $this->setMessage('Présence enregistrée avec succès');
            return $this->responseData($presence, 'group_presence', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }

    #[Route('/reunion/{id}', methods: ['GET'])]
    /**
     * Liste des participants d'une réunion (authentifié - vue admin / détails réunion).
     */
    #[OA\Tag(name: 'presence')]
    public function byReunion(int $id, PresenceRepository $presenceRepository): Response
    {
        try {
            $presences = $presenceRepository->findBy(['reunion' => $id], ['id' => 'DESC']);
            return $this->responseData($presences, 'group_presence', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->responseData([], 'group_presence', ['Content-Type' => 'application/json']);
        }
    }

    #[Route('/delete/{id}', methods: ['DELETE'])]
    /**
     * Supprime un participant.
     */
    #[OA\Tag(name: 'presence')]
    public function delete(Presence $presence, PresenceRepository $presenceRepository): Response
    {
        try {
            if ($presence != null) {
                $presenceRepository->remove($presence, true);
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($presence);
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
