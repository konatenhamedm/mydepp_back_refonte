<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add type / lien / jour columns to reunion.
 */
final class Version20260617000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add type, lien and jour columns to reunion';
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('reunion', 'type')) {
            $this->addSql("ALTER TABLE reunion ADD type VARCHAR(50) DEFAULT 'presentiel' NOT NULL");
        }
        if (!$this->columnExists('reunion', 'lien')) {
            $this->addSql('ALTER TABLE reunion ADD lien VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('reunion', 'jour')) {
            $this->addSql('ALTER TABLE reunion ADD jour DATE DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('reunion', 'type')) {
            $this->addSql('ALTER TABLE reunion DROP type');
        }
        if ($this->columnExists('reunion', 'lien')) {
            $this->addSql('ALTER TABLE reunion DROP lien');
        }
        if ($this->columnExists('reunion', 'jour')) {
            $this->addSql('ALTER TABLE reunion DROP jour');
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
