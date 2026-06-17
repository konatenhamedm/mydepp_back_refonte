<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add reunion.token (unique) and create presence table.
 */
final class Version20260617000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reunion.token and create presence table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reunion ADD token VARCHAR(64) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_REUNION_TOKEN ON reunion (token)');

        $this->addSql('CREATE TABLE presence (id INT AUTO_INCREMENT NOT NULL, reunion_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, nom_prenoms VARCHAR(255) NOT NULL, structure VARCHAR(255) DEFAULT NULL, fonction VARCHAR(255) DEFAULT NULL, telephone VARCHAR(50) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, signature LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_PRESENCE_REUNION (reunion_id), INDEX IDX_PRESENCE_CREATED_BY (created_by_id), INDEX IDX_PRESENCE_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE presence ADD CONSTRAINT FK_PRESENCE_REUNION FOREIGN KEY (reunion_id) REFERENCES reunion (id)');
        $this->addSql('ALTER TABLE presence ADD CONSTRAINT FK_PRESENCE_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE presence ADD CONSTRAINT FK_PRESENCE_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE presence');
        $this->addSql('DROP INDEX UNIQ_REUNION_TOKEN ON reunion');
        $this->addSql('ALTER TABLE reunion DROP token');
    }
}
