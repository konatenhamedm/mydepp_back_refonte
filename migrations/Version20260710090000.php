<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute la colonne obligatoire (le document est-il requis ou optionnel) sur type_document";
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('type_document', 'obligatoire')) {
            $this->addSql("ALTER TABLE type_document ADD obligatoire TINYINT(1) NOT NULL DEFAULT 1");
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('type_document', 'obligatoire')) {
            $this->addSql('ALTER TABLE type_document DROP obligatoire');
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
