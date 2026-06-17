<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add missing column membre_professionnel.lieu_obtention_diplome
 * (mapped by Professionnel::$lieuObtentionDiplome but never migrated).
 */
final class Version20260617000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add lieu_obtention_diplome column to membre_professionnel';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE membre_professionnel ADD lieu_obtention_diplome VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE membre_professionnel DROP lieu_obtention_diplome');
    }
}
