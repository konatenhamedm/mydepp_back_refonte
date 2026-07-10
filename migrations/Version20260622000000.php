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
        if ($this->columnExists('temp_professionnel', 'origine_diplome')) {
            $this->write('Colonne origine_diplome déjà présente sur temp_professionnel — étape ignorée.');
            return;
        }
        $this->addSql('ALTER TABLE temp_professionnel ADD origine_diplome VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        if (!$this->columnExists('temp_professionnel', 'origine_diplome')) {
            return;
        }
        $this->addSql('ALTER TABLE temp_professionnel DROP COLUMN origine_diplome');
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );
    }
}
