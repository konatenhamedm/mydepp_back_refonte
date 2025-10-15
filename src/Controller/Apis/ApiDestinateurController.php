<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\DestinateurDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Destinateur;
use App\Repository\DestinateurRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/destinateur')]
class ApiDestinateurController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des destinateurs.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Destinateur::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'destinateur')]
    // 
    public function index(DestinateurRepository $destinateurRepository): Response
    {
        try {

            $destinateurs = $destinateurRepository->findAll();

            $context = [AbstractNormalizer::GROUPS => 'group1'];
            $json = $this->serializer->serialize($destinateurs, 'json', $context);

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
     * Affiche un(e) destinateur en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) destinateur en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Destinateur::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'destinateur')]
    //
    public function getOne(?Destinateur $destinateur)
    {
        try {
            if ($destinateur) {
                $response = $this->response($destinateur);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($destinateur);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) destinateur.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
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
    #[OA\Tag(name: 'destinateur')]
    
    public function create(Request $request, DestinateurRepository $destinateurRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $destinateur = new Destinateur();
        $destinateur->setLibelle($data['libelle']);
        $destinateur->setCreatedBy($this->getUser());
        $destinateur->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($destinateur);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $destinateurRepository->add($destinateur, true);
        }

        return $this->responseData($destinateur, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de destinateur",
        description: "Permet de créer un destinateur.",
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
    #[OA\Tag(name: 'destinateur')]
    
    public function update(Request $request, Destinateur $destinateur, DestinateurRepository $destinateurRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($destinateur != null) {

                $destinateur->setLibelle($data->libelle);
                $destinateur->setUpdatedBy($this->getUser());
                $destinateur->setUpdatedAt(new \DateTime());
                $errorResponse = $this->errorResponse($destinateur);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $destinateurRepository->add($destinateur, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($destinateur, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) destinateur.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) destinateur',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Destinateur::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'destinateur')]
    //
    public function delete(Request $request, Destinateur $destinateur, DestinateurRepository $villeRepository): Response
    {
        try {

            if ($destinateur != null) {

                $villeRepository->remove($destinateur, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($destinateur);
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
     * Permet de supprimer plusieurs destinateur.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Destinateur::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'destinateur')]
    
    public function deleteAll(Request $request, DestinateurRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $destinateur = $villeRepository->find($value['id']);

                if ($destinateur != null) {
                    $villeRepository->remove($destinateur);
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
