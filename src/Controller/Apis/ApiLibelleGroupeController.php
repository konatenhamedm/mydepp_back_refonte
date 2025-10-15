<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\LibelleGroupeDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\LibelleGroupe;
use App\Entity\TypePersonne;
use App\Repository\LibelleGroupeRepository;
use App\Repository\TypePersonneRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/libelleGroupe')]
class ApiLibelleGroupeController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des libelleGroupes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: LibelleGroupe::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'libelleGroupe')]
    // 
    public function index(LibelleGroupeRepository $libelleGroupeRepository): Response
    {
        try {

            $libelleGroupes = $libelleGroupeRepository->findAll();

            $context = [AbstractNormalizer::GROUPS => 'group1'];
            $json = $this->serializer->serialize($libelleGroupes, 'json', $context);

            return new JsonResponse(['code' => 200, 'data' => json_decode($json)]);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

      #[Route('/all/{code}', methods: ['GET'])]
    /**
     * Retourne la liste des typeDocuments pour l'accord de principe.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: LibelleGroupe::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'libelleGroupe')]
    // 
    public function indexByLibelle(LibelleGroupeRepository $libelleGroupeRepository,TypePersonneRepository $typePersonneRepository,$code): Response
    {
        try {

            $libelleGroupe = $libelleGroupeRepository->findAllByLibelleGroupe($typePersonneRepository->findOneByCode($code)->getId());
            
            $response =  $this->responseData($libelleGroupe, 'group_libelle', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
      #[Route('/all/oep/{id}', methods: ['GET'])]
    /**
     * Retourne la liste des typeDocuments pour l'exploitation.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: LibelleGroupe::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'libelleGroupe')]
    // 
    public function indexByLibelleOep(LibelleGroupeRepository $libelleGroupeRepository,TypePersonne $typePersonne): Response
    {
        try {

            $libelleGroupe = $libelleGroupeRepository->findAllByLibelleGroupeOep($typePersonne->getId());
            
            $response =  $this->responseData($libelleGroupe, 'group_libelle', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) libelleGroupe en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) libelleGroupe en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: LibelleGroupe::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'libelle',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'libelleGroupe')]
    //
    public function getOne(?LibelleGroupe $libelleGroupe)
    {
        try {
            if ($libelleGroupe) {
                $response = $this->response($libelleGroupe);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($libelleGroupe);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) libelleGroupe.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "type", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'libelleGroupe')]
    
    public function create(Request $request, LibelleGroupeRepository $libelleGroupeRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $libelleGroupe = new LibelleGroupe();
        $libelleGroupe->setLibelle($data['libelle']);
        $libelleGroupe->setType($data['type']);
        $libelleGroupe->setCreatedBy($this->getUser());
        $libelleGroupe->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($libelleGroupe);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $libelleGroupeRepository->add($libelleGroupe, true);
        }

        return $this->responseData($libelleGroupe, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de libelleGroupe",
        description: "Permet de créer un libelleGroupe.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "type", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'libelleGroupe')]
    
    public function update(Request $request, LibelleGroupe $libelleGroupe, LibelleGroupeRepository $libelleGroupeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($libelleGroupe != null) {

                $libelleGroupe->setLibelle($data->libelle);
                $libelleGroupe->setType($data->type);
                $libelleGroupe->setUpdatedBy($this->getUser());
                $libelleGroupe->setUpdatedAt(new \DateTime());

                $errorResponse = $this->errorResponse($libelleGroupe);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $libelleGroupeRepository->add($libelleGroupe, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($libelleGroupe, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) libelleGroupe.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) libelleGroupe',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: LibelleGroupe::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'libelleGroupe')]
    //
    public function delete(Request $request, LibelleGroupe $libelleGroupe, LibelleGroupeRepository $villeRepository): Response
    {
        try {

            if ($libelleGroupe != null) {

                $villeRepository->remove($libelleGroupe, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($libelleGroupe);
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
     * Permet de supprimer plusieurs libelleGroupe.dd
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: LibelleGroupe::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'libelleGroupe')]
    
    public function deleteAll(Request $request, LibelleGroupeRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $libelleGroupe = $villeRepository->find($value['id']);

                if ($libelleGroupe != null) {
                    $villeRepository->remove($libelleGroupe);
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
