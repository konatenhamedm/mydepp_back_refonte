<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create reunion table (admin meetings, field: objet).
 */
final class Version20260617000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create reunion table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reunion (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, objet VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_REUNION_CREATED_BY (created_by_id), INDEX IDX_REUNION_UPDATED_BY (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reunion ADD CONSTRAINT FK_REUNION_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reunion ADD CONSTRAINT FK_REUNION_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE reunion');
    }
}
