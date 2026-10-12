<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Plaques d'immatriculation : position (latitude / longitude) corrigée sur la carte";
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists('plaque_immatriculation', 'latitude')) {
            $this->addSql('ALTER TABLE plaque_immatriculation ADD latitude DOUBLE PRECISION DEFAULT NULL');
        }
        if (!$this->columnExists('plaque_immatriculation', 'longitude')) {
            $this->addSql('ALTER TABLE plaque_immatriculation ADD longitude DOUBLE PRECISION DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('plaque_immatriculation', 'longitude')) {
            $this->addSql('ALTER TABLE plaque_immatriculation DROP COLUMN longitude');
        }
        if ($this->columnExists('plaque_immatriculation', 'latitude')) {
            $this->addSql('ALTER TABLE plaque_immatriculation DROP COLUMN latitude');
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );
    }
}
