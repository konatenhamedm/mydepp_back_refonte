<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Générée initialement par doctrine:migrations:diff directement sur le serveur.
 *
 * Le fichier original contenait, en plus des étapes ci-dessous, une cinquantaine
 * d'instructions `CHANGE colonne colonne MEME_TYPE` sur quasiment toutes les tables
 * (simple re-déclaration de métadonnées Doctrine, sans effet réel sur le schéma).
 * Elles ont été retirées ici : elles sont sans intérêt fonctionnel et le texte récupéré
 * depuis le terminal contenait de nombreuses coupures d'espaces (ex: "NOTNULL",
 * "CHANGEupdated_at") dues au retour à la ligne du terminal, trop risqué à retranscrire
 * fidèlement à la main. Seules les étapes qui modifient réellement le schéma sont
 * conservées, et protégées par des vérifications d'existence.
 */
final class Version20260703015713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée actualite/evenement et ajoute type/sous_type à admin_document (généré via diff serveur, nettoyé)';
    }

    public function up(Schema $schema): void
    {
        if (!$this->tableExists('actualite')) {
            $this->addSql('CREATE TABLE actualite (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, commune_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, hashtag VARCHAR(255) DEFAULT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, lien VARCHAR(500) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_549281973DA5256D (image_id), INDEX IDX_54928197131A4F72 (commune_id), INDEX IDX_54928197B03A8386 (created_by_id), INDEX IDX_54928197896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE actualite ADD CONSTRAINT FK_549281973DA5256D FOREIGN KEY (image_id) REFERENCES param_fichier (id)');
            $this->addSql('ALTER TABLE actualite ADD CONSTRAINT FK_54928197131A4F72 FOREIGN KEY (commune_id) REFERENCES commune (id)');
            $this->addSql('ALTER TABLE actualite ADD CONSTRAINT FK_54928197B03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE actualite ADD CONSTRAINT FK_54928197896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        if (!$this->tableExists('evenement')) {
            $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, lien VARCHAR(500) DEFAULT NULL, type VARCHAR(50) NOT NULL, titre VARCHAR(255) NOT NULL, date_evenement DATETIME DEFAULT NULL, texte LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B26681E3DA5256D (image_id), INDEX IDX_B26681EB03A8386 (created_by_id), INDEX IDX_B26681E896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E3DA5256D FOREIGN KEY (image_id) REFERENCES param_fichier (id)');
            $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EB03A8386 FOREIGN KEY (created_by_id) REFERENCES utilisateur (id)');
            $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E896DBBDE FOREIGN KEY (updated_by_id) REFERENCES utilisateur (id)');
        }

        if (!$this->columnExists('admin_document', 'type')) {
            $this->addSql('ALTER TABLE admin_document ADD type VARCHAR(255) DEFAULT NULL');
        }
        if (!$this->columnExists('admin_document', 'sous_type')) {
            $this->addSql('ALTER TABLE admin_document ADD sous_type VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('admin_document', 'sous_type')) {
            $this->addSql('ALTER TABLE admin_document DROP sous_type');
        }
        if ($this->columnExists('admin_document', 'type')) {
            $this->addSql('ALTER TABLE admin_document DROP type');
        }

        if ($this->tableExists('evenement')) {
            $this->addSql('DROP TABLE evenement');
        }
        if ($this->tableExists('actualite')) {
            $this->addSql('DROP TABLE actualite');
        }
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table]
        );
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );
    }
}
