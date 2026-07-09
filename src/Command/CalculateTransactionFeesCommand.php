<?php

namespace App\Command;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:transactions:calculate-fees',
    description: "Calcule et renseigne le champ fee (1% du montant) pour les transactions déjà en base qui n'en ont pas encore."
)]
class CalculateTransactionFeesCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Calcul des fees (1% du montant) pour les transactions existantes...',
            '---------------------------------------------------------------',
        ]);

        try {
            $connection = $this->entityManager->getConnection();

            $affected = $connection->executeStatement(
                sprintf(
                    "UPDATE transaction SET fee = CAST(ROUND(CAST(montant AS DECIMAL(20,2)) * %s) AS SIGNED) WHERE fee IS NULL OR fee = ''",
                    Transaction::TAUX_FEE
                )
            );

            $output->writeln(sprintf('<info>%d transaction(s) mise(s) à jour avec leur fee.</info>', $affected));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>Erreur lors du calcul des fees :</error>');
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }
    }
}
