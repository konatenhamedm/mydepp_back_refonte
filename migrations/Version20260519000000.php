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
        $this->addSql('ALTER TABLE professionnel ADD etat_old VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE professionnel DROP COLUMN etat_old');
    }
}
