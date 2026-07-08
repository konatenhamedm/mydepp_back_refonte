<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute service, certification_qualite, la table de jointure etablissement_service et les champs de contrôle qualité sur membre_etablissement";
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('service')) {
            $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_SERVICE_CREATED_BY (created_by_id), INDEX IDX_SERVICE_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_SERVICE_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_SERVICE_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        if (!$this->tableExists('certification_qualite')) {
            $this->addSql('CREATE TABLE certification_qualite (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, code VARCHAR(10) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CERT_QUAL_CREATED_BY (created_by_id), INDEX IDX_CERT_QUAL_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE certification_qualite ADD CONSTRAINT FK_CERT_QUAL_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE certification_qualite ADD CONSTRAINT FK_CERT_QUAL_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        if (!$this->tableExists('etablissement_service')) {
            $this->addSql('CREATE TABLE etablissement_service (etablissement_id INT NOT NULL, service_id INT NOT NULL, INDEX IDX_ETAB_SERV_ETAB (etablissement_id), INDEX IDX_ETAB_SERV_SERVICE (service_id), PRIMARY KEY(etablissement_id, service_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE etablissement_service ADD CONSTRAINT FK_ETAB_SERV_ETAB FOREIGN KEY (etablissement_id) REFERENCES membre_etablissement (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE etablissement_service ADD CONSTRAINT FK_ETAB_SERV_SERVICE FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        }

        if (!$this->columnExists('membre_etablissement', 'a_accreditation')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD a_accreditation TINYINT(1) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'engagement_processus_accreditation')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD engagement_processus_accreditation TINYINT(1) DEFAULT NULL');
        }
        if (!$this->columnExists('membre_etablissement', 'certification_qualite_id')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD certification_qualite_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE membre_etablissement ADD CONSTRAINT FK_ETAB_CERT_QUAL FOREIGN KEY (certification_qualite_id) REFERENCES certification_qualite (id)');
            $this->addSql('CREATE INDEX IDX_ETAB_CERT_QUAL ON membre_etablissement (certification_qualite_id)');
        }
        if (!$this->columnExists('membre_etablissement', 'autres_certification')) {
            $this->addSql('ALTER TABLE membre_etablissement ADD autres_certification VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('membre_etablissement', 'autres_certification')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP autres_certification');
        }
        if ($this->columnExists('membre_etablissement', 'certification_qualite_id')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP FOREIGN KEY FK_ETAB_CERT_QUAL');
            $this->addSql('DROP INDEX IDX_ETAB_CERT_QUAL ON membre_etablissement');
            $this->addSql('ALTER TABLE membre_etablissement DROP certification_qualite_id');
        }
        if ($this->columnExists('membre_etablissement', 'engagement_processus_accreditation')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP engagement_processus_accreditation');
        }
        if ($this->columnExists('membre_etablissement', 'a_accreditation')) {
            $this->addSql('ALTER TABLE membre_etablissement DROP a_accreditation');
        }

        if ($this->tableExists('etablissement_service')) {
            $this->addSql('ALTER TABLE etablissement_service DROP FOREIGN KEY FK_ETAB_SERV_ETAB');
            $this->addSql('ALTER TABLE etablissement_service DROP FOREIGN KEY FK_ETAB_SERV_SERVICE');
            $this->addSql('DROP TABLE etablissement_service');
        }
        if ($this->tableExists('certification_qualite')) {
            $this->addSql('ALTER TABLE certification_qualite DROP FOREIGN KEY FK_CERT_QUAL_CREATED_BY');
            $this->addSql('ALTER TABLE certification_qualite DROP FOREIGN KEY FK_CERT_QUAL_UPDATED_BY');
            $this->addSql('DROP TABLE certification_qualite');
        }
        if ($this->tableExists('service')) {
            $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_SERVICE_CREATED_BY');
            $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_SERVICE_UPDATED_BY');
            $this->addSql('DROP TABLE service');
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
