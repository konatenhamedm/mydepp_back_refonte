<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\TypeDocumentDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\TypeDocument;
use App\Entity\TypePersonne;
use App\Repository\LibelleGroupeRepository;
use App\Repository\TypeDocumentRepository;
use App\Repository\TypePersonneRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/typeDocument')]
class ApiTypeDocumentController extends ApiInterface
{

    #[Route('/api/type-documents/{typePersonneId}', name: 'get_type_documents_by_type_personne', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeDocument::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeDocument')]
    public function getByTypePersonne(
        int $typePersonneId,
        TypeDocumentRepository $typeDocumentRepository
    ): JsonResponse {
        $documents = $typeDocumentRepository->findByTypePersonneGrouped($typePersonneId);

        $grouped = [];
        foreach ($documents as $doc) {
            $groupe = $doc->getLibelleGroupe()->getLibelle();
            $grouped[$groupe][] = [
                'id' => $doc->getId(),
                'libelle' => $doc->getLibelle(),
            ];
        }

        return $this->json($grouped);
    }

    #[Route('/', methods: ['GET'])]
    /**
     * Retourne la liste des typeDocuments.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeDocument::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeDocument')]
    // 
    public function index(TypeDocumentRepository $typeDocumentRepository): Response
    {
        try {
            $typeDocuments = $typeDocumentRepository->findAll();
            $response =  $this->responseData($typeDocuments, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/all', methods: ['GET'])]
    /**
     * Retourne la liste des typeDocuments.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeDocument::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeDocument')]
    // 
    public function indexByLibelle(TypeDocumentRepository $typeDocumentRepository): Response
    {
        try {

            $typeDocuments = $typeDocumentRepository->findAllByLibelleGroupe();

            $response =  $this->responseData($typeDocuments, 'group1', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/get/one/{id}', methods: ['GET'])]
    /**
     * Affiche un(e) typeDocument en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) typeDocument en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeDocument::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'typeDocument')]
    //
    public function getOne(?TypeDocument $typeDocument)
    {
        try {
            if ($typeDocument) {
                $response = $this->response($typeDocument);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($typeDocument);
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }

    #[Route('/get/liste/doc/{idTypePersonne}', methods: ['GET'])]
    /**
     * Affiche un(e) typeDocument en offrant un identifiant.
     */
    #[OA\Response(
        response: 200,
        description: 'Affiche un(e) typeDocument en offrant un identifiant',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeDocument::class, groups: ['full']))
        )
    )]
    #[OA\Parameter(
        name: 'code',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'typeDocument')]
    //
    public function getDocByTypePersonne(?TypePersonne $typePersonne, TypeDocumentRepository $typeDocumentRepository)
    {
        try {
            if ($typePersonne) {

                $dataTypeDocuments = $typeDocumentRepository->findBy(['typePersonne' => $typePersonne]);


                $response =  $this->responseData($dataTypeDocuments, 'group1', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response('[]');
            }
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }


        return $response;
    }


    #[Route('/create',  methods: ['POST'])]
    /**
     * Permet de créer un(e) typeDocument.
     */
    #[OA\Post(
        summary: "Authentification admin",
        description: "Génère un token JWT pour les administrateurs.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "typePersonne", type: "string"),
                    new OA\Property(property: "nombre", type: "string"),
                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "libelleGroupe", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'typeDocument')]
    
    public function create(Request $request, LibelleGroupeRepository $libelleGroupeRepository, TypeDocumentRepository $typeDocumentRepository, TypePersonneRepository $typePersonneRepository): Response
    {

        $data = json_decode($request->getContent(), true);




        $typeDocument = new TypeDocument();

        $typeDocument->setLibelle($data['libelle']);
        $typeDocument->setLibelleGroupe($libelleGroupeRepository->find($data['libelleGroupe']));
        $typeDocument->setNombre($data['nombre']);
        $typeDocument->setTypePersonne($typePersonneRepository->find($data['typePersonne']));
        $typeDocument->setCreatedAtValue(new \DateTime());
        $typeDocument->setUpdatedAt(new \DateTime());
        $typeDocument->setCreatedBy($this->getUser());
        $typeDocument->setUpdatedBy($this->getUser());

        $errorResponse = $this->errorResponse($typeDocument);
        if ($errorResponse !== null) {
            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {

            $typeDocumentRepository->add($typeDocument, true);
        }



        $this->setMessage("Cette ressource est inexsitante");
        $this->setStatusCode(300);
        $response = $this->response('[]');

        return $response;
    }


    #[Route('/update/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de typeDocument",
        description: "Permet de créer un typeDocument.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [

                    new OA\Property(property: "libelle", type: "string"),
                    new OA\Property(property: "nombre", type: "string"),
                    new OA\Property(property: "libelleGroupe", type: "string"),
                    new OA\Property(property: "typePersonne", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'typeDocument')]
    
    public function update(Request $request, $id, LibelleGroupeRepository $libelleGroupeRepository, TypeDocumentRepository $typeDocumentRepository, TypePersonneRepository $typePersonneRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            //return $data;

            $typeDocument = $typeDocumentRepository->find($id);

            if ($typeDocument != null) {

                $typeDocument->setLibelle($data->libelle);
                $typeDocument->setNombre($data->nombre);
                $typeDocument->setLibelleGroupe($libelleGroupeRepository->find($data->libelleGroupe));
                $typeDocument->setTypePersonne($typePersonneRepository->find($data->typePersonne));
                $typeDocument->setUpdatedAt(new \DateTime());
                $typeDocument->setUpdatedBy($this->getUser());
                $errorResponse = $this->errorResponse($typeDocument);


                if ($errorResponse !== null) {
                    return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                } else {

                    $typeDocumentRepository->add($typeDocument, true);
                }

                $response = $this->responseData($typeDocument, 'group_', ['Content-Type' => 'application/json']);
            } else {
                $this->setMessage("Cette ressource est inexsitante");
                $this->setStatusCode(300);
                $response = $this->response('[]');
            }
            // On retourne la confirmation

        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }
        return $response;
    }
    #[Route('/update/multiple/{id}', methods: ['PUT', 'POST'])]
    #[OA\Post(
        summary: "Creation de typeDocument",
        description: "Permet de créer un typeDocument.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [

                    new OA\Property(property: "dataDocument", type: "string"),
                    new OA\Property(property: "dataDelete", type: "string"),
                    

                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    #[OA\Tag(name: 'typeDocument')]
    
    public function updateMultiple(Request $request, TypePersonne $typePersonne, LibelleGroupeRepository $libelleGroupeRepository, TypeDocumentRepository $typeDocumentRepository, TypePersonneRepository $typePersonneRepository): Response
    {
        try {
            $data = json_decode($request->getContent());
            if ($typePersonne != null) {

                $datasDocuments = $data->dataDocument;
                $datasDelete = $data->dataDelete;

                foreach ($datasDocuments as $key => $document) {


                    if ($document['id'] != null) {

                        $typeDocument =  $typeDocumentRepository->find($document['id']);
                        $typeDocument->setLibelle($document['libelle']);
                        $typeDocument->setLibelleGroupe($libelleGroupeRepository->find($data['libelleGroupe']));
                        $typeDocument->setNombre($document['nombre']);
                        $typeDocument->setUpdatedAt(new \DateTime());
                        $typeDocument->setUpdatedBy($this->getUser());
                        $errorResponse = $this->errorResponse($typeDocument);
                        if ($errorResponse !== null) {
                            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                        } else {

                            $typeDocumentRepository->add($typeDocument, true);
                        }
                    } else {

                        $typeDocument = new TypeDocument();
                        $typeDocument->setLibelle($document['libelle']);
                        $typeDocument->setLibelleGroupe($libelleGroupeRepository->find($data['libelleGroupe']));
                        $typeDocument->setNombre($document['nombre']);
                        $typeDocument->setTypePersonne($typePersonne);
                        $typeDocument->setCreatedAtValue(new \DateTime());
                        $typeDocument->setUpdatedAt(new \DateTime());
                        $typeDocument->setCreatedBy($this->getUser());
                        $typeDocument->setUpdatedBy($this->getUser());
                        $errorResponse = $this->errorResponse($typeDocument);
                        if ($errorResponse !== null) {
                            return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
                        } else {

                            $typeDocumentRepository->add($typeDocument, true);
                        }
                    }
                }

                foreach ($datasDelete as $key => $value) {
                    $typeDocument = $typeDocumentRepository->find($value['id']);

                    if ($typeDocument != null) {
                        $typeDocumentRepository->remove($typeDocument);
                    }
                }

                // On retourne la confirmation
                $response = $this->responseData([], 'group1', ['Content-Type' => 'application/json']);
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
     * permet de supprimer un(e) typeDocument.
     */
    #[OA\Response(
        response: 200,
        description: 'permet de supprimer un(e) typeDocument',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeDocument::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeDocument')]
    //
    public function delete(Request $request, TypeDocument $typeDocument, TypeDocumentRepository $villeRepository): Response
    {
        try {

            if ($typeDocument != null) {

                $villeRepository->remove($typeDocument, true);

                // On retourne la confirmation
                $this->setMessage("Operation effectuées avec success");
                $response = $this->response($typeDocument);
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
     * Permet de supprimer plusieurs typeDocument.
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: TypeDocument::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'typeDocument')]
    
    public function deleteAll(Request $request, TypeDocumentRepository $villeRepository): Response
    {
        try {
            $data = json_decode($request->getContent());

            foreach ($data->ids as $key => $value) {
                $typeDocument = $villeRepository->find($value['id']);

                if ($typeDocument != null) {
                    $villeRepository->remove($typeDocument);
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
