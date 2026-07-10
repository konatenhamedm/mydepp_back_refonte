<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260701122618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('autre_document_professionnel', 'statut')) {
            $this->addSql('ALTER TABLE autre_document_professionnel ADD statut VARCHAR(50) DEFAULT NULL');
        }
        if (!$this->columnExists('autre_document_professionnel', 'message')) {
            $this->addSql('ALTER TABLE autre_document_professionnel ADD message LONGTEXT DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('autre_document_professionnel', 'statut')) {
            $this->addSql('ALTER TABLE autre_document_professionnel DROP statut');
        }
        if ($this->columnExists('autre_document_professionnel', 'message')) {
            $this->addSql('ALTER TABLE autre_document_professionnel DROP message');
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );
    }
}
