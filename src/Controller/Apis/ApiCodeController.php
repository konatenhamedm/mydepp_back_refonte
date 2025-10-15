<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\CodeDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Code;
use App\Repository\CodeRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/code')]
class ApiCodeController extends ApiInterface
{



    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des codes.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Code::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'code')]
    // 
    public function index(CodeRepository $codeRepository): Response
    {
        try {

            $codes = $codeRepository->findAll();

            $context = [AbstractNormalizer::GROUPS => 'group1'];
            $json = $this->serializer->serialize($codes, 'json', $context);

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
     * Affiche un(e) code en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) code en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Code::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'code')]
    //
    public function getOne(?Code $code)
    {
        try {
            if ($code) {
                $response = $this->response($code);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($code);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) code.
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
    #[OA\Tag(name: 'code')]
    
    public function create(Request $request, CodeRepository $codeRepository): Response
    {

        $data = json_decode($request->getContent(), true);
        $code = new Code();

        $code->setCode($data['code']);
        $code->setCreatedBy($this->getUser());
        $code->setUpdatedBy($this->getUser());
        $errorResponse = $this->errorResponse($code);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $codeRepository->add($code, true);
        }

        return $this->responseData($code, 'group1', ['Content-Type' => 'application/json']);
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de code",
        description: "Permet de créer un code.",
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
    #[OA\Tag(name: 'code')]
    
    public function update(Request $request, Code $code, CodeRepository $codeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($code != null) {

                $code->setCode($data['code']);
                $code->setUpdatedBy($this->getUser());
                $code->setUpdatedAt(new \DateTime());

                $errorResponse = $this->errorResponse($code);

                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {
                    $codeRepository->add($code, true);
                }



                // On retourne la confirmation
                $response = $this->responseData($code, 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) code.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) code',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Code::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'code')]
    //
    public function delete(Request $request, Code $code, CodeRepository $villeRepository): Response
    {
        try {

            if ($code != null) {

                $villeRepository->remove($code, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($code);
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
     * Permet de supprimer plusieurs code.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Code::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'code')]
    
    public function deleteAll(Request $request, CodeRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $code = $villeRepository->find($value['id']);

                if ($code != null) {
                    $villeRepository->remove($code);
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
