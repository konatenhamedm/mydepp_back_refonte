<?php

namespace App\Controller;

use App\Controller\Apis\Config\ApiInterface;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Ramsey\Uuid\Uuid;
#[Route('/api/paiement2')]
class PaymentProController extends ApiInterface
{
    private HttpClientInterface $httpClient;
    
    private TransactionRepository $transactionRepository;

    public function __construct(HttpClientInterface $httpClient, UserRepository $userRepository, TransactionRepository $transactionRepository)
    {
        $this->httpClient = $httpClient;
        $this->userRepository = $userRepository;
        $this->transactionRepository = $transactionRepository;
        
    }

    #[Route('/paymentmomo', name: 'api_payment_momo', methods: ['POST', 'OPTIONS'])]
    public function processPayment(Request $request): JsonResponse
    {

        // Gérer les requêtes OPTIONS (CORS preflight)
        if ($request->getMethod() === 'OPTIONS') {
            return $this->createCorsResponse();
        }

        try {
            // Credentials
            $username = '8e10c4a7-bcae-4f64-ba50-7b5cfe338366';
            $password = 'b73936c9c1c449c9b6fcebf12aee00f2';
            $basicToken = base64_encode("$username:$password");
            
            // Récupérer la clé primaire depuis les variables d'environnement
            $momoPrimaryKey = $_ENV['MOMO_PRIMARY_KEY'] ?? '';
            $momoSubscriptionKey = 'f42e9a3ae31842fba6e8c2fea23fa0d7';
            
            // Étape 1: Obtenir le token
            $tokenResponse = $this->httpClient->request('POST', 'https://proxy.momoapi.mtn.com/collection/token/', [
                'headers' => [
                    'Authorization' => "Basic $basicToken",
                    'Cache-Control' => 'no-cache',
                    'Ocp-Apim-Subscription-Key' => $momoSubscriptionKey,
                ],
            ]);

            if ($tokenResponse->getStatusCode() !== 200) {
                return $this->createCorsResponse(
                    ['error' => 'Erreur lors de la récupération du token'],
                    500
                );
            }

            $tokenData = $tokenResponse->toArray();
            $token = $tokenData['access_token'];

            // Récupérer les données du corps de la requête
            $data = json_decode($request->getContent(), true);
            // Si le body JSON est vide, récupérer depuis form-data
            if (!$data || !is_array($data)) {
                $data = [
                    'amount' => $request->get('amount'),
                    'phoneNumber' => $request->get('phoneNumber'),
                    'user' => $request->get('user'),
                    'profession' => $request->get('profession'),
                ];
                dd( $request , $data);
            }

            $amount = $data['amount'] ?? null;
            $phoneNumber = $data['phoneNumber'] ?? null;

            if (!$amount || !$phoneNumber) {
                return $this->createCorsResponse(
                    ['error' => 'Montant et numéro de téléphone requis'],
                    400
                );
            }

            // Générer UUID
            $myUuid = Uuid::uuid4()->toString();

            // Construire le body pour la requête de paiement
            $paymentBody = [
                'amount' => (string) $amount,
                'currency' => 'XOF',
                'externalId' => $myUuid,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => "225$phoneNumber",
                ],
                'payerMessage' => 'string',
                'payeeNote' => 'string',
            ];


            // Étape 2: Initier le paiement
            $paymentResponse = $this->httpClient->request(
                'POST',
                'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay',
                [
                    'headers' => [
                        'Authorization' => "Bearer $token",
                        'X-Callback-Url' => 'https://webhook.site/#!/view/3b1651b5-677b-4034-8c6a-e37ce123869e',
                        'X-Reference-Id' => $myUuid,
                        'X-Target-Environment' => 'mtnivorycoast',
                        'Content-Type' => 'application/json',
                        'Cache-Control' => 'no-cache',
                        'Ocp-Apim-Subscription-Key' => $momoSubscriptionKey,
                    ],
                    'json' => $paymentBody,
                ]
            );

            if ($paymentResponse->getStatusCode() === 202 || $paymentResponse->getStatusCode() === 200) {
                // Enregistrer la transaction dans la base
                // dd($paymentResponse->getStatusCode(), $paymentResponse->getHeaders());
                $transaction = new \App\Entity\Transaction();
                if (isset($data['user'])) {
                    $transaction->setUser($this->userRepository->find($data['user']));
                }
                $transaction->setChannel('momo');
                $transaction->setReference($myUuid);
                $transaction->setMontant($amount);
                $transaction->setReferenceChannel($myUuid);
                $transaction->setType("PAIEMENT MOMO");
                $transaction->setTypeUser('etablissement');
                $transaction->setState(0);
                $transaction->setCreatedAtValue(new \DateTimeImmutable());
                $transaction->setUpdatedAt(new \DateTimeImmutable());
                $this->transactionRepository->add($transaction, true);

                return $this->createCorsResponse(
                    [
                        'success' => true,
                        'message' => 'Paiement initié avec succès',
                        'referenceId' => $myUuid,
                    ],
                    200
                );
            } else {
                return $this->createCorsResponse(
                    ['error' => 'Erreur lors de l\'initiation du paiement'],
                    500
                );
            }

        } catch (\Exception $e) {
            return $this->createCorsResponse(
                [
                    'error' => 'Erreur serveur',
                    'message' => $e->getMessage(),
                ],
                500
            );
        }
    }

    #[Route('/verif-status', name: 'api_verif_status', methods: ['POST', 'OPTIONS'])]
    public function verifyPaymentStatus(Request $request): JsonResponse
    {
        // Gérer les requêtes OPTIONS (CORS preflight)
        if ($request->getMethod() === 'OPTIONS') {
            return $this->createCorsResponse();
        }

        try {
            // Credentials
            $username = '8e10c4a7-bcae-4f64-ba50-7b5cfe338366';
            $password = 'b73936c9c1c449c9b6fcebf12aee00f2';
            $basicToken = base64_encode("$username:$password");
            
            // Récupérer la clé primaire depuis les variables d'environnement
            $momoPrimaryKey =  'f42e9a3ae31842fba6e8c2fea23fa0d7';

            // Étape 1: Obtenir le token
            $tokenResponse = $this->httpClient->request('POST', 'https://proxy.momoapi.mtn.com/collection/token/', [
                'headers' => [
                    'Authorization' => "Basic $basicToken",
                    'Cache-Control' => 'no-cache',
                    'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
                ],
            ]);

            if ($tokenResponse->getStatusCode() !== 200) {
                return $this->createCorsResponse(
                    ['error' => 'Erreur lors de la récupération du token'],
                    500
                );
            }

            $tokenData = $tokenResponse->toArray();
            $token = $tokenData['access_token'];

            // Récupérer les données du corps de la requête
            $data = json_decode($request->getContent(), true);
            $referenceId = $data['referenceId'] ?? null;

            if (!$referenceId) {
                return $this->createCorsResponse(
                    ['error' => 'Reference ID requis'],
                    400
                );
            }

            // Étape 2: Vérifier le statut du paiement
            $statusResponse = $this->httpClient->request(
                'GET',
                "https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay/$referenceId",
                [
                    'headers' => [
                        'Authorization' => "Bearer $token",
                        'X-Target-Environment' => 'mtnivorycoast',
                        'Cache-Control' => 'no-cache',
                        'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
                    ],
                ]
            );

            $statusCode = $statusResponse->getStatusCode();

            if ($statusCode === 200) {
                $statusData = $statusResponse->toArray();
                // Mettre à jour le statut de la transaction
                $transaction = $this->transactionRepository->findOneBy(['reference_channel' => $referenceId]);
                if ($transaction) {
                    if (($statusData['status'] ?? null) === 'FAILED') {
                        $transaction->setState(0); // failed
                    } elseif (($statusData['status'] ?? null) !== 'FAILED' && ($statusData['status'] ?? null) !== 'PENDING') {
                        $transaction->setState(1); // completed
                    }
                    $transaction->setUpdatedAt(new \DateTimeImmutable());
                    $this->transactionRepository->add($transaction, true);
                }
                return $this->createCorsResponse(
                    [
                        'success' => true,
                        'message' => 'Paiement traité avec succès',
                        'status' => $statusData['status'] ?? null,
                        'reason' => $statusData['reason'] ?? null,
                        'data' => $statusData,
                    ],
                    200
                );
            } else {
                return $this->createCorsResponse(
                    ['error' => 'Erreur lors de la vérification du statut'],
                    303
                );
            }

        } catch (\Exception $e) {
            return $this->createCorsResponse(
                [
                    'error' => 'Erreur serveur',
                    'message' => $e->getMessage(),
                ],
                303
            );
        }
    }

    /**
     * Créer une réponse avec les headers CORS
     */
    private function createCorsResponse($data = null, int $status = 200): JsonResponse
    {
        $response = new JsonResponse($data, $status);
        
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Ocp-Apim-Subscription-Key, X-Callback-Url, X-Reference-Id, X-Target-Environment');
        
        return $response;
    }
}
