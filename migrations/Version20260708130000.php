<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute organisme_enregistrement et les champs d'enregistrement/certification/horaires sur membre_etablissement";
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('organisme_enregistrement')) {
            $this->addSql('CREATE TABLE organisme_enregistrement (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_ORG_ENR_CREATED_BY (created_by_id), INDEX IDX_ORG_ENR_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE organisme_enregistrement ADD CONSTRAINT FK_ORG_ENR_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE organisme_enregistrement ADD CONSTRAINT FK_ORG_ENR_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        if (!$this->columnExists('membre_etablissement', 'organisme_enregistrement_id')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD organisme_enregistrement_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE membre_etablissement ADD CONSTRAINT FK_ETAB_ORG_ENR FOREIGN KEY (organisme_enregistrement_id) REFERENCES organisme_enregistrement (id)');
            $this->addSql('CREATE INDEX IDX_ETAB_ORG_ENR ON membre_etablissement (organisme_enregistrement_id)');
        }

        if (!$this->columnExists('membre_etablissement', 'annee_creation')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD annee_creation VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'enregistree_depps')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD enregistree_depps TINYINT(1) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'numero_enregistrement')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD numero_enregistrement VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'annee_autorisation')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD annee_autorisation VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'a_certificat_conformite')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD a_certificat_conformite TINYINT(1) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'date_validite_certificat')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD date_validite_certificat DATETIME DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'horaire_ouverture')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD horaire_ouverture VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'autre_horaire_ouverture')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD autre_horaire_ouverture VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $stringColumns = [
            'autre_horaire_ouverture',
            'horaire_ouverture',
            'date_validite_certificat',
            'a_certificat_conformite',
            'annee_autorisation',
            'numero_enregistrement',
            'enregistree_depps',
            'annee_creation',
        ];

        foreach ($stringColumns as $column) {
            if ($this->columnExists('membre_etablissement', $column)) {
                $this->addSql("ALTER TABLE membre_etablissement DROP {$column}");
            }
        }

        if ($this->columnExists('membre_etablissement', 'organisme_enregistrement_id')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP FOREIGN KEY FK_ETAB_ORG_ENR');
            $this->addSql('DROP INDEX IDX_ETAB_ORG_ENR ON membre_etablissement');
            $this->addSql('ALTER TABLE membre_etablissement DROP organisme_enregistrement_id');
        }

        if ($this->tableExists('organisme_enregistrement')) {
            $this->addSql('ALTER TABLE organisme_enregistrement DROP FOREIGN KEY FK_ORG_ENR_CREATED_BY');
            $this->addSql('ALTER TABLE organisme_enregistrement DROP FOREIGN KEY FK_ORG_ENR_UPDATED_BY');
            $this->addSql('DROP TABLE organisme_enregistrement');
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
