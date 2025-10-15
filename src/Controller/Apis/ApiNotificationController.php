<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\NotificationDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/notification')]
class ApiNotificationController extends ApiInterface
{
    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des notifications.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Notification::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'notification')]
    // 
    public function index(NotificationRepository $notificationRepository): Response
    {
        try {

            $notifications = $notificationRepository->findAll();



            $response =  $this->responseData($notifications, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/by/{userId}', methods: ['GET'])]
    /**
     * Retourne la liste des notifications.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Notification::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'notification')]
    // 
    public function indexByUser(NotificationRepository $notificationRepository,$userId): Response
    {
        try {

            $notifications = $notificationRepository->findBy(['user'=>$userId]);

            $response =  $this->responseData($notifications, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/nombre/{userId}', methods: ['GET'])]
    /**
     * Retourne la liste des notifications.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Notification::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'notification')]
    // 
    public function indexNombreNotificationNonlu(NotificationRepository $notificationRepository,$userId): Response
    {
        try {

            $notifications = $notificationRepository->findBy(['user'=>$userId,'isRead'=> 0]);

            $response =  $this->responseData($notifications, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) notification en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) notification en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Notification::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'notification')]
    //
    public function getOne(?Notification $notification)
    {
        try {
            if ($notification) {
                $response = $this->response($notification);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($notification);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) notification.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "user", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'notification')]
    
    public function create(Request $request, NotificationRepository $notificationRepository, UserRepository $userRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $notification = new Notification();
        $notification->setLibelle($data['libelle']);
        $notification->setUser($userRepository->find($data['user']));
        $notification->setRead(false);
        $notification->setCreatedBy($this->getUser());
        $notification->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($notification);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $notificationRepository->add($notification, true);
        }

        return $this->responseData($notification, 'group1', ['Content-Type' => 'application/json']);
    }
    #[Route('/read/{id}',  methods: ['POST'])]
    /**
     * Permet de créer un(e) notification.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'notification')]
    
    public function Read(Request $request, Notification $notification, NotificationRepository $notificationRepository, UserRepository $userRepository): Response
    {
        if ($notification) {
            $notification->setRead(true);
            $notificationRepository->add($notification, true);
        }


        return $this->responseData($notification, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de notification",
        description: "Permet de créer un notification.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "user", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'notification')]
    
    public function update(Request $request, Notification $notification, NotificationRepository $notificationRepository, UserRepository $userRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($notification != null) {

                $notification->setLibelle($data->libelle);
                $notification->setUser($userRepository->find($data->user));
                $notification->setUpdatedBy($this->getUser());
                $notification->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($notification);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $notificationRepository->add($notification, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($notification, 'group1', ['Content-Type' => 'application/json']);
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

    //const TAB_ID = 'parametre-tabs';

    #[Route('/delete/{id}',  methods: ['DELETE'])]
    /**
     * permet de supprimer un(e) notification.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) notification',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Notification::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'notification')]
    //
    public function delete(Request $request, Notification $notification, NotificationRepository $notificationRepository): Response
    {
        try {

            if ($notification != null) {

                $notificationRepository->remove($notification, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($notification);
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

    #[Route('/delete/all',  methods: ['DELETE'])]
    /**
     * Permet de supprimer plusieurs notification.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Notification::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'notification')]
    
    public function deleteAll(Request $request, NotificationRepository $notificationRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $notification = $notificationRepository->find($value['id']);

                if ($notification != null) {
                    $notificationRepository->remove($notification);
                }
            }
            $this->setMessage("Operation effectuées avec success");
            $response = $this->response('[]');
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }
}
