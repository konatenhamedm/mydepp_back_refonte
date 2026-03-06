<?php


namespace App\Service;

use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Ramsey\Uuid\Uuid;

class PaiementProService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private UserRepository $userRepository,
        private HttpClientInterface $httpClient,
    ) {}

    /**
     * Initie un paiement professionnel via Momo
     */
    public function initierPaiementPro(array $data): array
    {
        $username = '8e10c4a7-bcae-4f64-ba50-7b5cfe338366';
        $password = 'b73936c9c1c449c9b6fcebf12aee00f2';
        $basicToken = base64_encode("$username:$password");
        $momoPrimaryKey = $_ENV['MOMO_PRIMARY_KEY'] ?? '';
        $momoSubscriptionKey = $_ENV['MOMO_SUBSCRIPTION_KEY'] ?? 'f42e9a3ae31842fba6e8c2fea23fa0d7';

        // Obtenir le token
        $tokenResponse = $this->httpClient->request('POST', 'https://proxy.momoapi.mtn.com/collection/token/', [
            'headers' => [
                'Authorization' => "Basic $basicToken",
                'Cache-Control' => 'no-cache',
                'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
            ],
        ]);
        if ($tokenResponse->getStatusCode() !== 200) {
            return ['error' => 'Erreur lors de la récupération du token'];
        }
        $token = $tokenResponse->toArray()['access_token'];

        $amount = $data['amount'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $userId = $data['user'] ?? null;
        if (!$amount || !$phoneNumber || !$userId) {
            return ['error' => 'Montant, numéro de téléphone et user requis'];
        }
        $myUuid = Uuid::uuid4()->toString();
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
        if (!in_array($paymentResponse->getStatusCode(), [200, 202])) {
            return ['error' => 'Erreur lors de l\'initiation du paiement'];
        }
        // Enregistrer la transaction
        $transaction = new Transaction();
        $transaction->setUser($this->userRepository->find($userId));
        $transaction->setChannel('momo');
        $transaction->setReference($myUuid);
        $transaction->setMontant($amount);
        $transaction->setReferenceChannel($myUuid);
        $transaction->setType('PAIEMENT MOMO PRO');
        $transaction->setTypeUser('professionnel');
        $transaction->setState(0);
        $transaction->setCreatedAtValue(new \DateTimeImmutable());
        $transaction->setUpdatedAt(new \DateTimeImmutable());
        $this->transactionRepository->add($transaction, true);
        return [
            'success' => true,
            'message' => 'Paiement initié avec succès',
            'referenceId' => $myUuid,
        ];
    }

    /**
     * Vérifie le statut d'un paiement professionnel et met à jour la transaction
     */
    public function verifierStatutPaiementPro(string $referenceId): array
    {
        $username = '8e10c4a7-bcae-4f64-ba50-7b5cfe338366';
        $password = 'b73936c9c1c449c9b6fcebf12aee00f2';
        $basicToken = base64_encode("$username:$password");
        $momoPrimaryKey = $_ENV['MOMO_PRIMARY_KEY'] ?? '';
        // Obtenir le token
        $tokenResponse = $this->httpClient->request('POST', 'https://proxy.momoapi.mtn.com/collection/token/', [
            'headers' => [
                'Authorization' => "Basic $basicToken",
                'Cache-Control' => 'no-cache',
                'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
            ],
        ]);
        if ($tokenResponse->getStatusCode() !== 200) {
            return ['error' => 'Erreur lors de la récupération du token'];
        }
        $token = $tokenResponse->toArray()['access_token'];
        // Vérifier le statut
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
        if ($statusResponse->getStatusCode() !== 200) {
            return ['error' => 'Erreur lors de la vérification du statut'];
        }
        $statusData = $statusResponse->toArray();
        $transaction = $this->transactionRepository->findOneBy(['reference' => $referenceId]);
        if ($transaction) {
            if (($statusData['status'] ?? null) === 'FAILED') {
                $transaction->setState(-1);
            } elseif (($statusData['status'] ?? null) !== 'FAILED' && ($statusData['status'] ?? null) !== 'PENDING') {
                $transaction->setState(1);
            }
            $transaction->setUpdatedAt(new \DateTimeImmutable());
            $this->transactionRepository->add($transaction, true);
        }
        return [
            'success' => true,
            'message' => 'Statut vérifié',
            'status' => $statusData['status'] ?? null,
            'reason' => $statusData['reason'] ?? null,
            'data' => $statusData,
        ];
    }

    public function getMomoToken(): ?string
    {
        $username = '8e10c4a7-bcae-4f64-ba50-7b5cfe338366';
        $password = 'b73936c9c1c449c9b6fcebf12aee00f2';
        $basicToken = base64_encode("$username:$password");
        $momoPrimaryKey = $_ENV['MOMO_PRIMARY_KEY'] ?? '';

        try {
            $tokenResponse = $this->httpClient->request('POST', 'https://proxy.momoapi.mtn.com/collection/token/', [
                'headers' => [
                    'Authorization' => "Basic $basicToken",
                    'Cache-Control' => 'no-cache',
                    'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
                ],
            ]);

            if ($tokenResponse->getStatusCode() !== 200) {
                return null;
            }

            $data = $tokenResponse->toArray();
            return $data['access_token'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Génère un identifiant de référence unique
     */
    public function generateReferenceId(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Initie un paiement Momo
     */
    public function initiateMomoPayment(string $token, array $body, string $referenceId): array
    {
        $momoSubscriptionKey = $_ENV['MOMO_SUBSCRIPTION_KEY'] ?? 'f42e9a3ae31842fba6e8c2fea23fa0d7';

        try {
            $paymentResponse = $this->httpClient->request(
                'POST',
                'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay',
                [
                    'headers' => [
                        'Authorization' => "Bearer $token",
                        'X-Callback-Url' => 'https://webhook.site/#!/view/3b1651b5-677b-4034-8c6a-e37ce123869e',
                        'X-Reference-Id' => $referenceId,
                        'X-Target-Environment' => 'mtnivorycoast',
                        'Content-Type' => 'application/json',
                        'Cache-Control' => 'no-cache',
                        'Ocp-Apim-Subscription-Key' => $momoSubscriptionKey,
                    ],
                    'json' => $body,
                ]
            );

            if (in_array($paymentResponse->getStatusCode(), [200, 202])) {
                return ['success' => true];
            }

            return ['success' => false, 'error' => 'Erreur lors de l\'initiation du paiement'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Crée une transaction temporaire pour un professionnel
     */
    public function createProfessionnelTemp(array $data, string $referenceId): void
    {
        $transaction = new Transaction();
        $transaction->setUser($this->userRepository->find($data['user']));
        $transaction->setChannel('momo');
        $transaction->setReference($referenceId);
        $transaction->setMontant($data['amount']);
        $transaction->setReferenceChannel($referenceId);
        $transaction->setType('PAIEMENT MOMO PRO');
        $transaction->setTypeUser('professionnel');
        $transaction->setState(0);
        $transaction->setCreatedAtValue(new \DateTimeImmutable());
        $transaction->setUpdatedAt(new \DateTimeImmutable());
        $this->transactionRepository->add($transaction, true);
    }

    /**
     * Crée une transaction temporaire pour un établissement
     */
    public function createEtablissementTemp(array $data, string $referenceId): void
    {
        $transaction = new Transaction();
        $transaction->setUser($this->userRepository->find($data['user']));
        $transaction->setChannel('momo');
        $transaction->setReference($referenceId);
        $transaction->setMontant($data['amount']);
        $transaction->setReferenceChannel($referenceId);
        $transaction->setType('PAIEMENT MOMO PRO');
        $transaction->setTypeUser('etablissement');
        $transaction->setState(0);
        $transaction->setCreatedAtValue(new \DateTimeImmutable());
        $transaction->setUpdatedAt(new \DateTimeImmutable());
        $this->transactionRepository->add($transaction, true);
    }

    /**
     * Traite le webhook de notification de paiement Momo
     */
    public function traiterWebhookMomo(array $webhookData): array
    {
        try {
            $referenceId = $webhookData['referenceId'] ?? null;
            
            if (!$referenceId) {
                return ['success' => false, 'error' => 'Reference ID manquant'];
            }

            // Récupérer la transaction
            $transaction = $this->transactionRepository->findOneBy(['reference' => $referenceId]);
            
            if (!$transaction) {
                return ['success' => false, 'error' => 'Transaction non trouvée'];
            }

            // Vérifier le statut auprès de l'API Momo
            $statusResult = $this->verifierStatutPaiementPro($referenceId);
            
            if (!$statusResult['success']) {
                return $statusResult;
            }

            return [
                'success' => true,
                'message' => 'Webhook traité avec succès',
                'status' => $statusResult['status'],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifie et met à jour le statut d'une transaction via webhook
     */
    public function verifierEtMettreAJourStatut(string $referenceId): array
    {
        $username = '8e10c4a7-bcae-4f64-ba50-7b5cfe338366';
        $password = 'b73936c9c1c449c9b6fcebf12aee00f2';
        $basicToken = base64_encode("$username:$password");
        $momoPrimaryKey = $_ENV['MOMO_PRIMARY_KEY'] ?? '';

        try {
            // Obtenir le token
            $tokenResponse = $this->httpClient->request('POST', 'https://proxy.momoapi.mtn.com/collection/token/', [
                'headers' => [
                    'Authorization' => "Basic $basicToken",
                    'Cache-Control' => 'no-cache',
                    'Ocp-Apim-Subscription-Key' => $momoPrimaryKey,
                ],
            ]);

            if ($tokenResponse->getStatusCode() !== 200) {
                return ['success' => false, 'error' => 'Erreur lors de la récupération du token'];
            }

            $token = $tokenResponse->toArray()['access_token'];

            // Vérifier le statut du paiement
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

            if ($statusResponse->getStatusCode() !== 200) {
                return ['success' => false, 'error' => 'Erreur lors de la vérification du statut'];
            }

            $statusData = $statusResponse->toArray();
            $status = $statusData['status'] ?? null;

            // Mettre à jour la transaction
            $transaction = $this->transactionRepository->findOneBy(['reference' => $referenceId]);
            
            if ($transaction) {
                if ($status === 'SUCCESSFUL') {
                    $transaction->setState(1);
                } elseif ($status === 'FAILED') {
                    $transaction->setState(-1);
                } else {
                    $transaction->setState(0); // PENDING
                }
                
                $transaction->setUpdatedAt(new \DateTimeImmutable());
                $this->transactionRepository->add($transaction, true);
            }

            return [
                'success' => true,
                'status' => $status,
                'data' => $statusData,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Récupère une transaction par sa référence
     */
    public function getTransactionByReference(string $referenceId): ?Transaction
    {
        return $this->transactionRepository->findOneBy(['reference' => $referenceId]);
    }
}