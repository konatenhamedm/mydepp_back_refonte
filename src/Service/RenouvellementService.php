<?php

namespace App\Service;

use App\Repository\EtablissementRepository;
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
    private EtablissementRepository $repoEtablissementRepository;

    public function __construct(
        TransactionRepository $transactionRepository,
        ProfessionnelRepository $repoProfessionnel,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        SendMailService $sendMailService,
        EtablissementRepository $repoEtablissementRepository
    ) {
        $this->repoTransaction = $transactionRepository;
        $this->repoProfessionnel = $repoProfessionnel;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->sendMailService = $sendMailService;
        $this->repoEtablissementRepository = $repoEtablissementRepository;
    }

    public function updateData(): string
    {
        $now = new \DateTime();
        $currentYear = (int) $now->format('Y');
        $compteur = 0;

        $professionnels = $this->repoProfessionnel->createQueryBuilder('p')
            ->where('p.status NOT IN (:statuts)')
            ->andWhere('p.code IS NOT NULL') 
            ->setParameter('statuts', ['attente', 'rejete', 'accepte', 'refuse', 'renouvellement','a_jour'])
            ->getQuery()
            ->getResult();

        foreach ($professionnels as $pro) {
           
            $user = $this->userRepository->findOneBy(['personne' => $pro->getId()]);

            if (!$user) {
                continue;
            }

            $code = $pro->getCode();
            // On cherche "MS" suivi de 4 chiffres dans le code (ex: MS2025OPTLO1992.0493)
            if ($code && preg_match('/MS(\d{4})/', $code, $matches)) {
                $yearCode = (int) $matches[1];
                
                // Si l'année extraite est inférieure à l'année en cours, l'abonnement a expiré
                if ($yearCode < $currentYear) {
                    $pro->setStatus('renouvellement');
                    $this->entityManager->persist($pro);

                    // Envoi de mail
                    $user_message = [
                        'message' => "Bonjour " . $user->getEmail() . ",\n\nNous vous informons que votre abonnement est arrivé à expiration.\n\nAfin de régulariser votre situation, nous vous invitons à procéder à son renouvellement. Vous pouvez effectuer cette démarche directement depuis votre tableau de bord en ligne, ou en vous rendant dans nos locaux.\n\nCordialement,",
                    ];

                    $context = compact('user_message');

                    $this->sendMailService->send(
                        'depps@leadagro.net',
                        $user->getEmail(),
                        'Informations - Renouvellement Abonnement',
                        'renew_mail',
                        $context
                    );

                    $compteur++;
                }
            }
        }

        // Persiste les modifications
        $this->entityManager->flush();

        return "$compteur professionnel(s) ont été mis à jour pour renouvellement.";
    }

    public function updateDataEtablissement(): string
    {
        $now = new \DateTime();
        $compteur = 0;

        $etablissements = $this->repoEtablissementRepository->createQueryBuilder('p')
            ->where('p.status = :statut')
            ->andWhere('p.dateValidation != :now')
            ->setParameter('statut', 'oep_dossier_conforme')
            ->setParameter('now', null)
            ->getQuery()
            ->getResult();

        foreach ($etablissements as $etab) {
           
            $user = $this->userRepository->findOneBy(['personne' => $etab->getId()]);

            if (!$user) {
                continue;
            }

            $dateTransaction = $etab->getDateValidation();
            $diff = $dateTransaction->diff($now);

     
                if ($diff->y >= 1) {
                    $etab->setStatus('renouvellement');
                    $this->entityManager->persist($etab);

                    $user_message = [
                        'message' => "Bonjour " . $user->getEmail() . ",\n\nNous vous informons que votre abonnement est arrivé à expiration.\n\nAfin de régulariser votre situation, nous vous invitons à procéder à son renouvellement. Vous pouvez effectuer cette démarche directement depuis votre tableau de bord en ligne, ou en vous rendant dans nos locaux.\n\nCordialement,",
                    ];

                    $context = compact('user_message');

                    $this->sendMailService->send(
                        'depps@leadagro.net',
                        $user->getEmail(),
                        'Informations - Renouvellement Abonnement',
                        'renew_mail',
                        $context
                    );

                    $compteur++;
                }
          
        }

        $this->entityManager->flush();

        return "$compteur etablissement(s) ont été mis à jour pour renouvellement.";
    }
}
