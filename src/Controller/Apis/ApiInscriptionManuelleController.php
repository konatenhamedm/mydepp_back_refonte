<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Transaction;
use App\Repository\TempProfessionnelRepository;
use App\Repository\TransactionRepository;
use App\Service\PaiementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api/inscription-manuelle')]
class ApiInscriptionManuelleController extends ApiInterface
{
    #[Route('/search', methods: ['GET'])]
    public function search(Request $request, TempProfessionnelRepository $tempProfessionnelRepository): Response
    {
        $email = $request->query->get('email');
        if (!$email) {
            return $this->json(['error' => 'L\'email est requis'], Response::HTTP_BAD_REQUEST);
        }

        $temps = $tempProfessionnelRepository->findBy(['email' => $email]);
        
        return $this->responseData($temps, 'group_pro', ['Content-Type' => 'application/json']);
    }

    #[Route('/validate/{id}', methods: ['POST'])]
    public function validateInscription(
        int $id,
        Request $request,
        TempProfessionnelRepository $tempProfessionnelRepository,
        TransactionRepository $transactionRepository,
        PaiementService $paiementService,
        EntityManagerInterface $em
    ): Response {
        $data = json_decode($request->getContent(), true);
        $numero = $data['numero'] ?? null;
        $referenceParam = $data['reference'] ?? null;
        $montant = $data['montant'] ?? null;

        if (!$numero || !$referenceParam || !$montant) {
            return $this->json(['error' => 'Numéro, référence et montant sont requis'], Response::HTTP_BAD_REQUEST);
        }

        $tempPro = $tempProfessionnelRepository->find($id);
        if (!$tempPro) {
            return $this->json(['error' => 'Profil temporaire non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // On utilise la référence fournie dans le formulaire pour la transaction
        $transaction = $transactionRepository->findOneBy(['reference' => $referenceParam]);
        if (!$transaction) {
            $transaction = new Transaction();
            $transaction->setReference($referenceParam);
            $transaction->setNumero($numero);
            $transaction->setMontant($montant);
            $transaction->setChannel("momo");
            $transaction->setType("NOUVELLE DEMANDE");
            $transaction->setState(1); // Validé
            $transaction->setReferenceChannel($referenceParam);
            $transaction->setTypeUser("professionnel"); // Requis pour la suite
            $transaction->setCreatedAtValue();
            $transaction->setUpdatedAt();
        } else {
            $transaction->setMontant($montant);
            $transaction->setNumero($numero);
            $transaction->setState(1);
            $transaction->setUpdatedAt();
        }
        $em->persist($transaction);

        // On met à jour la référence du TempProfessionnel pour que updateProfessionnel la trouve
        $tempPro->setReference($referenceParam);
        $em->persist($tempPro);
        $em->flush();

        // Appel du service pour créer User + Professionnel (et supprimer le TempProfessionnel)
        try {
            $response = $paiementService->updateProfessionnel($referenceParam);

            if (isset($response['code']) && $response['code'] == 200) {
                // Le service supprime le TempProfessionnel en cas de succès, mais s'il ne le fait pas, on le fait ici
                if ($tempPro->getId()) {
                    $tempProfessionnelRepository->remove($tempPro, true);
                }
                return $this->json(['success' => true, 'message' => 'Inscription validée avec succès']);
            } else {
                return $this->json(['error' => 'Erreur lors de la validation métier'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
