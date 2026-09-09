<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260909100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table retrait (demandes de retrait comptable)';
    }

    public function up(Schema $schema): void
    {
        if ($this->tableExists('retrait')) {
            $this->write('Table retrait déjà existante — étape ignorée.');
            return;
        }

        $this->addSql(<<<'SQL'
            CREATE TABLE retrait (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                created_by_id INT DEFAULT NULL,
                updated_by_id INT DEFAULT NULL,
                montant VARCHAR(255) NOT NULL,
                telephone VARCHAR(50) NOT NULL,
                demande_par VARCHAR(255) DEFAULT NULL,
                created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX IDX_RETRAIT_USER (user_id),
                INDEX IDX_RETRAIT_CREATED_BY (created_by_id),
                INDEX IDX_RETRAIT_UPDATED_BY (updated_by_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql('ALTER TABLE retrait ADD CONSTRAINT FK_RETRAIT_USER FOREIGN KEY (user_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE retrait ADD CONSTRAINT FK_RETRAIT_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE retrait ADD CONSTRAINT FK_RETRAIT_UPDATED_BY FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        if ($this->tableExists('retrait')) {
            $this->addSql('DROP TABLE retrait');
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
