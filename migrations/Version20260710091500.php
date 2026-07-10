<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710091500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute la table de liaison niveau_intervention_libelle_groupe (ManyToMany) reliant les niveaux d'intervention aux groupes de documents requis";
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('niveau_intervention_libelle_groupe')) {
            $this->addSql(<<<'SQL'
                CREATE TABLE niveau_intervention_libelle_groupe (
                    niveau_intervention_id INT NOT NULL,
                    libelle_groupe_id INT NOT NULL,
                    INDEX IDX_NILG_NIVEAU (niveau_intervention_id),
                    INDEX IDX_NILG_LIBELLE_GROUPE (libelle_groupe_id),
                    PRIMARY KEY(niveau_intervention_id, libelle_groupe_id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
            $this->addSql('ALTER TABLE niveau_intervention_libelle_groupe ADD CONSTRAINT FK_NILG_NIVEAU FOREIGN KEY (niveau_intervention_id) REFERENCES niveau_intervention (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE niveau_intervention_libelle_groupe ADD CONSTRAINT FK_NILG_LIBELLE_GROUPE FOREIGN KEY (libelle_groupe_id) REFERENCES libelle_groupe (id) ON DELETE CASCADE');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->tableExists('niveau_intervention_libelle_groupe')) {
            $this->addSql('DROP TABLE niveau_intervention_libelle_groupe');
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
