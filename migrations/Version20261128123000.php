<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261128123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Import ESPR autorisés DEPPS : élargit les colonnes d'adresse/dénomination et ajoute les champs officiels d'arrêté, date d'expiration, dossier, immatriculation, promoteur, responsable technique et géolocalisation sur membre_etablissement";
    }

    public function up(Schema $schema): void
    {
        $widen255 = [
            'nom',
            'prenoms',
            'telephone',
            'email',
            'bp',
            'type_societe',
            'denomination',
            'nom_representant',
            'email_autre',
            'code',
        ];

        foreach ($widen255 as $col) {
            if ($this->columnExists('membre_etablissement', $col)) {
                $this->addSql("ALTER TABLE membre_etablissement MODIFY {$col} VARCHAR(255) DEFAULT NULL");
            }
        }

        if ($this->columnExists('membre_etablissement', 'adresse')) {
            $this->addSql('ALTER TABLE membre_etablissement MODIFY adresse VARCHAR(500) DEFAULT NULL');
        }

        $newColumns = [
            'numero_dossier' => 'VARCHAR(100) DEFAULT NULL',
            'arrete_autorisation' => 'VARCHAR(255) DEFAULT NULL',
            'numero_arrete' => 'VARCHAR(100) DEFAULT NULL',
            'date_signature_arrete' => 'DATETIME DEFAULT NULL',
            'date_expiration_arrete' => 'DATETIME DEFAULT NULL',
            'immatriculation' => 'VARCHAR(100) DEFAULT NULL',
            'code_identifiant' => 'VARCHAR(100) DEFAULT NULL',
            'qualite_promoteur' => 'VARCHAR(100) DEFAULT NULL',
            'promoteur_proprietaire' => 'LONGTEXT DEFAULT NULL',
            'responsable_technique_sante' => 'LONGTEXT DEFAULT NULL',
            'responsable_sexe' => 'VARCHAR(30) DEFAULT NULL',
            'responsable_est_resident' => 'TINYINT(1) DEFAULT NULL',
            'lieu_geolocalisation' => 'VARCHAR(150) DEFAULT NULL',
            'precision_geolocalisation' => 'VARCHAR(50) DEFAULT NULL',
        ];

        foreach ($newColumns as $col => $definition) {
            if (!$this->columnExists('membre_etablissement', $col)) {
                $this->addSql("ALTER TABLE membre_etablissement ADD {$col} {$definition}");
            }
        }

        if (!$this->indexExists('membre_etablissement', 'IDX_ETAB_NUMERO_DOSSIER')) {
            $this->addSql('CREATE INDEX IDX_ETAB_NUMERO_DOSSIER ON membre_etablissement (numero_dossier)');
        }
        if (!$this->indexExists('membre_etablissement', 'IDX_ETAB_IMMATRICULATION')) {
            $this->addSql('CREATE INDEX IDX_ETAB_IMMATRICULATION ON membre_etablissement (immatriculation)');
        }
        if (!$this->indexExists('membre_etablissement', 'IDX_ETAB_DATE_EXP_ARRETE')) {
            $this->addSql('CREATE INDEX IDX_ETAB_DATE_EXP_ARRETE ON membre_etablissement (date_expiration_arrete)');
        }
    }

    public function down(Schema $schema): void
    {
        foreach (['IDX_ETAB_DATE_EXP_ARRETE', 'IDX_ETAB_IMMATRICULATION', 'IDX_ETAB_NUMERO_DOSSIER'] as $index) {
            if ($this->indexExists('membre_etablissement', $index)) {
                $this->addSql("DROP INDEX {$index} ON membre_etablissement");
            }
        }

        $dropColumns = [
            'precision_geolocalisation',
            'lieu_geolocalisation',
            'responsable_est_resident',
            'responsable_sexe',
            'responsable_technique_sante',
            'promoteur_proprietaire',
            'qualite_promoteur',
            'code_identifiant',
            'immatriculation',
            'date_expiration_arrete',
            'date_signature_arrete',
            'numero_arrete',
            'arrete_autorisation',
            'numero_dossier',
        ];

        foreach ($dropColumns as $col) {
            if ($this->columnExists('membre_etablissement', $col)) {
                $this->addSql("ALTER TABLE membre_etablissement DROP COLUMN {$col}");
            }
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );
    }

    private function indexExists(string $table, string $index): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $index]
        );
    }
}
