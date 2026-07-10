<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute statut_juridique, niveau_formation, responsabilite_medicolegale et les champs Personne Physique/Morale, Représentant et Responsable médicolégal sur membre_etablissement";
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('statut_juridique')) {
            $this->addSql('CREATE TABLE statut_juridique (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_STAT_JUR_CREATED_BY (created_by_id), INDEX IDX_STAT_JUR_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE statut_juridique ADD CONSTRAINT FK_STAT_JUR_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE statut_juridique ADD CONSTRAINT FK_STAT_JUR_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        if (!$this->tableExists('niveau_formation')) {
            $this->addSql('CREATE TABLE niveau_formation (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_NIV_FORM_CREATED_BY (created_by_id), INDEX IDX_NIV_FORM_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE niveau_formation ADD CONSTRAINT FK_NIV_FORM_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE niveau_formation ADD CONSTRAINT FK_NIV_FORM_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        if (!$this->tableExists('responsabilite_medicolegale')) {
            $this->addSql('CREATE TABLE responsabilite_medicolegale (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_RESP_MED_CREATED_BY (created_by_id), INDEX IDX_RESP_MED_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE responsabilite_medicolegale ADD CONSTRAINT FK_RESP_MED_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE responsabilite_medicolegale ADD CONSTRAINT FK_RESP_MED_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        $fkColumns = [
            'statut_juridique_id' => ['statut_juridique', 'FK_ETAB_STAT_JUR', 'IDX_ETAB_STAT_JUR'],
            'civilite_id' => ['civilite', 'FK_ETAB_CIVILITE', 'IDX_ETAB_CIVILITE'],
            'profession_id' => ['profession', 'FK_ETAB_PROFESSION', 'IDX_ETAB_PROFESSION'],
            'representant_civilite_id' => ['civilite', 'FK_ETAB_REP_CIVILITE', 'IDX_ETAB_REP_CIVILITE'],
            'responsable_civilite_id' => ['civilite', 'FK_ETAB_RESP_CIVILITE', 'IDX_ETAB_RESP_CIVILITE'],
            'responsabilite_medicolegale_id' => ['responsabilite_medicolegale', 'FK_ETAB_RESP_MED', 'IDX_ETAB_RESP_MED'],
            'responsable_niveau_formation_id' => ['niveau_formation', 'FK_ETAB_RESP_NIV_FORM', 'IDX_ETAB_RESP_NIV_FORM'],
            'responsable_statut_administratif_id' => ['status_pro', 'FK_ETAB_RESP_STAT_ADM', 'IDX_ETAB_RESP_STAT_ADM'],
        ];

        foreach ($fkColumns as $column => [$refTable, $fkName, $idxName]) {
            if (!$this->columnExists('membre_etablissement', $column)) {
                $this->addSql("ALTER TABLE membre_etablissement ADD {$column} INT DEFAULT NULL");
                $this->addSql("ALTER TABLE membre_etablissement ADD CONSTRAINT {$fkName} FOREIGN KEY ({$column}) REFERENCES {$refTable} (id)");
                $this->addSql("CREATE INDEX {$idxName} ON membre_etablissement ({$column})");
            }
        }

        $stringColumns = [
            'cni_numero',
            'whatsapp_personnel',
            'representant_qualite',
            'representant_cni',
            'representant_telephone',
            'representant_whatsapp',
            'representant_email',
            'responsable_nom',
            'responsable_profession',
            'responsable_diplome',
            'responsable_specialite',
            'responsable_email',
            'responsable_telephone',
            'responsable_whatsapp',
            'responsable_numero_ordre',
            'responsable_cni',
        ];

        foreach ($stringColumns as $column) {
            if (!$this->columnExists('membre_etablissement', $column)) {
                $this->addSql("ALTER TABLE membre_etablissement ADD {$column} VARCHAR(255) DEFAULT NULL");
            }
        }
    }

    public function down(Schema $schema): void
    {
        $stringColumns = [
            'cni_numero',
            'whatsapp_personnel',
            'representant_qualite',
            'representant_cni',
            'representant_telephone',
            'representant_whatsapp',
            'representant_email',
            'responsable_nom',
            'responsable_profession',
            'responsable_diplome',
            'responsable_specialite',
            'responsable_email',
            'responsable_telephone',
            'responsable_whatsapp',
            'responsable_numero_ordre',
            'responsable_cni',
        ];

        foreach ($stringColumns as $column) {
            if ($this->columnExists('membre_etablissement', $column)) {
                $this->addSql("ALTER TABLE membre_etablissement DROP {$column}");
            }
        }

        $fkColumns = [
            'statut_juridique_id' => 'FK_ETAB_STAT_JUR',
            'civilite_id' => 'FK_ETAB_CIVILITE',
            'profession_id' => 'FK_ETAB_PROFESSION',
            'representant_civilite_id' => 'FK_ETAB_REP_CIVILITE',
            'responsable_civilite_id' => 'FK_ETAB_RESP_CIVILITE',
            'responsabilite_medicolegale_id' => 'FK_ETAB_RESP_MED',
            'responsable_niveau_formation_id' => 'FK_ETAB_RESP_NIV_FORM',
            'responsable_statut_administratif_id' => 'FK_ETAB_RESP_STAT_ADM',
        ];

        foreach ($fkColumns as $column => $fkName) {
            if ($this->columnExists('membre_etablissement', $column)) {
                $this->addSql("ALTER TABLE membre_etablissement DROP FOREIGN KEY {$fkName}");
                $this->addSql("ALTER TABLE membre_etablissement DROP {$column}");
            }
        }

        if ($this->tableExists('responsabilite_medicolegale')) {
            $this->addSql('ALTER TABLE responsabilite_medicolegale DROP FOREIGN KEY FK_RESP_MED_CREATED_BY');
            $this->addSql('ALTER TABLE responsabilite_medicolegale DROP FOREIGN KEY FK_RESP_MED_UPDATED_BY');
            $this->addSql('DROP TABLE responsabilite_medicolegale');
        }
        if ($this->tableExists('niveau_formation')) {
            $this->addSql('ALTER TABLE niveau_formation DROP FOREIGN KEY FK_NIV_FORM_CREATED_BY');
            $this->addSql('ALTER TABLE niveau_formation DROP FOREIGN KEY FK_NIV_FORM_UPDATED_BY');
            $this->addSql('DROP TABLE niveau_formation');
        }
        if ($this->tableExists('statut_juridique')) {
            $this->addSql('ALTER TABLE statut_juridique DROP FOREIGN KEY FK_STAT_JUR_CREATED_BY');
            $this->addSql('ALTER TABLE statut_juridique DROP FOREIGN KEY FK_STAT_JUR_UPDATED_BY');
            $this->addSql('DROP TABLE statut_juridique');
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
