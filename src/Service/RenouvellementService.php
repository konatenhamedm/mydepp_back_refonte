<?php

namespace App\Service;

use App\Repository\ProfessionnelRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\SendMailService;

class RenouvellementService
{
    private TransactionRepository $repoTransaction;
    private ProfessionnelRepository $repoProfessionnel;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private SendMailService $sendMailService;

    public function __construct(
        TransactionRepository $transactionRepository,
        ProfessionnelRepository $repoProfessionnel,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        SendMailService $sendMailService,
    ) {
        $this->repoTransaction = $transactionRepository;
        $this->repoProfessionnel = $repoProfessionnel;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->sendMailService = $sendMailService;
    }

    public function updateData(): string
    {
        $now = new \DateTime();
        $compteur = 0;

        // Étape 1 : récupérer les professionnels dont le statut est différent de "renouvellement"
        $professionnels = $this->repoProfessionnel->createQueryBuilder('p')
            ->where('p.status != :statut')
            ->setParameter('statut', 'renouvellement') // corrigé ici
            ->getQuery()
            ->getResult();

        foreach ($professionnels as $pro) {
            // Étape 2 : récupérer le user lié au professionnel
            $user = $this->userRepository->findOneBy(['personne' => $pro->getId()]);

            if (!$user) {
                continue;
            }

            /*// Étape 3 : récupérer la dernière transaction réussie
            $lastTransaction = $this->repoTransaction->createQueryBuilder('t')
                ->where('t.user = :user')
                ->andWhere('t.state = :state')
                ->setParameter('user', $user)
                ->setParameter('state', 1)
                ->orderBy('t.createdAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult(); */

            /*  if ($lastTransaction) { */
            $dateTransaction = $pro->getDateValidation();
            $diff = $dateTransaction->diff($now);

            if ($pro->getDateValidation() != null) {
                // Étape 4 : si la dernière transaction date de plus d'un an
                if ($diff->y >= 1) {
                    $pro->setStatus('renouvellement');
                    $this->entityManager->persist($pro);

                    // Envoi de mail
                    $user_message = [
                        'message' => "Bonjour " . $user->getEmail() . ", votre abonnement a expiré. Veuillez vous connecter à votre dashboard pour le renouveler.",
                    ];

                    $context = compact('user_message');

                    $this->sendMailService->send(
                        'depps@myonmci.ci',
                        $user->getEmail(),
                        'Informations - Renouvellement Abonnement',
                        'renew_mail',
                        $context
                    );

                    $compteur++;
                }
            }

            /* } */
        }

        // Persiste les modifications
        $this->entityManager->flush();

        return "$compteur professionnel(s) ont été mis à jour pour renouvellement.";
    }
}
