<?php

namespace App\Controller;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Setting;
use App\Repository\SettingRepository;
use App\Repository\UserRepository;
use App\Service\JwtService;
use App\Service\SubscriptionChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use OpenApi\Attributes as OA;


class AuthController extends ApiInterface
{


    #[Route('/api/login', methods: ['POST'])]
    #[OA\Post(
        summary: "Permet d'authentifier un utilisateur",
        description: "Permet d'authentifier un utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "email",
                        type: "string",
                        default: "konatenhamed@gmail.com"
                    ),
                    new OA\Property(
                        property: "password",
                        type: "string",
                        default: "admin_93K"
                    ),
                    new OA\Property(
                        property: "plateforme",
                        type: "string",
                        default: "backoffice"
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials"),
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    #[OA\Tag(name: 'auth')]
    public function login(
        Request $request,
        JwtService $jwtService,
        UserPasswordHasherInterface $hasher,
        UserRepository $userRepo,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        //dd($data['email']);
        $user = $userRepo->findOneBy(['email' => $data['email']]);


        if (!$user || !$hasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        } elseif (!$user->isActive()) {

            return $this->errorResponse($user, 'User is not active');
        }
        if ($data['plateforme'] == "backoffice" && ($user->getTypeUser() != "ADMINISTRATEUR" || $user->getTypeUser() != "ETABLISSEMENT" || $user->getTypeUser() != "PROFESSIONNEL")) {
            return $this->json(['error' => 'Invalid car vous devez être un administrateur'], Response::HTTP_UNAUTHORIZED);
        } elseif ($data['plateforme'] == "front") {
            if ($user->getTypeUser() != "PROFESSIONNEL" && $user->getTypeUser() != "ETABLISSEMENT") {
                return $this->json(['error' => 'Invalid car vous devez être un professionnel ou un établissement'], Response::HTTP_UNAUTHORIZED);
            }
        }


        $token = $jwtService->generateToken([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ]);
        $expire = true;
        $finRenouvelement = "";

        return $this->responseData([
            'token' => $token,
            'data' => [
                'id' => $user->getId(),
                'role' => $user->getRoles(),
                "expire" => $user->getPersonne()->getStatus() == "renouvellement" ? true : false,
                "finRenouvellement" => $finRenouvelement,
                'username' => $user->getTypeUser() == "ADMINISTRATEUR" ? $user->getUsername() : $user->getUserIdentifier(),
                'avatar' => ($user->getTypeUser() == "PROFESSIONNEL") ? ($user->getAvatar()
                    ? $user->getAvatar()->getPath() . '/' . $user->getAvatar()->getAlt()
                    : $user->getPersonne()->getPhoto()->getPath() . '/' . $user->getPersonne()->getPhoto()->getAlt()
                ) : null,
                'status' =>  $user->getPersonne()->getStatus(),
                'nom' => $user->getTypeUser() === "PROFESSIONNEL"
                    ? $user->getPersonne()->getNom() . " " . $user->getPersonne()->getPrenoms()
                    : ($user->getTypeUser() === "ETABLISSEMENT"
                        ? ($user->getPersonne()->getTypePersonne()->getCode() === "PHYSIQUE"
                            ? $user->getPersonne()->getNom() . " " . $user->getPersonne()->getPrenoms()
                            : $user->getPersonne()->getDenomination()
                        )
                        : null
                    ),

                'payement' => $user->getPayement(),
                'type' => $user->getTypeUser(),
                'typePersonne' => $user->getTypeUser() == "ETABLISSEMENT" ? $user->getPersonne()->getTypePersonne()->getId() : null,
                'personneId' => $user->getTypeUser() == "ADMINISTRATEUR" ? null : $user->getPersonne()->getId(),
                'token_expires_in' => $jwtService->getTtl()
            ]
        ], 'group1', ['Content-Type' => 'application/json']);
    }
}
