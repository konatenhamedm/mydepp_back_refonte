<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute les listes de référence type_etablissement, nature_etablissement, type_organisation et les champs Structure / Adresses et Contacts sur membre_etablissement";
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('type_etablissement')) {
            $this->addSql('CREATE TABLE type_etablissement (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_TYPE_ETAB_CREATED_BY (created_by_id), INDEX IDX_TYPE_ETAB_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE type_etablissement ADD CONSTRAINT FK_TYPE_ETAB_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE type_etablissement ADD CONSTRAINT FK_TYPE_ETAB_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        if (!$this->tableExists('nature_etablissement')) {
            $this->addSql('CREATE TABLE nature_etablissement (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_NATURE_ETAB_CREATED_BY (created_by_id), INDEX IDX_NATURE_ETAB_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE nature_etablissement ADD CONSTRAINT FK_NATURE_ETAB_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE nature_etablissement ADD CONSTRAINT FK_NATURE_ETAB_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        if (!$this->tableExists('type_organisation')) {
            $this->addSql('CREATE TABLE type_organisation (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_TYPE_ORG_CREATED_BY (created_by_id), INDEX IDX_TYPE_ORG_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE type_organisation ADD CONSTRAINT FK_TYPE_ORG_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE type_organisation ADD CONSTRAINT FK_TYPE_ORG_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        // Section "Structure" complémentaire
        if (!$this->columnExists('membre_etablissement', 'type_etablissement_id')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD type_etablissement_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE membre_etablissement ADD CONSTRAINT FK_ETAB_TYPE_ETAB FOREIGN KEY (type_etablissement_id) REFERENCES type_etablissement (id)');
            $this->addSql('CREATE INDEX IDX_ETAB_TYPE_ETAB ON membre_etablissement (type_etablissement_id)');
        }
        if (!$this->columnExists('membre_etablissement', 'nature_etablissement_id')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD nature_etablissement_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE membre_etablissement ADD CONSTRAINT FK_ETAB_NATURE_ETAB FOREIGN KEY (nature_etablissement_id) REFERENCES nature_etablissement (id)');
            $this->addSql('CREATE INDEX IDX_ETAB_NATURE_ETAB ON membre_etablissement (nature_etablissement_id)');
        }
        if (!$this->columnExists('membre_etablissement', 'type_organisation_id')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD type_organisation_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE membre_etablissement ADD CONSTRAINT FK_ETAB_TYPE_ORG FOREIGN KEY (type_organisation_id) REFERENCES type_organisation (id)');
            $this->addSql('CREATE INDEX IDX_ETAB_TYPE_ORG ON membre_etablissement (type_organisation_id)');
        }
        if (!$this->columnExists('membre_etablissement', 'accord_ministere')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD accord_ministere TINYINT(1) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'date_validite_accord')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD date_validite_accord DATETIME DEFAULT NULL');
        }

        // Section "Adresses et Contacts"
        if (!$this->columnExists('membre_etablissement', 'region_id')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD region_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE membre_etablissement ADD CONSTRAINT FK_ETAB_REGION FOREIGN KEY (region_id) REFERENCES region (id)');
            $this->addSql('CREATE INDEX IDX_ETAB_REGION ON membre_etablissement (region_id)');
        }
        if (!$this->columnExists('membre_etablissement', 'district_id')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD district_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE membre_etablissement ADD CONSTRAINT FK_ETAB_DISTRICT FOREIGN KEY (district_id) REFERENCES district (id)');
            $this->addSql('CREATE INDEX IDX_ETAB_DISTRICT ON membre_etablissement (district_id)');
        }
        if (!$this->columnExists('membre_etablissement', 'ville_village')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD ville_village VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'commune')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD commune VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'quartier')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD quartier VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'zone_secteur')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD zone_secteur VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'villa_immeuble_etage_porte')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD villa_immeuble_etage_porte VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'ilot_numero')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD ilot_numero VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'lot_numero')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD lot_numero VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'rue_avenue')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD rue_avenue VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'point_de_repere')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD point_de_repere VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'adresse_electronique')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD adresse_electronique VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'telephone_fixe')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD telephone_fixe VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'whatsapp')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD whatsapp VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'telephone_mobile')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD telephone_mobile VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'telephone_autre')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD telephone_autre VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'adresse_postale')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD adresse_postale VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('membre_etablissement', 'adresse_postale')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP adresse_postale');
        }
        if ($this->columnExists('membre_etablissement', 'telephone_autre')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP telephone_autre');
        }
        if ($this->columnExists('membre_etablissement', 'telephone_mobile')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP telephone_mobile');
        }
        if ($this->columnExists('membre_etablissement', 'whatsapp')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP whatsapp');
        }
        if ($this->columnExists('membre_etablissement', 'telephone_fixe')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP telephone_fixe');
        }
        if ($this->columnExists('membre_etablissement', 'adresse_electronique')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP adresse_electronique');
        }
        if ($this->columnExists('membre_etablissement', 'point_de_repere')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP point_de_repere');
        }
        if ($this->columnExists('membre_etablissement', 'rue_avenue')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP rue_avenue');
        }
        if ($this->columnExists('membre_etablissement', 'lot_numero')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP lot_numero');
        }
        if ($this->columnExists('membre_etablissement', 'ilot_numero')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP ilot_numero');
        }
        if ($this->columnExists('membre_etablissement', 'villa_immeuble_etage_porte')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP villa_immeuble_etage_porte');
        }
        if ($this->columnExists('membre_etablissement', 'zone_secteur')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP zone_secteur');
        }
        if ($this->columnExists('membre_etablissement', 'quartier')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP quartier');
        }
        if ($this->columnExists('membre_etablissement', 'commune')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP commune');
        }
        if ($this->columnExists('membre_etablissement', 'ville_village')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP ville_village');
        }
        if ($this->columnExists('membre_etablissement', 'district_id')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP FOREIGN KEY FK_ETAB_DISTRICT');
            $this->addSql('DROP INDEX IDX_ETAB_DISTRICT ON membre_etablissement');
            $this->addSql('ALTER TABLE membre_etablissement DROP district_id');
        }
        if ($this->columnExists('membre_etablissement', 'region_id')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP FOREIGN KEY FK_ETAB_REGION');
            $this->addSql('DROP INDEX IDX_ETAB_REGION ON membre_etablissement');
            $this->addSql('ALTER TABLE membre_etablissement DROP region_id');
        }
        if ($this->columnExists('membre_etablissement', 'date_validite_accord')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP date_validite_accord');
        }
        if ($this->columnExists('membre_etablissement', 'accord_ministere')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP accord_ministere');
        }
        if ($this->columnExists('membre_etablissement', 'type_organisation_id')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP FOREIGN KEY FK_ETAB_TYPE_ORG');
            $this->addSql('DROP INDEX IDX_ETAB_TYPE_ORG ON membre_etablissement');
            $this->addSql('ALTER TABLE membre_etablissement DROP type_organisation_id');
        }
        if ($this->columnExists('membre_etablissement', 'nature_etablissement_id')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP FOREIGN KEY FK_ETAB_NATURE_ETAB');
            $this->addSql('DROP INDEX IDX_ETAB_NATURE_ETAB ON membre_etablissement');
            $this->addSql('ALTER TABLE membre_etablissement DROP nature_etablissement_id');
        }
        if ($this->columnExists('membre_etablissement', 'type_etablissement_id')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP FOREIGN KEY FK_ETAB_TYPE_ETAB');
            $this->addSql('DROP INDEX IDX_ETAB_TYPE_ETAB ON membre_etablissement');
            $this->addSql('ALTER TABLE membre_etablissement DROP type_etablissement_id');
        }

        if ($this->tableExists('type_organisation')) {
            $this->addSql('ALTER TABLE type_organisation DROP FOREIGN KEY FK_TYPE_ORG_CREATED_BY');
            $this->addSql('ALTER TABLE type_organisation DROP FOREIGN KEY FK_TYPE_ORG_UPDATED_BY');
            $this->addSql('DROP TABLE type_organisation');
        }
        if ($this->tableExists('nature_etablissement')) {
            $this->addSql('ALTER TABLE nature_etablissement DROP FOREIGN KEY FK_NATURE_ETAB_CREATED_BY');
            $this->addSql('ALTER TABLE nature_etablissement DROP FOREIGN KEY FK_NATURE_ETAB_UPDATED_BY');
            $this->addSql('DROP TABLE nature_etablissement');
        }
        if ($this->tableExists('type_etablissement')) {
            $this->addSql('ALTER TABLE type_etablissement DROP FOREIGN KEY FK_TYPE_ETAB_CREATED_BY');
            $this->addSql('ALTER TABLE type_etablissement DROP FOREIGN KEY FK_TYPE_ETAB_UPDATED_BY');
            $this->addSql('DROP TABLE type_etablissement');
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
