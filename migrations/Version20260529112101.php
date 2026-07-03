<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260529112101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($this->columnExists('membre_professionnel', 'lieu_naissance')) {
            $this->write('Colonne lieu_naissance déjà présente sur membre_professionnel — étape ignorée.');
            return;
        }
        $this->addSql('ALTER TABLE membre_professionnel ADD lieu_naissance VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        if (!$this->columnExists('membre_professionnel', 'lieu_naissance')) {
            return;
        }
        $this->addSql('ALTER TABLE membre_professionnel DROP lieu_naissance');
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );
    }
}
