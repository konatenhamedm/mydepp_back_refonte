<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute la colonne fee (1% du montant) sur la table transaction";
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('transaction', 'fee')) {
            $this->addSql('ALTER TABLE transaction ADD fee VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('transaction', 'fee')) {
            $this->addSql('ALTER TABLE transaction DROP fee');
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
