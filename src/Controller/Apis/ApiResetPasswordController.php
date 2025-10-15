<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\CiviliteDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Civilite;
use App\Repository\CiviliteRepository;
use App\Repository\UserRepository;
use App\Service\ResetPasswordService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/resetpassword')]
class ApiResetPasswordController extends ApiInterface
{

    #[Route('/reset/email', methods: ['POST'])]
    /**
     * Affiche un(e) civilite en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) civilite en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Civilite::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'resetpassword')]
    //
    public function resetEmail(Request $request, UserRepository $userRepository, ResetPasswordService $resetPasswordServicee): Response
    {

        $data = json_decode($request->getContent(), true);


        $email = $data['email'];
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {

            $this->setMessage("Cette ressource est inexsitante");
            $this->setStatusCode(300);
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }

        // Generate a reset token and send the email
        $resetPasswordServicee->sendResetPasswordEmail($user);

        return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
    }
    #[Route('/reset/email/admin', methods: ['POST'])]
    /**
     * Affiche un(e) civilite en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) civilite en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Civilite::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'resetpassword')]
    //
    public function resetEmailAdmin(Request $request, UserRepository $userRepository, ResetPasswordService $resetPasswordServicee): Response
    {

        $data = json_decode($request->getContent(), true);


        $email = $data['email'];
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {

            $this->setMessage("Cette ressource est inexsitante");
            $this->setStatusCode(300);
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }

        // Generate a reset token and send the email
        $resetPasswordServicee->sendResetPasswordEmailAdmin($user);

        return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
    }

   

    #[Route('/change/new/access', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de civilite",
        description: "Permet de créer un civilite.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'resetpassword')]
    
    public function resetNewAccess(Request $request, UserRepository $userRepository, ResetPasswordService $resetPasswordServicee): Response
    {

        $data = json_decode($request->getContent(), true);


        $email = $data['email'];
        $password = $data['password'];
        $token = $data['token'];
        $user = $userRepository->findOneBy(['email' => $email, 'resetToken' => $token]);

        if (!$user) {

            $this->setMessage("Cette ressource est inexsitante");
            $this->setStatusCode(300);
            return $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
        }

        $user->setPassword($this->hasher->hashPassword($user, $password));
        $user->setResetToken("");
        $this->em->persist($user);
        $this->em->flush();
     

        return $this->responseData($user, 'group1', ['Content-Type' => 'application/json']);
    }
}
