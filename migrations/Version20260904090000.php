<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260904090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne deleted_at sur reunion (soft delete)';
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('reunion', 'deleted_at')) {
            $this->addSql('ALTER TABLE reunion ADD deleted_at DATETIME DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('reunion', 'deleted_at')) {
            $this->addSql('ALTER TABLE reunion DROP COLUMN deleted_at');
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
