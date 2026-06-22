<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260622000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add origine_diplome column to temp_professionnel';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE temp_professionnel ADD origine_diplome VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE temp_professionnel DROP COLUMN origine_diplome');
    }
}
