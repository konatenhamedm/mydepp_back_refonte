<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\MessageDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/message')]
class ApiMessageController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des messages.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Message::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'message')]
    // 
    public function index(MessageRepository $messageRepository): Response
    {
        try {

            $messages = $messageRepository->findAll();

            $context = [AbstractNormalizer::GROUPS => 'group1'];
            $json = $this->serializer->serialize($messages, 'json', $context);

            return new JsonResponse(['code' => 200, 'data' => json_decode($json)]);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) message en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) message en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Message::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'message')]
    //
    public function getOne(?Message $message)
    {
        try {
            if ($message) {
                $response = $this->response($message);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($message);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }



    #[Route('/get/all/{sender}/{receiver}', methods: ['GET'])]
    /**
     * Affiche la conversation entre deux utilisateurs.
     */
    #[OA\Get(
        path: "/get/one/{sender}/{receiver}",
        summary: "Affiche la conversation entre deux utilisateurs",
        parameters: [
            new OA\Parameter(
                name: "sender",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string"),
                description: "L'expéditeur de la conversation"
            ),
            new OA\Parameter(
                name: "receiver",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string"),
                description: "Le destinataire de la conversation"
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Affiche la conversation entre deux utilisateurs",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: new Model(type: Message::class, groups: ['full']))
                )
            )
        ]
    )]
    #[OA\Tag(name: 'message')]
    public function listeConversationParReceiver(
        string $sender,
        string $receiver,
        MessageRepository $messageRepository
    ): Response {
        try {
            $messages = $messageRepository->findConversation($sender, $receiver);
/* dd($messages); */
            if (empty($messages)) {

                $this->setMessage("Aucun message trouvé entre ces utilisateurs.");
                $this->setStatusCode(200);
                return $this->response('[]');
            }

            $response = $this->responseData($messages, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("Cette ressource est inexsitante");
            $this->setStatusCode(500);
            $response = $this->response('[]');
        }

        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) message.
     */
    #[OA\Post(
        summary: "Creation de message",
        description: "Permet de crtéer un message.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "sender", type: "string"),
                    new OA\Property(property: "receiver", type: "string"),
                    new OA\Property(property: "message", type: "string"),
                    


                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'message')]
    
    public function create(Request $request, MessageRepository $messageRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $message = new Message();
        $message->setSender($this->userRepository->find($data['sender']));
        $message->setReceiver($this->userRepository->find($data['receiver']));
        $message->setMessage($data['message']);
        $message->setCreatedBy($this->userRepository->find($data['sender']));
        $message->setUpdatedBy($this->userRepository->find($data['sender']));
        $errorResponse = $this->errorResponse($message);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $messageRepository->add($message, true);
        }
        return $this->responseData($message, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de message",
        description: "Permet de créer un message.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "sender", type: "string"),
                    new OA\Property(property: "receiver", type: "string"),
                    new OA\Property(property: "message", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'message')]
    
    public function update(Request $request, Message $message, MessageRepository $messageRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($message != null) {

                $message->setSender($this->userRepository->find($data->sender));
                $message->setSender($this->userRepository->find($data->receiver));
                $message->setMessage($data->message);
                $message->setUpdatedBy($this->getUser());
                $message->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($message);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $messageRepository->add($message, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($message, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) message.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) message',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Message::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'message')]
    //
    public function delete(Request $request, Message $message, MessageRepository $villeRepository): Response
    {
        try {

            if ($message != null) {

                $villeRepository->remove($message, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($message);
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
     * Permet de supprimer plusieurs message.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Message::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'message')]
    
    public function deleteAll(Request $request, MessageRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $message = $villeRepository->find($value['id']);

                if ($message != null) {
                    $villeRepository->remove($message);
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
