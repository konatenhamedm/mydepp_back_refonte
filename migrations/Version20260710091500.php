<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710091500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute la table de liaison type_document_niveau_intervention (ManyToMany) : un type de document peut être restreint à un ou plusieurs niveaux d'intervention (vide = tous les niveaux)";
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('type_document_niveau_intervention')) {
            $this->addSql(<<<'SQL'
                CREATE TABLE type_document_niveau_intervention (
                    type_document_id INT NOT NULL,
                    niveau_intervention_id INT NOT NULL,
                    INDEX IDX_TDNI_TYPE_DOCUMENT (type_document_id),
                    INDEX IDX_TDNI_NIVEAU (niveau_intervention_id),
                    PRIMARY KEY(type_document_id, niveau_intervention_id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
            $this->addSql('ALTER TABLE type_document_niveau_intervention ADD CONSTRAINT FK_TDNI_TYPE_DOCUMENT FOREIGN KEY (type_document_id) REFERENCES type_document (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE type_document_niveau_intervention ADD CONSTRAINT FK_TDNI_NIVEAU FOREIGN KEY (niveau_intervention_id) REFERENCES niveau_intervention (id) ON DELETE CASCADE');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->tableExists('type_document_niveau_intervention')) {
            $this->addSql('DROP TABLE type_document_niveau_intervention');
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
