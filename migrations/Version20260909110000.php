<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260909110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le workflow de validation/annulation (statut, traite_par_nom, traite_at, motif_annulation) sur retrait';
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('retrait', 'statut')) {
            $this->addSql("ALTER TABLE retrait ADD statut VARCHAR(20) NOT NULL DEFAULT 'en_attente'");
        }
        if (!$this->columnExists('retrait', 'traite_par_nom')) {
            $this->addSql('ALTER TABLE retrait ADD traite_par_nom VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('retrait', 'traite_at')) {
            $this->addSql("ALTER TABLE retrait ADD traite_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
        }
        if (!$this->columnExists('retrait', 'motif_annulation')) {
            $this->addSql('ALTER TABLE retrait ADD motif_annulation LONGTEXT DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('retrait', 'motif_annulation')) {
            $this->addSql('ALTER TABLE retrait DROP COLUMN motif_annulation');
        }
        if ($this->columnExists('retrait', 'traite_at')) {
            $this->addSql('ALTER TABLE retrait DROP COLUMN traite_at');
        }
        if ($this->columnExists('retrait', 'traite_par_nom')) {
            $this->addSql('ALTER TABLE retrait DROP COLUMN traite_par_nom');
        }
        if ($this->columnExists('retrait', 'statut')) {
            $this->addSql('ALTER TABLE retrait DROP COLUMN statut');
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
