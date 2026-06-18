<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add type / lien / jour columns to reunion.
 */
final class Version20260617000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add type, lien and jour columns to reunion';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE reunion ADD type VARCHAR(50) DEFAULT 'presentiel' NOT NULL");
        $this->addSql('ALTER TABLE reunion ADD lien VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reunion ADD jour DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reunion DROP type');
        $this->addSql('ALTER TABLE reunion DROP lien');
        $this->addSql('ALTER TABLE reunion DROP jour');
    }
}
