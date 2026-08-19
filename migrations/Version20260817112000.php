<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260817112000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la table reunion_partenaire pour les partenaires de réunions (nom et logo)';
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('reunion_partenaire')) {
            $this->addSql(<<<'SQL'
                CREATE TABLE reunion_partenaire (
                    id INT AUTO_INCREMENT NOT NULL,
                    nom VARCHAR(255) DEFAULT NULL,
                    logo_id INT DEFAULT NULL,
                    reunion_id INT NOT NULL,
                    INDEX IDX_REUNION_PARTENAIRE_LOGO (logo_id),
                    INDEX IDX_REUNION_PARTENAIRE_REUNION (reunion_id),
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
            $this->addSql('ALTER TABLE reunion_partenaire ADD CONSTRAINT FK_RP_LOGO FOREIGN KEY (logo_id) REFERENCES param_fichier (id) ON DELETE SET NULL');
            $this->addSql('ALTER TABLE reunion_partenaire ADD CONSTRAINT FK_RP_REUNION FOREIGN KEY (reunion_id) REFERENCES reunion (id) ON DELETE CASCADE');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->tableExists('reunion_partenaire')) {
            $this->addSql('DROP TABLE reunion_partenaire');
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
