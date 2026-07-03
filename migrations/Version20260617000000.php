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
        if ($this->columnExists('membre_professionnel', 'lieu_obtention_diplome')) {
            $this->write('Colonne lieu_obtention_diplome déjà présente sur membre_professionnel — étape ignorée.');
            return;
        }
        $this->addSql('ALTER TABLE membre_professionnel ADD lieu_obtention_diplome VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        if (!$this->columnExists('membre_professionnel', 'lieu_obtention_diplome')) {
            return;
        }
        $this->addSql('ALTER TABLE membre_professionnel DROP lieu_obtention_diplome');
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );
    }
}
