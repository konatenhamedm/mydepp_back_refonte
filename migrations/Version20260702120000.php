<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260702120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les colonnes type et sous_type à admin_document (classification de la documenthèque)';
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('admin_document', 'type')) {
            $this->addSql('ALTER TABLE admin_document ADD type VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('admin_document', 'sous_type')) {
            $this->addSql('ALTER TABLE admin_document ADD sous_type VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('admin_document', 'type')) {
            $this->addSql('ALTER TABLE admin_document DROP type');
        }
        if ($this->columnExists('admin_document', 'sous_type')) {
            $this->addSql('ALTER TABLE admin_document DROP sous_type');
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
