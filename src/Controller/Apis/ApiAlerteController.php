<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\AlerteDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Alerte;
use App\Entity\Transaction;
use App\Repository\AlerteRepository;
use App\Repository\DestinateurRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/alerte')]
class ApiAlerteController extends ApiInterface
{


   

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des alertes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Alerte::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'alerte')]
    // 
    public function index(AlerteRepository $alerteRepository): Response
    {
        try {

            $alertes = $alerteRepository->findAll();

            $context = [AbstractNormalizer::GROUPS => 'group1'];
            $json = $this->serializer->serialize($alertes, 'json', $context);

            return new JsonResponse(['code' => 200, 'data' => json_decode($json)]);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/get/all/{type}', methods: ['GET'])]
    /**
     * Retourne la liste des alertes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Alerte::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'alerte')]
    // 
    public function indexAlerteByType(AlerteRepository $alerteRepository, string $type): Response
    {
        try {

            $alertes = $alerteRepository->getAllAlerteByTypeUser($type);

            $context = [AbstractNormalizer::GROUPS => 'group1'];
            $json = $this->serializer->serialize($alertes, 'json', $context);

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
     * Affiche un(e) alerte en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) alerte en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Alerte::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'alerte')]
    //
    public function getOne(?Alerte $alerte)
    {
        try {
            if ($alerte) {
                $response = $this->response($alerte);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($alerte);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }

   


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) alerte.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "user", type: "string"),
                    new OA\Property(property: "destinateur", type: "string"),
                    new OA\Property(property: "message", type: "string"),
                    new OA\Property(property: "objet", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'alerte')]
    
    public function create(Request $request, AlerteRepository $alerteRepository,DestinateurRepository $destinateurRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $alerte = new Alerte();
        $alerte->setDestinateur($destinateurRepository->find($data['destinateur']));
        $alerte->setUser($this->userRepository->find($data['user']));
        $alerte->setMessage($data['message']);
        $alerte->setObjet($data['objet']);
        $alerte->setCreatedBy($this->getUser());
        $alerte->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($alerte);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $alerteRepository->add($alerte, true);
        }

        return $this->responseData($alerte, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de alerte",
        description: "Permet de créer un alerte.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "user", type: "string"),
                    new OA\Property(property: "destinateur", type: "string"),
                    new OA\Property(property: "message", type: "string"),
                    new OA\Property(property: "objet", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'alerte')]
    
    public function update(Request $request, Alerte $alerte, AlerteRepository $alerteRepository,DestinateurRepository $destinateurRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($alerte != null) {

                $alerte->setDestinateur($destinateurRepository->find($data->destinateur));
                $alerte->setUser($this->userRepository->find($data->user));
                $alerte->setMessage($data->message);
                $alerte->setObjet($data->objet);
                $alerte->setUpdatedBy($this->getUser());
                $alerte->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($alerte);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $alerteRepository->add($alerte, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($alerte, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) alerte.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) alerte',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Alerte::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'alerte')]
    //
    public function delete(Request $request, Alerte $alerte, AlerteRepository $villeRepository): Response
    {
        try {

            if ($alerte != null) {

                $villeRepository->remove($alerte, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($alerte);
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
     * Permet de supprimer plusieurs alerte.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Alerte::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'alerte')]
    
    public function deleteAll(Request $request, AlerteRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $alerte = $villeRepository->find($value['id']);

                if ($alerte != null) {
                    $villeRepository->remove($alerte);
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
