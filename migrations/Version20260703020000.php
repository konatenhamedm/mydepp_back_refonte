<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260703020000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée les tables actualite et evenement (module Communication)';
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('actualite')) {
            $this->addSql('CREATE TABLE actualite (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, commune_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, hashtag VARCHAR(255) DEFAULT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, lien VARCHAR(500) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_ACTUALITE_IMAGE (image_id), INDEX IDX_ACTUALITE_COMMUNE (commune_id), INDEX IDX_ACTUALITE_CREATED_BY (created_by_id), INDEX IDX_ACTUALITE_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE actualite ADD CONSTRAINT FK_ACTUALITE_IMAGE FOREIGN KEY (image_id) REFERENCES param_fichier (id)');
            $this->addSql('ALTER TABLE actualite ADD CONSTRAINT FK_ACTUALITE_COMMUNE FOREIGN KEY (commune_id) REFERENCES commune (id)');
            $this->addSql('ALTER TABLE actualite ADD CONSTRAINT FK_ACTUALITE_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE actualite ADD CONSTRAINT FK_ACTUALITE_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        if (!$this->tableExists('evenement')) {
            $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, lien VARCHAR(500) DEFAULT NULL, type VARCHAR(50) NOT NULL, titre VARCHAR(255) NOT NULL, date_evenement DATETIME DEFAULT NULL, texte LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_EVENEMENT_IMAGE (image_id), INDEX IDX_EVENEMENT_CREATED_BY (created_by_id), INDEX IDX_EVENEMENT_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_EVENEMENT_IMAGE FOREIGN KEY (image_id) REFERENCES param_fichier (id)');
            $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_EVENEMENT_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_EVENEMENT_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->tableExists('evenement')) {
            $this->addSql('DROP TABLE evenement');
        }
        if ($this->tableExists('actualite')) {
            $this->addSql('DROP TABLE actualite');
        }
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table]
        );
    }
}
