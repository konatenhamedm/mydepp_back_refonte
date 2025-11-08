<?php

namespace  App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\DTO\ProfessionDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Profession;
use App\Entity\TypeProfession;
use App\Entity\Ville;
use App\Repository\ProfessionRepository;
use App\Repository\TypeProfessionRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/profession')]
class ApiProfessionController extends ApiInterface
{


  #[Route('/api/montants', name: 'api_montants', methods: ['GET'])]
    /**
   * Affiche un(e) specialite en offrant un identifiant.
   */
  #[OA\Response(
    response: 200,
    description: 'Affiche les montants',
   
  )]

  #[OA\Tag(name: 'profession')]
  //
  public function getMontantsOptions(ProfessionRepository $repo): JsonResponse
  {
    $montants = $repo->findUniqueMontants();

    $options = [
      ['value' => '', 'label' => 'Tous les montants'],
    ];

    foreach ($montants as $montant) {
      $options[] = [
        'value' => $montant ? (string) $montant : "0",
        'label' => number_format((float)$montant, 0, '', ' ') . ' FCFA',
      ];
    }

    return $this->json(["data"=> $options]);
  }


  #[Route('/get/profession/typeProfession/{typeProfession}', methods: ['GET'])]
  /**
   * Affiche un(e) specialite en offrant un identifiant.
   */
  #[OA\Response(
    response: 200,
    description: 'Affiche etat paiement de la specialite',
    content: new OA\JsonContent(
      type: 'array',
      items: new OA\Items(ref: new Model(type: Profession::class, groups: ['full']))
    )
  )]
  #[OA\Parameter(
    name: 'typeProfession',
    in: 'query',
    schema: new OA\Schema(type: 'string')
  )]
  #[OA\Tag(name: 'profession')]
  //
  public function getProfessionByTypeProfession($typeProfession, ProfessionRepository $professionRepository)
  {
    try {

      $professions = $professionRepository->findBy(['typeProfession' => $typeProfession]);
      $response =  $this->responseData($professions, 'group_autre', ['Content-Type' => 'application/json']);
   
    } catch (\Exception $exception) {
      $this->setMessage($exception->getMessage());
      $response = $this->response('[]');
    }


    return $response;
  }

  #[Route('/get/status/paiement/{code}', methods: ['GET'])]
  /**
   * Affiche un(e) specialite en offrant un identifiant.
   */
  #[OA\Response(
    response: 200,
    description: 'Affiche etat paiement de la specialite',
    content: new OA\JsonContent(
      type: 'array',
      items: new OA\Items(ref: new Model(type: Profession::class, groups: ['full']))
    )
  )]
  #[OA\Parameter(
    name: 'code',
    in: 'query',
    schema: new OA\Schema(type: 'string')
  )]
  #[OA\Tag(name: 'profession')]
  //
  public function getPaiementStatus($code, ProfessionRepository $professionRepository)
  {
    try {

      $profession = $professionRepository->findOneBy(['code' => $code]);
      if ($profession) {
        $response = $this->response($profession->getMontantNouvelleDemande() != null || $profession->getMontantNouvelleDemande() != 0 ? true : false);
      } else {
        $this->setMessage('Cette ressource est inexistante');
        $this->setStatusCode(300);
        $response = $this->response(false);
      }
    } catch (\Exception $exception) {
      $this->setMessage($exception->getMessage());
      $response = $this->response('[]');
    }


    return $response;
  }

  #[Route('/get/by/code/{code}', methods: ['GET'])]
  /**
   * Affiche un(e) specialite en offrant un identifiant.
   */
  #[OA\Response(
    response: 200,
    description: 'Affiche etat paiement de la specialite',
    content: new OA\JsonContent(
      type: 'array',
      items: new OA\Items(ref: new Model(type: Profession::class, groups: ['full']))
    )
  )]
  #[OA\Parameter(
    name: 'code',
    in: 'query',
    schema: new OA\Schema(type: 'string')
  )]
  #[OA\Tag(name: 'profession')]
  //
  public function getByCodes($code, ProfessionRepository $professionRepository)
  {
    try {

      $profession = $professionRepository->findOneBy(['code' => $code]);
      if ($profession) {
        $response = $this->response($profession->getLibelle());
      } else {
        $this->setMessage('Cette ressource est inexistante');
        $this->setStatusCode(300);
        $response = $this->response('');
      }
    } catch (\Exception $exception) {
      $this->setMessage($exception->getMessage());
      $response = $this->response('[]');
    }


    return $response;
  }




  #[Route('/', methods: ['GET'])]
  /**
   * Retourne la liste des professions.
   * 
   */
  #[OA\Response(
    response: 200,
    description: 'Returns the rewards of an user',
    content: new OA\JsonContent(
      type: 'array',
      items: new OA\Items(ref: new Model(type: Profession::class, groups: ['full']))
    )
  )]
  #[OA\Tag(name: 'profession')]
  // 
  public function index(ProfessionRepository $professionRepository): Response
  {
    try {

      $professions = $professionRepository->findAll();



      $response =  $this->responseData($professions, 'group1', ['Content-Type' => 'application/json']);
    } catch (\Exception $exception) {
      $this->setMessage("");
      $response = $this->response('[]');
    }

    // On envoie la réponse
    return $response;
  }


  #[Route('/get/one/{id}', methods: ['GET'])]
  /**
   * Affiche un(e) profession en offrant un identifiant.
   */
  #[OA\Response(
    response: 200,
    description: 'Affiche un(e) profession en offrant un identifiant',
    content: new OA\JsonContent(
      type: 'array',
      items: new OA\Items(ref: new Model(type: Profession::class, groups: ['full']))
    )
  )]
  #[OA\Parameter(
    name: 'code',
    in: 'query',
    schema: new OA\Schema(type: 'string')
  )]
  #[OA\Tag(name: 'profession')]
  //
  public function getOne(?Profession $profession)
  {
    try {
      if ($profession) {
        $response = $this->response($profession);
      } else {
        $this->setMessage('Cette ressource est inexistante');
        $this->setStatusCode(300);
        $response = $this->response($profession);
      }
    } catch (\Exception $exception) {
      $this->setMessage($exception->getMessage());
      $response = $this->response('[]');
    }


    return $response;
  }


  #[Route('/create',  methods: ['POST'])]
  /**
   * Permet de créer un(e) profession.
   */
  #[OA\Post(
    summary: "Authentification admin",
    description: "Génère un token JWT pour les administrateurs.",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        properties: [
          new OA\Property(property: "libelle", type: "string"),
          new OA\Property(property: "typeProfession", type: "string"),
          new OA\Property(property: "montantNouvelleDemande", type: "string"),
          new OA\Property(property: "montantRenouvellement", type: "string"),
          
          new OA\Property(property: "code", type: "string"),
          new OA\Property(property: "chronoMax", type: "string"),
          new OA\Property(property: "codeGeneration", type: "string"),

        ],
        type: "object"
      )
    ),
    responses: [
      new OA\Response(response: 401, description: "Invalid credentials")
    ]
  )]
  #[OA\Tag(name: 'profession')]
  
  public function create(Request $request, ProfessionRepository $professionRepository, TypeProfessionRepository $typeProfessionRepository): Response
  {


    $data = json_decode($request->getContent(), true);

    $profession = new Profession();
    $profession->setLibelle($data['libelle']);
    $profession->setChronoMax($data['chronoMax']);
    $profession->setMaxCode($data['chronoMax']);
    $profession->setCodeGeneration($data['codeGeneration']);
    $profession->setCreatedAtValue(new \DateTime());
    $profession->setUpdatedAt(new \DateTime());
    $profession->setTypeProfession($typeProfessionRepository->find($data['typeProfession']));
    $profession->setMontantNouvelleDemande($data['montantNouvelleDemande']);
    $profession->setMontantRenouvellement($data['montantRenouvellement']);
    $profession->setCode($typeProfessionRepository->find($data['typeProfession'])->getCode() . '_' . $data['libelle']);
    $profession->setCreatedBy($this->getUser());
    $profession->setUpdatedBy($this->getUser());
    $errorResponse = $this->errorResponse($profession);
    if ($errorResponse !== null) {
      return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
    } else {

      $professionRepository->add($profession, true);
    }

    return $this->responseData($profession, 'group1', ['Content-Type' => 'application/json']);
  }


  #[Route('/update/{id}', methods: ['PUT', 'POST'])]
  #[OA\Post(
    summary: "Creation de profession",
    description: "Permet de créer un profession.",
    requestBody: new OA\RequestBody(
      required: true,
      content: new OA\JsonContent(
        properties: [
          new OA\Property(property: "libelle", type: "string"),
          new OA\Property(property: "typeProfession", type: "string"),
          
          new OA\Property(property: "montantNouvelleDemande", type: "string"),
          new OA\Property(property: "montantRenouvellement", type: "string"),
          new OA\Property(property: "codeGeneration", type: "string"),
          new OA\Property(property: "chronoMax", type: "string"),
          new OA\Property(property: "code", type: "string"),
        ],
        type: "object"
      )
    ),
    responses: [
      new OA\Response(response: 401, description: "Invalid credentials")
    ]
  )]
  #[OA\Tag(name: 'profession')]
  
  public function update(Request $request, Profession $profession, ProfessionRepository $professionRepository, TypeProfessionRepository $typeProfessionRepository): Response
  {
    try {
      $data = json_decode($request->getContent());
      if ($profession != null) {

        $profession->setLibelle($data->libelle);
        $profession->setChronoMax($data->chronoMax);
        $profession->setMaxCode($profession->getMaxCode() == null ? $data->chronoMax : $profession->getMaxCode());
        $profession->setCodeGeneration($data->codeGeneration);
        $profession->setTypeProfession($typeProfessionRepository->find($data->typeProfession));
        $profession->setMontantNouvelleDemande($data->montantNouvelleDemande);
        $profession->setMontantRenouvellement($data->montantRenouvellement);
        $profession->setCode($typeProfessionRepository->find($data->typeProfession)->getCode() . '_' . $data->libelle);
        $profession->setUpdatedBy($this->getUser());
        $profession->setUpdatedAt(new \DateTime());
        $errorResponse = $this->errorResponse($profession);

        if ($errorResponse !== null) {
          return $errorResponse; // Retourne la réponse d'erreur si des erreurs sont présentes
        } else {
          $professionRepository->add($profession, true);
        }

        // On retourne la confirmation
        $response = $this->responseData($profession, 'group1', ['Content-Type' => 'application/json']);
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
   * permet de supprimer un(e) profession.
   */
  #[OA\Response(
    response: 200,
    description: 'permet de supprimer un(e) profession',
    content: new OA\JsonContent(
      type: 'array',
      items: new OA\Items(ref: new Model(type: Profession::class, groups: ['full']))
    )
  )]
  #[OA\Tag(name: 'profession')]
  //
  public function delete(Request $request, Profession $profession, ProfessionRepository $villeRepository): Response
  {
    try {

      if ($profession != null) {

        $villeRepository->remove($profession, true);

        // On retourne la confirmation
        $this->setMessage("Operation effectuées avec success");
        $response = $this->response($profession);
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
   * Permet de supprimer plusieurs profession.
   */
  #[OA\Response(
    response: 200,
    description: 'Returns the rewards of an user',
    content: new OA\JsonContent(
      type: 'array',
      items: new OA\Items(ref: new Model(type: Profession::class, groups: ['full']))
    )
  )]
  #[OA\Tag(name: 'profession')]
  
  public function deleteAll(Request $request, ProfessionRepository $villeRepository): Response
  {
    try {
      $data = json_decode($request->getContent());

      foreach ($data->ids as $key => $value) {
        $profession = $villeRepository->find($value['id']);

        if ($profession != null) {
          $villeRepository->remove($profession);
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
