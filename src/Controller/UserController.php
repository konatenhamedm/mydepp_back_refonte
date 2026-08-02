<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;


class UserController extends AbstractController
{
    #[Route('/api/user/{id}/toggle-active', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/users/{id}/toggle-active',
        summary: 'Activer ou désactiver un utilisateur',
        tags: ['user'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Statut modifié'),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé')
        ]
    )]
    #[OA\Tag(name: 'user')]
    public function toggleActive(User $user, UserRepository $repository): JsonResponse
    {
        $repository->updateActiveStatus($user, !$user->isActive());
        return $this->json(['message' => 'Status updated']);
    }

    #[Route('/api/user/{id}/update-email', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/user/{id}/update-email',
        summary: 'Mettre à jour l\'email d\'un utilisateur',
        tags: ['user'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'email', type: 'string', example: 'nouveau@email.com')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Email mis à jour'),
            new OA\Response(response: 400, description: 'Email invalide ou déjà utilisé'),
            new OA\Response(response: 404, description: 'Utilisateur non trouvé')
        ]
    )]
    #[OA\Tag(name: 'user')]
    public function updateEmail(User $user, Request $request, UserRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $newEmail = $data['email'] ?? null;

        if (!$newEmail || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['message' => 'Email invalide'], 400);
        }

        $existingUser = $repository->findOneBy(['email' => $newEmail]);
        if ($existingUser && $existingUser->getId() !== $user->getId()) {
            return $this->json(['message' => 'Cet email est déjà utilisé'], 400);
        }

        $user->setEmail($newEmail);
        $em->flush();

        return $this->json(['message' => 'Email mis à jour avec succès', 'email' => $newEmail]);
    }
}
