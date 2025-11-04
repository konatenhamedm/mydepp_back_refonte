<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\TransactionRepository;
use App\Repository\ProfessionRepository;
use App\Repository\NiveauInterventionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaiementServiceHub2
{
    private string $apiKey;
    private string $merchantId;
    private string $apiUrl;
    private string $environment;

    public function __construct(
        private EntityManagerInterface $em,
        private TransactionRepository $transactionRepository,
        private HttpClientInterface $httpClient,
        private ParameterBagInterface $params,
        private ProfessionRepository $professionRepository,
        private NiveauInterventionRepository $niveauInterventionRepository,
        private UserRepository $userRepository,
    ) {
       /*  $this->apiKey = $params->get('HUB2_API_KEY');
        $this->merchantId = $params->get('HUB2_MERCHANT_ID');
        $this->apiUrl = $params->get('HUB2_API_URL') ?? 'https://api.hub2.io';
        $this->environment = $params->get('HUB2_ENVIRONMENT') ?? 'sandbox'; */
    }

    /**
     * MÉTHODE 1: Créer Payment Intent + Initier Paiement (Tout-en-un)
     * Cette méthode fait tout en une seule fois :
     * 1. Crée le Payment Intent
     * 2. Initie le paiement avec le provider
     * 3. Retourne le résultat
     */
    public function initPaymentIntent(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        
        // Vérifier que le provider et le numéro sont fournis
        if (!isset($data['provider']) || !isset($data['numero'])) {
            throw new \Exception('Provider et numéro de téléphone requis');
        }
        
        // Déterminer le montant selon le type
        $montant = $this->calculerMontant($request, $data);
        
        // Générer référence unique
        $reference = $this->genererNumero();
        
        // Créer la transaction en base
        $transaction = new Transaction();
        $transaction->setChannel($data['provider']);
        $transaction->setReference($reference);
        $transaction->setMontant($montant);
        $transaction->setReferenceChannel("");
        $transaction->setType($this->determinerType($data));
        $transaction->setTypeUser($data['type'] ?? 'professionnel');
        $transaction->setState(0);
        $transaction->setCreatedAtValue(new \DateTime());
        $transaction->setUpdatedAt(new \DateTime());
        
        // Associer l'utilisateur si renouvellement
        if (isset($data['user'])) {
            $user = $this->userRepository->find($data['user']);
            $transaction->setUser($user);
        }
        
        $this->transactionRepository->add($transaction, true);

        try {
            $customerReference = $this->prepareCustomerReference($data);
            
            // ÉTAPE 1: Créer Payment Intent chez Hub2
            $paymentIntentResponse = $this->httpClient->request('POST', $this->apiUrl . '/payment-intents', [
                'json' => [
                    'customerReference' => $customerReference,
                    'purchaseReference' => $reference,
                    'amount' => $montant,
                    'currency' => 'XOF'
                ],
                'headers' => [
                    /* 'ApiKey' => $this->apiKey,
                    'MerchantId' => $this->merchantId,
                    'Environment' => $this->environment,
                    'Content-Type' => 'application/json', */
                ],
                'verify_peer' => false,
                'verify_host' => false
            ]);

            $paymentIntent = $paymentIntentResponse->toArray();
            
            // Sauvegarder le Payment Intent
            $transaction->setData(json_encode([
                'hub2_intent_id' => $paymentIntent['id'],
                'hub2_token' => $paymentIntent['token'],
                'type_operation' => $data['type'] ?? 'professionnel',
                'provider' => $data['provider']
            ], JSON_UNESCAPED_UNICODE));
            $this->transactionRepository->add($transaction, true);

            // ÉTAPE 2: Initier le paiement immédiatement
            $paymentData = [
                'token' => $paymentIntent['token'],
                'paymentMethod' => 'mobile_money',
                'country' => 'CI',
                'provider' => $data['provider'],
                'mobileMoney' => [
                    'msisdn' => $data['numero']
                ]
            ];
            
            // Ajouter l'OTP si fourni (Orange)
            if (isset($data['otp']) && !empty($data['otp'])) {
                $paymentData['mobileMoney']['otp'] = $data['otp'];
            }
            
            $paymentResponse = $this->httpClient->request(
                'POST',
                $this->apiUrl . '/payment-intents/' . $paymentIntent['id'] . '/payments',
                [
                    'json' => $paymentData,
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'verify_peer' => false,
                    'verify_host' => false
                ]
            );

            $paymentResult = $paymentResponse->toArray();
            
            // Mettre à jour la transaction avec les infos du paiement
            $transactionData = json_decode($transaction->getData(), true);
            $transactionData['payment_result'] = $paymentResult;
            $transaction->setData(json_encode($transactionData, JSON_UNESCAPED_UNICODE));
            $this->transactionRepository->add($transaction, true);

            return [
                'code' => 200,
                'reference' => $reference,
                'type' => $data['type'] ?? 'professionnel',
                'montant' => $montant,
                'provider' => $data['provider'],
                'payment' => $paymentResult,
                'message' => $this->getMessageByProvider($data['provider'], $paymentResult)
            ];

        } catch (\Exception $e) {
            // Supprimer la transaction en cas d'erreur
            $this->transactionRepository->remove($transaction, true);
            throw new \Exception('Erreur paiement: ' . $e->getMessage());
        }
    }
    
    /**
     * Génère un message approprié selon le provider et le résultat
     */
    private function getMessageByProvider(string $provider, array $paymentResult): string
    {
        $status = $paymentResult['status'] ?? '';
        
        if ($status === 'successful') {
            return 'Paiement effectué avec succès';
        }
        
        // Messages selon le provider
        $messages = [
            'orange' => 'Paiement initié. Validez avec votre code OTP Orange Money.',
            'mtn' => 'SMS envoyé sur votre numéro MTN. Validez depuis votre téléphone.',
            'moov' => 'SMS envoyé sur votre numéro Moov. Validez depuis votre téléphone.',
            'wave' => 'Cliquez sur le lien pour finaliser le paiement Wave.'
        ];
        
        return $messages[$provider] ?? 'Paiement en cours de traitement.';
    }

  
    public function initPayment(string $intentId, string $token, array $paymentData): array
    {
        try {
            $response = $this->httpClient->request(
                'POST', 
                $this->apiUrl . '/payment-intents/' . $intentId . '/payments',
                [
                    'json' => array_merge(
                        ['token' => $token],
                        $paymentData
                    ),
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'verify_peer' => false,
                    'verify_host' => false
                ]
            );

            return $response->toArray();
            
        } catch (\Exception $e) {
            throw new \Exception('Erreur initiation paiement: ' . $e->getMessage());
        }
    }

    /**
     * MÉTHODE 3: Gérer le webhook Hub2
     * Cette méthode traite les notifications de Hub2 pour tous les types de paiement
     */
    public function handleWebhook(Request $request, ?string $webhookSecret = null): array
    {
        // ÉTAPE 1: Valider la signature (RECOMMANDÉ EN PRODUCTION)
        if ($webhookSecret) {
            if (!$this->validateWebhookSignature($request, $webhookSecret)) {
                throw new \Exception('Signature webhook invalide - Requête non autorisée');
            }
        }

        $data = json_decode($request->getContent(), true);
        
        // Récupérer le Payment Intent ID depuis les données webhook
        $intentId = $data['id'] ?? null;
        
        if (!$intentId) {
            throw new \Exception('Payment Intent ID manquant dans le webhook');
        }

        // Trouver la transaction associée
        $transactions = $this->transactionRepository->findAll();
        $transaction = null;
        
        foreach ($transactions as $t) {
            $transactionData = json_decode($t->getData() ?? '{}', true);
            if (isset($transactionData['hub2_intent_id']) && 
                $transactionData['hub2_intent_id'] === $intentId) {
                $transaction = $t;
                break;
            }
        }

        if (!$transaction) {
            throw new \Exception('Transaction non trouvée pour Intent ID: ' . $intentId);
        }

        // Traiter selon le statut du paiement
        $status = $data['status'] ?? null;
        
        if ($status === 'successful') {
            return $this->traiterPaiementReussi($transaction, $data);
        } elseif ($status === 'failed') {
            return $this->traiterPaiementEchoue($transaction, $data);
        } else {
            return $this->traiterPaiementEnCours($transaction, $data);
        }
    }

    /**
     * Traite un paiement réussi
     */
    private function traiterPaiementReussi(Transaction $transaction, array $webhookData): array
    {
        $payments = $webhookData['payments'] ?? [];
        $lastPayment = end($payments);

        // Mettre à jour la transaction
        $transaction->setState(1);
        $transaction->setChannel($lastPayment['provider'] ?? 'hub2');
        $transaction->setReferenceChannel($lastPayment['id'] ?? '');
        $transaction->setData(json_encode($webhookData, JSON_UNESCAPED_UNICODE));
        $this->transactionRepository->add($transaction, true);

        return [
            'code' => 200,
            'message' => 'Paiement réussi',
            'reference' => $transaction->getReference(),
            'state' => 1
        ];
    }

    /**
     * Traite un paiement échoué
     */
    private function traiterPaiementEchoue(Transaction $transaction, array $webhookData): array
    {
        $transaction->setState(-1);
        $transaction->setData(json_encode($webhookData, JSON_UNESCAPED_UNICODE));
        $this->transactionRepository->add($transaction, true);

        return [
            'code' => 400,
            'message' => 'Paiement échoué',
            'reference' => $transaction->getReference(),
            'state' => -1
        ];
    }

    /**
     * Traite un paiement en cours
     */
    private function traiterPaiementEnCours(Transaction $transaction, array $webhookData): array
    {
        $transaction->setState(0);
        $transaction->setData(json_encode($webhookData, JSON_UNESCAPED_UNICODE));
        $this->transactionRepository->add($transaction, true);

        return [
            'code' => 202,
            'message' => 'Paiement en cours',
            'reference' => $transaction->getReference(),
            'state' => 0,
            'status' => $webhookData['status'] ?? 'processing'
        ];
    }

    /**
     * Calcule le montant selon le type d'opération
     */
    private function calculerMontant(Request $request, array $data): int
    {
        $type = $data['type'] ?? $request->get('type');

        // Pour les inscriptions
        if (!isset($data['user'])) {
            if ($type === 'professionnel') {
                $professionCode = $request->get('profession') ?? $data['profession'];
                $profession = $this->professionRepository->findOneByCode($professionCode);
                return $profession->getMontantNouvelleDemande();
            } else {
                $niveauId = $request->get('niveauIntervention') ?? $data['niveauIntervention'];
                $niveau = $this->niveauInterventionRepository->find($niveauId);
                return $niveau->getMontant();
            }
        }
        
        // Pour les renouvellements
        $user = $this->userRepository->find($data['user']);
        if ($user->getTypeUser() === 'PROFESSIONNEL') {
            return $user->getPersonne()->getProfession()->getMontantRenouvellement();
        } else {
            return $user->getPersonne()->getNiveauIntervention()->getMontantRenouvellement();
        }
    }

    /**
     * Détermine le type de transaction
     */
    private function determinerType(array $data): string
    {
        if (isset($data['user'])) {
            return 'RENOUVELLEMENT';
        }
        
        if (isset($data['isOep']) && $data['isOep']) {
            return 'OUVERTURE D\'EXPLOITATION';
        }
        
        return 'NOUVELLE DEMANDE';
    }

    /**
     * Prépare la référence client
     */
    private function prepareCustomerReference(array $data): string
    {
        if (isset($data['user'])) {
            $user = $this->userRepository->find($data['user']);
            return 'USER_' . $data['user'] . '_' . $user->getEmail();
        }
        
        if (isset($data['nom']) && isset($data['prenoms'])) {
            return $data['nom'] . '_' . $data['prenoms'];
        }
        
        if (isset($data['denomination'])) {
            return $data['denomination'] . '_' . time();
        }
        
        return 'CLIENT_' . time();
    }

    /**
     * Génère un numéro de référence unique
     */
    private function genererNumero(): string
    {
        $query = $this->em->createQueryBuilder();
        $query->select("count(a.id)")
            ->from(Transaction::class, 'a');

        $nb = $query->getQuery()->getSingleScalarResult();
        return 'DEPPS' . date("ymdHis") . str_pad($nb + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * MÉTHODE BONUS 1: Créer un webhook chez Hub2
     * Enregistre automatiquement vos endpoints webhook auprès de Hub2
     */
    public function createWebhook(string $url, array $events, string $description = ''): array
    {
        try {
            $response = $this->httpClient->request('POST', $this->apiUrl . '/webhooks', [
                'json' => [
                    'url' => $url,
                    'events' => $events,
                    'description' => $description,
                    'metadata' => []
                ],
                'headers' => [
                   /*  'ApiKey' => $this->apiKey,
                    'MerchantId' => $this->merchantId,
                    'Environment' => $this->environment,
                    'Content-Type' => 'application/json', */
                ],
                'verify_peer' => false,
                'verify_host' => false
            ]);

            $webhook = $response->toArray();
            
         
            
            return [
                'code' => 200,
                'webhook' => $webhook,
                'secret' => $webhook['secret'],
                'message' => 'Webhook créé avec succès. Sauvegardez le secret!'
            ];

        } catch (\Exception $e) {
            throw new \Exception('Erreur création webhook: ' . $e->getMessage());
        }
    }


    public function ensureWebhooksExist(string $baseUrl): array
    {
        try {
            // Lister les webhooks existants
            $existingWebhooks = $this->listWebhooks();
            
            $webhookUrls = [
                $baseUrl . '/api/paiement/info-paiement',
                $baseUrl . '/api/paiement/info-paiement-oep',
                $baseUrl . '/api/paiement/info-paiement-renouvellement'
            ];
            
            $existingUrls = array_map(function($wh) {
                return $wh['url'] ?? '';
            }, $existingWebhooks);
            
            $createdWebhooks = [];
            $skippedWebhooks = [];
            
            // Webhook 1: Inscriptions
            if (!in_array($webhookUrls[0], $existingUrls)) {
                $createdWebhooks[] = $this->createWebhook(
                    $webhookUrls[0],
                    ['payment_intent.successful', 'payment_intent.failed'],
                    'Webhook pour les paiements d\'inscription'
                );
            } else {
                $skippedWebhooks[] = $webhookUrls[0] . ' (existe déjà)';
            }
            
            // Webhook 2: OEP
            if (!in_array($webhookUrls[1], $existingUrls)) {
                $createdWebhooks[] = $this->createWebhook(
                    $webhookUrls[1],
                    ['payment_intent.successful', 'payment_intent.failed'],
                    'Webhook pour les paiements OEP'
                );
            } else {
                $skippedWebhooks[] = $webhookUrls[1] . ' (existe déjà)';
            }
            
            // Webhook 3: Renouvellements
            if (!in_array($webhookUrls[2], $existingUrls)) {
                $createdWebhooks[] = $this->createWebhook(
                    $webhookUrls[2],
                    ['payment_intent.successful', 'payment_intent.failed'],
                    'Webhook pour les renouvellements'
                );
            } else {
                $skippedWebhooks[] = $webhookUrls[2] . ' (existe déjà)';
            }
            
            return [
                'code' => 200,
                'created' => $createdWebhooks,
                'skipped' => $skippedWebhooks,
                'message' => count($createdWebhooks) > 0 
                    ? 'Webhooks créés avec succès. SAUVEGARDEZ LES SECRETS!'
                    : 'Tous les webhooks existent déjà.'
            ];
            
        } catch (\Exception $e) {
            throw new \Exception('Erreur vérification webhooks: ' . $e->getMessage());
        }
    }

    public function validateWebhookSignature(Request $request, string $webhookSecret): bool
    {
        $signature = $request->headers->get('Hub2-Signature');
        
        if (!$signature) {
            return false;
        }

        // Parse la signature (format: s1=xxx,s0=xxx)
        $signatures = [];
        foreach (explode(',', $signature) as $part) {
            [$version, $sig] = explode('=', trim($part));
            $signatures[$version] = $sig;
        }

        // Récupérer le payload
        $payload = $request->getContent();

        // Vérifier avec s1 (HMAC-SHA256)
        if (isset($signatures['s1'])) {
            $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
            
            if (hash_equals($expectedSignature, $signatures['s1'])) {
                return true;
            }
        }

        // Vérifier avec s0 (SHA256 simple - fallback)
        if (isset($signatures['s0'])) {
            $expectedSignature = hash('sha256', $payload . $webhookSecret);
            
            if (hash_equals($expectedSignature, $signatures['s0'])) {
                return true;
            }
        }

        return false;
    }

    public function listWebhooks(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiUrl . '/webhooks', [
                'headers' => [
                   /*  'ApiKey' => $this->apiKey,
                    'MerchantId' => $this->merchantId,
                    'Environment' => $this->environment, */
                ],
                'verify_peer' => false,
                'verify_host' => false
            ]);

            return $response->toArray();

        } catch (\Exception $e) {
            throw new \Exception('Erreur liste webhooks: ' . $e->getMessage());
        }
    }

    public function deleteWebhook(string $webhookId): array
    {
        try {
            $response = $this->httpClient->request(
                'DELETE', 
                $this->apiUrl . '/webhooks/' . $webhookId,
                [
                    'headers' => [
                        /* 'ApiKey' => $this->apiKey,
                        'MerchantId' => $this->merchantId,
                        'Environment' => $this->environment, */
                    ],
                    'verify_peer' => false,
                    'verify_host' => false
                ]
            );

            return [
                'code' => 200,
                'message' => 'Webhook supprimé avec succès'
            ];

        } catch (\Exception $e) {
            throw new \Exception('Erreur suppression webhook: ' . $e->getMessage());
        }
    }
}