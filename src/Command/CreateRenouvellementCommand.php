<?php

namespace App\Command;

use App\Service\RenouvellementService; // Adapter au nom réel de ton service
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:update-renouvellement',
    description: 'Met à jour automatiquement les renouvellements de contrat.'
)]
class CreateRenouvellementCommand extends Command
{
    private RenouvellementService $renouvellementService;

    public function __construct(RenouvellementService $renouvellementService)
    {
        $this->renouvellementService = $renouvellementService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Début de la mise à jour des renouvellements...',
            '---------------------------------------------',
        ]);

        try {
            $result = $this->renouvellementService->updateData();

            $output->writeln('Mise à jour terminée avec succès :');
            $output->writeln($result);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>Erreur lors de la mise à jour :</error>');
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}
