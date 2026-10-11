<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261011100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Espace Plaques d'immatriculation : tables plaque_immatriculation et plaque_parametre (code d'accès par défaut 12345678, haché)";
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('plaque_immatriculation')) {
            $this->addSql(<<<'SQL'
                CREATE TABLE plaque_immatriculation (
                    id INT AUTO_INCREMENT NOT NULL,
                    token VARCHAR(64) NOT NULL,
                    denomination VARCHAR(255) NOT NULL,
                    nature VARCHAR(255) DEFAULT NULL,
                    immatriculation VARCHAR(255) DEFAULT NULL,
                    numero_dossier VARCHAR(100) DEFAULT NULL,
                    localisation LONGTEXT DEFAULT NULL,
                    donnees_publiques LONGTEXT NOT NULL COLLATE `utf8mb4_bin`,
                    donnees_privees LONGTEXT NOT NULL COLLATE `utf8mb4_bin`,
                    signe_visibilite VARCHAR(255) DEFAULT NULL,
                    nom_fichier VARCHAR(255) DEFAULT NULL,
                    fichier_stocke VARCHAR(255) DEFAULT NULL,
                    created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                    updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                    created_by_id INT DEFAULT NULL,
                    updated_by_id INT DEFAULT NULL,
                    UNIQUE INDEX UNIQ_PLAQUE_TOKEN (token),
                    INDEX IDX_PLAQUE_IMMAT (immatriculation),
                    INDEX IDX_PLAQUE_CREATED_BY (created_by_id),
                    INDEX IDX_PLAQUE_UPDATED_BY (updated_by_id),
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
            $this->addSql('ALTER TABLE plaque_immatriculation ADD CONSTRAINT FK_PLAQUE_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
            $this->addSql('ALTER TABLE plaque_immatriculation ADD CONSTRAINT FK_PLAQUE_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        }

        if (!$this->tableExists('plaque_parametre')) {
            $this->addSql(<<<'SQL'
                CREATE TABLE plaque_parametre (
                    id INT AUTO_INCREMENT NOT NULL,
                    code_acces_hash VARCHAR(255) NOT NULL,
                    updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                    updated_by_nom VARCHAR(255) DEFAULT NULL,
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
            $this->addSql(
                'INSERT INTO plaque_parametre (code_acces_hash) VALUES (?)',
                [password_hash('12345678', PASSWORD_DEFAULT)]
            );
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->tableExists('plaque_parametre')) {
            $this->addSql('DROP TABLE plaque_parametre');
        }
        if ($this->tableExists('plaque_immatriculation')) {
            $this->addSql('DROP TABLE plaque_immatriculation');
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
