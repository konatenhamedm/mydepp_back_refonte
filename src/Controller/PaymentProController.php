<?php


namespace App\Controller;

use App\Controller\Apis\Config\ApiInterface;
use App\Repository\NiveauInterventionRepository;
use App\Repository\ProfessionnelRepository;
use App\Repository\ProfessionRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Service\PaiementProService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Ramsey\Uuid\Uuid;

#[Route('/api/paiement2')]
class PaymentProController extends ApiInterface
{
    public function __construct(
        private PaiementProService $paiementServices,
    ) {}

    #[Route('/paiement', name: 'new_paiement', methods: ['POST'])]
    public function paiement(Request $request, ProfessionnelRepository $professionnelRepository, NiveauInterventionRepository $niveauInterventionRepository, ProfessionRepository $professionRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Valider les données requises
        if (!$request->get('phoneNumber') || !$request->get('type')) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        // 1. Récupérer le token Momo
        $token = $this->paiementServices->getMomoToken();
        if (!$token) {
            return $this->json(['error' => 'Erreur lors de la récupération du token'], 500);
        }

        // 2. Préparer la requête de paiement
        $referenceId = $this->paiementServices->generateReferenceId();

        $montant = $request->get('type') == "professionnel" ? $professionRepository->findOneByCode($request->get('profession'))->getMontantNouvelleDemande() : $niveauInterventionRepository->find($request->get('niveauIntervention'))->getMontant();

        $body = [
            'amount' => (string) $montant,
            'currency' => 'XOF',
            'externalId' => $referenceId,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => '225' . $request->get('phoneNumber'),
            ],
            'payerMessage' => 'Paiement',
            'payeeNote' => 'Paiement',
        ];

        // 3. Appeler l'API Momo
        $result = $this->paiementServices->initiateMomoPayment($token, $body, $referenceId);

        // 4. Enregistrer la transaction selon le type
        if ($result['success']) {

            $this->paiementServices->initTransactionTemp($request, $montant, $referenceId);

            return $this->json([
                'success' => true,
                'message' => 'Paiement initié avec succès',
                'reference' => $referenceId
            ], 201);
        } else {
            return $this->json(['error' => $result['error'] ?? 'Erreur lors de l\'initiation du paiement'], 500);
        }
    }

    #[Route('/paiement/{referenceId}/statut', name: 'check_payment_status', methods: ['GET'])]
    public function verifierStatut(string $referenceId): JsonResponse
    {
        $result = $this->paiementServices->verifierStatutPaiementPro($referenceId);

        if ($result['success'] ?? false) {

            return $this->json([
                'success' => true,
                'status' => $result['status'],
                'data' => $result['data'] ?? [],
            ]);
        } else {
            return $this->json(['error' => $result['error'] ?? 'Erreur lors de la vérification'], 500);
        }
    }

    #[Route('/paiement/{referenceId}/transaction', name: 'get_transaction', methods: ['GET'])]
    public function getTransaction(string $referenceId): JsonResponse
    {
        $transaction = $this->paiementServices->getTransactionByReference($referenceId);

        if (!$transaction) {
            return $this->json(['error' => 'Transaction non trouvée'], 404);
        }

        return $this->json([
            'success' => true,
            'transaction' => [
                'reference' => $transaction->getReference(),
                'montant' => $transaction->getMontant(),
                'type' => $transaction->getType(),
                'typeUser' => $transaction->getTypeUser(),
                'state' => $transaction->getState(),
                'channel' => $transaction->getChannel(),
                'createdAt' => $transaction->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $transaction->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    #[Route('/paiement/webhook/momo', name: 'momo_webhook', methods: ['POST'])]
    public function momoWebhook(Request $request): JsonResponse
    {
        $webhookData = json_decode($request->getContent(), true);

        $result = $this->paiementServices->traiterWebhookMomo($webhookData);

        if ($result['success'] ?? false) {
            return $this->json([
                'success' => true,
                'message' => $result['message'] ?? 'Webhook traité',
                'status' => $result['status'] ?? null,
            ], 200);
        } else {
            return $this->json(['error' => $result['error'] ?? 'Erreur lors du traitement du webhook'], 500);
        }
    }

    #[Route('/paiement/{referenceId}/mise-a-jour', name: 'update_payment_status', methods: ['POST'])]
    public function mettreAJourStatut(Request $request, string $referenceId): JsonResponse
    {
        $data = json_decode($request->getContent(), true);


        $result = $this->paiementServices->verifierEtMettreAJourStatut($referenceId);

        if ($result['success'] ?? false) {
            return $this->json([
                'success' => true,
                'message' => 'Statut mis à jour',
                'status' => $result['status'],
                'data' => $result['data'] ?? [],
            ]);
        } else {
            return $this->json(['error' => $result['error'] ?? 'Erreur lors de la mise à jour'], 500);
        }
    }

    #[Route('/paiement/initier', name: 'initier_paiement', methods: ['POST'])]
    public function initierPaiement(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Valider les données requises
        if (!isset($data['amount'], $data['phoneNumber'], $data['user'])) {
            return $this->json(['error' => 'Données manquantes (amount, phoneNumber, user)'], 400);
        }

        $result = $this->paiementServices->initierPaiementPro($data);

        if ($result['success'] ?? false) {
            return $this->json([
                'success' => true,
                'message' => $result['message'],
                'referenceId' => $result['referenceId'],
            ], 201);
        } else {
            return $this->json(['error' => $result['error'] ?? 'Erreur lors de l\'initiation du paiement'], 500);
        }
    }
}
