<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260702120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les colonnes type et sous_type à admin_document (classification de la documenthèque)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE admin_document ADD type VARCHAR(255) DEFAULT NULL, ADD sous_type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE admin_document DROP type, DROP sous_type');
    }
}
