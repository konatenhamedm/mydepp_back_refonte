<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260701072404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE autre_document_professionnel (id INT AUTO_INCREMENT NOT NULL, document_id INT DEFAULT NULL, professionnel_id INT DEFAULT NULL, type_autre_document_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2A72E9FFC33F7837 (document_id), INDEX IDX_2A72E9FF8A49CC82 (professionnel_id), INDEX IDX_2A72E9FF5608B2A8 (type_autre_document_id), INDEX IDX_2A72E9FFB03A8386 (created_by_id), INDEX IDX_2A72E9FF896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE presence (id INT AUTO_INCREMENT NOT NULL, reunion_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, nom_prenoms VARCHAR(255) NOT NULL, structure VARCHAR(255) DEFAULT NULL, fonction VARCHAR(255) DEFAULT NULL, telephone VARCHAR(50) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, signature LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6977C7A54E9B7368 (reunion_id), INDEX IDX_6977C7A5B03A8386 (created_by_id), INDEX IDX_6977C7A5896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reunion (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, objet VARCHAR(255) NOT NULL, token VARCHAR(64) DEFAULT NULL, type VARCHAR(50) DEFAULT \'presentiel\' NOT NULL, lien VARCHAR(255) DEFAULT NULL, jour DATE DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_5B00A4825F37A13B (token), INDEX IDX_5B00A482B03A8386 (created_by_id), INDEX IDX_5B00A482896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_autre_document (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_971DB4AEB03A8386 (created_by_id), INDEX IDX_971DB4AE896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE autre_document_professionnel ADD CONSTRAINT FK_2A72E9FFC33F7837 FOREIGN KEY (document_id) REFERENCES param_fichier (id)');
        $this->addSql('ALTER TABLE autre_document_professionnel ADD CONSTRAINT FK_2A72E9FF8A49CC82 FOREIGN KEY (professionnel_id) REFERENCES membre_professionnel (id)');
        $this->addSql('ALTER TABLE autre_document_professionnel ADD CONSTRAINT FK_2A72E9FF5608B2A8 FOREIGN KEY (type_autre_document_id) REFERENCES type_autre_document (id)');
        $this->addSql('ALTER TABLE autre_document_professionnel ADD CONSTRAINT FK_2A72E9FFB03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE autre_document_professionnel ADD CONSTRAINT FK_2A72E9FF896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE presence ADD CONSTRAINT FK_6977C7A54E9B7368 FOREIGN KEY (reunion_id) REFERENCES reunion (id)');
        $this->addSql('ALTER TABLE presence ADD CONSTRAINT FK_6977C7A5B03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE presence ADD CONSTRAINT FK_6977C7A5896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reunion ADD CONSTRAINT FK_5B00A482B03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reunion ADD CONSTRAINT FK_5B00A482896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE type_autre_document ADD CONSTRAINT FK_971DB4AEB03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE type_autre_document ADD CONSTRAINT FK_971DB4AE896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE membre_professionnel DROP FOREIGN KEY FK_5D832F3D9732555');
        $this->addSql('DROP INDEX IDX_5D832F3D9732555 ON membre_professionnel');
        $this->addSql('ALTER TABLE membre_professionnel ADD lieu_obtention_diplome VARCHAR(255) DEFAULT NULL, CHANGE lieu_obtention_diplome_id origine_diplome_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE membre_professionnel ADD CONSTRAINT FK_5D832F3DB3799188 FOREIGN KEY (origine_diplome_id) REFERENCES lieu_diplome (id)');
        $this->addSql('CREATE INDEX IDX_5D832F3DB3799188 ON membre_professionnel (origine_diplome_id)');
        $this->addSql('ALTER TABLE temp_professionnel ADD origine_diplome VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE autre_document_professionnel DROP FOREIGN KEY FK_2A72E9FFC33F7837');
        $this->addSql('ALTER TABLE autre_document_professionnel DROP FOREIGN KEY FK_2A72E9FF8A49CC82');
        $this->addSql('ALTER TABLE autre_document_professionnel DROP FOREIGN KEY FK_2A72E9FF5608B2A8');
        $this->addSql('ALTER TABLE autre_document_professionnel DROP FOREIGN KEY FK_2A72E9FFB03A8386');
        $this->addSql('ALTER TABLE autre_document_professionnel DROP FOREIGN KEY FK_2A72E9FF896DBBDE');
        $this->addSql('ALTER TABLE presence DROP FOREIGN KEY FK_6977C7A54E9B7368');
        $this->addSql('ALTER TABLE presence DROP FOREIGN KEY FK_6977C7A5B03A8386');
        $this->addSql('ALTER TABLE presence DROP FOREIGN KEY FK_6977C7A5896DBBDE');
        $this->addSql('ALTER TABLE reunion DROP FOREIGN KEY FK_5B00A482B03A8386');
        $this->addSql('ALTER TABLE reunion DROP FOREIGN KEY FK_5B00A482896DBBDE');
        $this->addSql('ALTER TABLE type_autre_document DROP FOREIGN KEY FK_971DB4AEB03A8386');
        $this->addSql('ALTER TABLE type_autre_document DROP FOREIGN KEY FK_971DB4AE896DBBDE');
        $this->addSql('DROP TABLE autre_document_professionnel');
        $this->addSql('DROP TABLE presence');
        $this->addSql('DROP TABLE reunion');
        $this->addSql('DROP TABLE type_autre_document');
        $this->addSql('ALTER TABLE temp_professionnel DROP origine_diplome');
        $this->addSql('ALTER TABLE membre_professionnel DROP FOREIGN KEY FK_5D832F3DB3799188');
        $this->addSql('DROP INDEX IDX_5D832F3DB3799188 ON membre_professionnel');
        $this->addSql('ALTER TABLE membre_professionnel DROP lieu_obtention_diplome, CHANGE origine_diplome_id lieu_obtention_diplome_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE membre_professionnel ADD CONSTRAINT FK_5D832F3D9732555 FOREIGN KEY (lieu_obtention_diplome_id) REFERENCES lieu_diplome (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5D832F3D9732555 ON membre_professionnel (lieu_obtention_diplome_id)');
    }
}
