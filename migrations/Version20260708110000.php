<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute la liste de référence type_demande_etablissement et le champ correspondant sur membre_etablissement";
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('type_demande_etablissement')) {
            $this->addSql('CREATE TABLE type_demande_etablissement (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_TYPE_DEM_ETAB_CREATED_BY (created_by_id), INDEX IDX_TYPE_DEM_ETAB_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE type_demande_etablissement ADD CONSTRAINT FK_TYPE_DEM_ETAB_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE type_demande_etablissement ADD CONSTRAINT FK_TYPE_DEM_ETAB_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        if (!$this->columnExists('membre_etablissement', 'type_demande_etablissement_id')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD type_demande_etablissement_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE membre_etablissement ADD CONSTRAINT FK_ETAB_TYPE_DEM_ETAB FOREIGN KEY (type_demande_etablissement_id) REFERENCES type_demande_etablissement (id)');
            $this->addSql('CREATE INDEX IDX_ETAB_TYPE_DEM_ETAB ON membre_etablissement (type_demande_etablissement_id)');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('membre_etablissement', 'type_demande_etablissement_id')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP FOREIGN KEY FK_ETAB_TYPE_DEM_ETAB');
            $this->addSql('DROP INDEX IDX_ETAB_TYPE_DEM_ETAB ON membre_etablissement');
            $this->addSql('ALTER TABLE membre_etablissement DROP type_demande_etablissement_id');
        }

        if ($this->tableExists('type_demande_etablissement')) {
            $this->addSql('ALTER TABLE type_demande_etablissement DROP FOREIGN KEY FK_TYPE_DEM_ETAB_CREATED_BY');
            $this->addSql('ALTER TABLE type_demande_etablissement DROP FOREIGN KEY FK_TYPE_DEM_ETAB_UPDATED_BY');
            $this->addSql('DROP TABLE type_demande_etablissement');
        }
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table]
        );
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );
    }
}
