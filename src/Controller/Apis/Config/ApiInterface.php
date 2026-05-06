<?php

namespace App\Controller\Apis\Config;

use App\Controller\FileTrait;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use App\Service\PaiementBusinessLogicService;
use App\Service\PaiementServiceHub2;
use App\Service\SendMailService;
use App\Service\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ApiInterface extends AbstractController
{
    use FileTrait;

    protected const UPLOAD_PATH = 'media_deeps';
    protected $security;
    protected $validator;
    protected $slugger;
    protected $userInterface;
    protected $subscriptionChecker;
    protected  $hasher;
    protected  $userRepository;
    protected  $boutiqueRepository;
    protected  $succursaleRe;
    protected $settingRepository;
    protected  $utils;
    //protected  $utils;
    protected $em;

    protected $client;

    protected $serializer;

    protected $sendMail ;
    protected $superAdmin ;

    public function __construct(
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        protected SendMailService $sendMailService,
        Utils $utils,
        UserPasswordHasherInterface $hasher,
        HttpClientInterface $client,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        protected PaiementServiceHub2 $paiementService,
        protected PaiementBusinessLogicService $businessLogicService,
        protected ParameterBagInterface $params,
        protected PaginationService $paginationService,
       #[Autowire(param: 'SEND_MAIL')] string $sendMail,
        #[Autowire(param: 'SUPER_ADMIN')] string $superAdmin
    ) {

        $this->client = $client;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->utils = $utils;
        $this->hasher = $hasher;
        $this->slugger = $slugger;
        $this->sendMail = $sendMail;
        $this->superAdmin = $superAdmin;

    }

   

    /**
     * @var integer HTTP status code - 200 (OK) by default
     */
    protected $statusCode = 200;
    protected $message = "Operation effectuée avec succes";

    /**
     * Gets the value of statusCode.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the value of statusCode.
     *
     * @param integer $statusCode the status code
     *
     * @return self
     */
    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }
    protected function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function response($data, $headers = [])
    {
        // On spécifie qu'on utilise l'encodeur JSON
        $encoders = [new JsonEncoder()];

        // On instancie le "normaliseur" pour convertir la collection en tableau
        $normalizers = [new ObjectNormalizer()];

        // On instancie le convertisseur
        $serializer = new Serializer($normalizers, $encoders);


        if ($data == null) {
            $arrayData = [
                'data' => '[]',
                'message' => $this->getMessage(),
                'status' => $this->getStatusCode()
            ];
            $response = $this->json([
                'data' => $data,
                'message' => $this->getMessage(),
                'status' => $this->getStatusCode(),
                'errors' => []

            ], 200);
            $response->headers->set('Access-Control-Allow-Origin', '*');
        } else {
            $arrayData = [
                'data' => $data,
                'message' => $this->getMessage(),
                'status' => $this->getStatusCode()
            ];
            $jsonContent = $serializer->serialize($arrayData, 'json', [
                'circular_reference_handler' => function ($object) {
                    return  $object->getId();
                },

            ]);
            // On instancie la réponse
            $response = new Response($jsonContent);
            //$response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }
        // dd($this->json($data));
        // On convertit en json
        // On ajoute l'entête HTTP

        return $response;
        //return new JsonResponse($response, $this->getStatusCode(), $headers);
    }
    public function responseTrue($data, $headers = [])
    {
        // On spécifie qu'on utilise l'encodeur JSON
        $encoders = [new JsonEncoder()];

        // On instancie le "normaliseur" pour convertir la collection en tableau
        $normalizers = [new ObjectNormalizer()];

        // On instancie le convertisseur
        $serializer = new Serializer($normalizers, $encoders);


        if ($data == null) {
            $arrayData = [
                'data' => '[]',
                'message' => $this->getMessage(),
                'status' => $this->getStatusCode()
            ];
            $response = $this->json([
                'data' => $data,
                'message' => $this->getMessage(),
                'status' => $this->getStatusCode(),
                'errors' => []

            ], 200);
            $response->headers->set('Access-Control-Allow-Origin', '*');
        } else {
            $arrayData = [
                'data' => $data,
                'message' => $this->getMessage(),
                'status' => $this->getStatusCode(),
                'errors' => []
            ];
            $jsonContent = $serializer->serialize($arrayData, 'json', [
                'circular_reference_handler' => function ($object) {
                    return  $object->getId();
                },

            ]);
            // On instancie la réponse
            $response = new Response($jsonContent);
            //$response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }
        return $response;
    }



    public function responseAdd($data, $headers = [])
    {
        return $this->json([
            'data' => $data,
            'message' => $this->getMessage(),
            'status' => $this->getStatusCode()

        ], 200);
    }

    /*  public function responseData($data = [], $group = null, $headers = [])
    {
        try {

            $finalHeaders = empty($headers) ? ['Content-Type' => 'application/json'] : $headers;
            if ($data) {
                $context = [AbstractNormalizer::GROUPS => $group];
                $json = $this->serializer->serialize($data, 'json', $context);
                $response = new JsonResponse([
                    'code' => 200,
                    'message' => $this->getMessage(),
                    'data' => json_decode($json),
                    'errors' => []
                ], 200, $finalHeaders);
                $response->headers->set('Access-Control-Allow-Origin', '*');
            } else {
                $response = new JsonResponse([
                    'code' => 200,
                    'message' => $this->getMessage(),
                    'data' => [],
                    'errors' => []
                ], 200, $finalHeaders);
                $response->headers->set('Access-Control-Allow-Origin', '*');
            }
        } catch (\Exception $e) {
            $response = new JsonResponse([
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => []
            ], 500, $finalHeaders);
        }

        return $response;
    } */

    public function responseData(
        $data = [],
        $group = null,
        $headers = [],
        bool $paginate = false
    ): JsonResponse {
        try {
            $finalHeaders = empty($headers) ? ['Content-Type' => 'application/json'] : $headers;

            $request = $this->paginationService->getRequest();
            $search = $request ? ($request->query->get('search') ?? $request->query->get('q') ?? $request->query->get('searchTerm')) : null;

            if ($search) {
                if (is_array($data)) {
                    $data = array_values(array_filter($data, function ($item) use ($search) {
                        $fields = ['libelle', 'code', 'nom', 'prenoms', 'email', 'message', 'title', 'titre', 'text', 'name'];
                        if (is_array($item)) {
                            foreach ($fields as $field) {
                                if (isset($item[$field]) && $item[$field] !== null && stripos((string)$item[$field], $search) !== false) {
                                    return true;
                                }
                            }
                            if (isset($item['personne']) && is_array($item['personne'])) {
                                foreach ($fields as $field) {
                                    if (isset($item['personne'][$field]) && $item['personne'][$field] !== null && stripos((string)$item['personne'][$field], $search) !== false) {
                                        return true;
                                    }
                                }
                            }
                        } elseif (is_object($item)) {
                            foreach ($fields as $field) {
                                $getter = 'get' . ucfirst($field);
                                if (method_exists($item, $getter)) {
                                    $val = $item->$getter();
                                    if ($val !== null && stripos((string)$val, $search) !== false) {
                                        return true;
                                    }
                                }
                            }
                        }
                        return false;
                    }));
                } elseif ($data instanceof \Doctrine\ORM\QueryBuilder) {
                    $aliases = $data->getRootAliases();
                    if (!empty($aliases)) {
                        $alias = $aliases[0];
                        $metadata = $data->getEntityManager()->getClassMetadata($data->getRootEntities()[0]);
                        $orX = $data->expr()->orX();
                        $hasSearchable = false;
                        $fields = ['libelle', 'code', 'nom', 'prenoms', 'email', 'message', 'title', 'titre', 'text', 'name'];
                        foreach ($fields as $field) {
                            if ($metadata->hasField($field)) {
                                $orX->add($data->expr()->like($alias . '.' . $field, ':search'));
                                $hasSearchable = true;
                            }
                        }
                        if ($hasSearchable) {
                            $data->andWhere($orX)->setParameter('search', '%' . $search . '%');
                        }
                    }
                }
            }

            $queryParams = $request ? $request->query->all() : [];
            unset($queryParams['with_pagination'], $queryParams['page'], $queryParams['limit'], $queryParams['search'], $queryParams['q'], $queryParams['searchTerm']);

            if (!empty($queryParams)) {
                if (is_array($data)) {
                    $data = array_values(array_filter($data, function ($item) use ($queryParams) {
                        foreach ($queryParams as $key => $value) {
                            if ($value === null || $value === '') {
                                continue;
                            }
                            $resolveNested = function ($array, $path) {
                                $keys = explode('.', $path);
                                foreach ($keys as $k) {
                                    if (is_array($array) && isset($array[$k])) {
                                        $array = $array[$k];
                                    } else {
                                        return null;
                                    }
                                }
                                return $array;
                            };

                            if (is_array($item)) {
                                $val = $resolveNested($item, $key);
                                if ($val === null && isset($item['personne'])) {
                                    $val = $resolveNested($item['personne'], $key);
                                }
                                if ($val === null) {
                                    // Fallback for flat keys
                                    if (isset($item[$key])) {
                                        $val = $item[$key];
                                    } elseif (isset($item['personne']) && is_array($item['personne']) && isset($item['personne'][$key])) {
                                        $val = $item['personne'][$key];
                                    }
                                }
                                if ($val !== null) {
                                    if ((string)$val !== (string)$value) {
                                        return false;
                                    }
                                } else {
                                    return false;
                                }
                            } elseif (is_object($item)) {
                                $getter = 'get' . ucfirst($key);
                                if (method_exists($item, $getter)) {
                                    $val = $item->$getter();
                                    if (is_object($val)) {
                                        if (method_exists($val, 'getId')) {
                                            if ((string)$val->getId() !== (string)$value) {
                                                return false;
                                            }
                                        } else {
                                            return false;
                                        }
                                    } else {
                                        if ((string)$val !== (string)$value) {
                                            return false;
                                        }
                                    }
                                } else {
                                    return false;
                                }
                            } else {
                                return false;
                             }
                        }
                        return true;
                    }));
                } elseif ($data instanceof \Doctrine\ORM\QueryBuilder) {
                    $aliases = $data->getRootAliases();
                    if (!empty($aliases)) {
                        $alias = $aliases[0];
                        $metadata = $data->getEntityManager()->getClassMetadata($data->getRootEntities()[0]);
                        foreach ($queryParams as $key => $value) {
                            if ($value === null || $value === '') {
                                continue;
                            }
                            if ($metadata->hasField($key)) {
                                $paramName = 'filter_' . $key;
                                $data->andWhere($alias . '.' . $key . ' = :' . $paramName)
                                     ->setParameter($paramName, $value);
                            } elseif ($metadata->hasAssociation($key)) {
                                $paramName = 'filter_' . $key;
                                $data->andWhere($alias . '.' . $key . ' = :' . $paramName)
                                     ->setParameter($paramName, $value);
                            }
                        }
                    }
                }
            }

            $withPagination = $request ? $request->query->get('with_pagination') === 'true' : false;

            if ($withPagination && !$data instanceof PaginationInterface && $data !== null) {
                $data = $this->paginationService->paginate($data);
                $paginate = true;
            }

            $tabCounts = null;
            if ($request && strpos($request->getPathInfo(), 'professionnel') !== false) {
                try {
                    $countsResult = $this->em->createQueryBuilder()
                        ->select('p.status, COUNT(u.id) as count')
                        ->from(\App\Entity\User::class, 'u')
                        ->innerJoin('u.personne', 'p')
                        ->andWhere('u.typeUser = :typeUser')
                        ->setParameter('typeUser', 'PROFESSIONNEL')
                        ->groupBy('p.status')
                        ->getQuery()
                        ->getResult();

                    $tabCounts = [];
                    foreach ($countsResult as $row) {
                        if (isset($row['status']) && $row['status']) {
                            $tabCounts[$row['status']] = (int) $row['count'];
                        }
                    }
                } catch (\Exception $e) {
                    // Fail gracefully if schema differs
                }
            } elseif ($request && strpos($request->getPathInfo(), 'etablissement') !== false) {
                try {
                    $countsResult = $this->em->createQueryBuilder()
                        ->select('p.status, COUNT(u.id) as count')
                        ->from(\App\Entity\User::class, 'u')
                        ->innerJoin('u.personne', 'p')
                        ->andWhere('u.typeUser = :typeUser')
                        ->setParameter('typeUser', 'ETABLISSEMENT')
                        ->groupBy('p.status')
                        ->getQuery()
                        ->getResult();

                    $tabCounts = [];
                    foreach ($countsResult as $row) {
                        if (isset($row['status']) && $row['status']) {
                            $tabCounts[$row['status']] = (int) $row['count'];
                        }
                    }
                } catch (\Exception $e) {
                    // Fail gracefully if schema differs
                }
            }

            $context = [AbstractNormalizer::GROUPS => $group];

            if ($paginate && $data instanceof PaginationInterface) {
                $items = $this->serializer->serialize($data->getItems(), 'json', $context);

                $responseData = [
                    'code' => 200,
                    'message' => $this->getMessage(),
                    'data' => json_decode($items),
                    'pagination' => [
                        'currentPage' => $data->getCurrentPageNumber(),
                        'totalItems'  => $data->getTotalItemCount(),
                        'itemsPerPage' => $data->getItemNumberPerPage(),
                        'totalPages'  => ceil($data->getTotalItemCount() / $data->getItemNumberPerPage())
                    ],
                    'errors' => []
                ];
                if ($tabCounts !== null) {
                    $responseData['tabCounts'] = $tabCounts;
                }
                $response = new JsonResponse($responseData, 200, $finalHeaders);
            } else {
                $json = $this->serializer->serialize($data, 'json', $context);

                $responseData = [
                    'code' => 200,
                    'message' => $this->getMessage(),
                    'data' => json_decode($json),
                    'errors' => []
                ];
                if ($tabCounts !== null) {
                    $responseData['tabCounts'] = $tabCounts;
                }
                $response = new JsonResponse($responseData, 200, $finalHeaders);
            }

            $response->headers->set('Access-Control-Allow-Origin', '*');
        } catch (\Exception $e) {
            $response = new JsonResponse([
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => []
            ], 500, $finalHeaders);
        }

        return $response;
    }

    public function responseDataWith_($data = [], $group = null, $headers = [])
    {
        try {

            $finalHeaders = empty($headers) ? ['Content-Type' => 'application/json'] : $headers;
            if ($data) {
                $context = [AbstractNormalizer::GROUPS => $group];
                $json = $this->serializer->serialize($data['data'], 'json', $context);
                $response = new JsonResponse([
                    'code' => 200,
                    'message' => $this->getMessage(),
                    'data' => json_decode($json),
                    'errors' => []
                ], 200, $finalHeaders);
                $response->headers->set('Access-Control-Allow-Origin', '*');
            } else {
                $response = new JsonResponse([
                    'code' => 200,
                    'message' => $this->getMessage(),
                    'data' => [],
                    'errors' => []
                ], 200, $finalHeaders);
                $response->headers->set('Access-Control-Allow-Origin', '*');
            }
        } catch (\Exception $e) {
            $response = new JsonResponse([
                'code' => 500,
                'message' => $e->getMessage(),
                'data' => []
            ], 500, $finalHeaders);
        }

        return $response;
    }

    public function errorResponse($DTO, string $customMessage = ''): ?JsonResponse
    {
        $errors = $this->validator->validate($DTO);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            //array_push($arerrorMessagesray, 4)

            $response = [
                'code' => 400,
                'message' => 'Validation failed',
                'errors' => $errorMessages
            ];

            return new JsonResponse($response, 400);
        } elseif ($customMessage != '') {
            $errorMessages[] = $customMessage;
            $response = [
                'code' => 400,
                'message' => 'Validation failed',
                'errors' => $errorMessages
            ];

            return new JsonResponse($response, 400);
        }

        return null; // Pas d'erreurs, donc pas de réponse d'erreur
    }
    public function errorResponseWithoutAbonnement(string $customMessage = ''): ?JsonResponse
    {
        $response = [
            'code' => 400,
            'message' => $customMessage,
            'errors' => $customMessage
        ];

        return new JsonResponse($response, 400);
    }

   
}
