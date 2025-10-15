<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\RacineSequenceDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\RacineSequence;
use App\Repository\RacineSequenceRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/racineSequence')]
class ApiRacineSequenceController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des racineSequence.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: RacineSequence::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'racineSequence')]
    // 
    public function index(RacineSequenceRepository $racineSequenceRepository): Response
    {
        try {

            $racineSequence = $racineSequenceRepository->findAll();

            $context = [AbstractNormalizer::GROUPS => 'group1'];
            $json = $this->serializer->serialize($racineSequence, 'json', $context);

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
     * Affiche un(e) racineSequence en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) racineSequence en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: RacineSequence::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'racineSequence')]
    //
    public function getOne(?RacineSequence $racineSequence)
    {
        try {
            if ($racineSequence) {
                $response = $this->response($racineSequence);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($racineSequence);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) racineSequence.
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
    #[OA\Tag(name: 'racineSequence')]
    
    public function create(Request $request, RacineSequenceRepository $racineSequenceRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $racineSequence = new RacineSequence();
        $racineSequence->setCode($data['libelle']);
        $racineSequence->setCreatedBy($this->getUser());
        $racineSequence->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($racineSequence);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $racineSequenceRepository->add($racineSequence, true);
        }

        return $this->responseData($racineSequence, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de racineSequence",
        description: "Permet de créer un racineSequence.",
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
    #[OA\Tag(name: 'racineSequence')]
    
    public function update(Request $request, RacineSequence $racineSequence, RacineSequenceRepository $racineSequenceRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($racineSequence != null) {

                $racineSequence->setCode($data->libelle);
                $racineSequence->setUpdatedBy($this->getUser());
                $racineSequence->setUpdatedAt(new \DateTime());

                $errorResponse = $this->errorResponse($racineSequence);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $racineSequenceRepository->add($racineSequence, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($racineSequence, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) racineSequence.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) racineSequence',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: RacineSequence::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'racineSequence')]
    //
    public function delete(Request $request, RacineSequence $racineSequence, RacineSequenceRepository $villeRepository): Response
    {
        try {

            if ($racineSequence != null) {

                $villeRepository->remove($racineSequence, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($racineSequence);
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
     * Permet de supprimer plusieurs racineSequence.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: RacineSequence::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'racineSequence')]
    
    public function deleteAll(Request $request, RacineSequenceRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $racineSequence = $villeRepository->find($value['id']);

                if ($racineSequence != null) {
                    $villeRepository->remove($racineSequence);
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
