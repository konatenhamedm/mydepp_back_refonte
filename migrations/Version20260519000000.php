<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260519000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add etat_old column to professionnel table (set to "init" on Excel import)';
    }

    public function up(Schema $schema): void
    {
        $columnExists = (bool) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'membre_professionnel' AND column_name = 'etat_old'"
        );

        if ($columnExists) {
            $this->write('Colonne etat_old déjà présente sur membre_professionnel — étape ignorée.');
            return;
        }

        $this->addSql('ALTER TABLE membre_professionnel ADD etat_old VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $columnExists = (bool) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'membre_professionnel' AND column_name = 'etat_old'"
        );

        if (!$columnExists) {
            return;
        }

        $this->addSql('ALTER TABLE membre_professionnel DROP COLUMN etat_old');
    }
}
