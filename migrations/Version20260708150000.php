<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute les colonnes du parcours complet (structure, adresses, représentant, responsable médicolégal, qualité/services) sur temp_etablissement afin que le flux paiement-puis-création conserve toutes les données du formulaire";
    }

    private const COLUMNS = [
        'type_demande_etablissement',
        'type_etablissement',
        'nature_etablissement',
        'type_organisation',
        'accord_ministere',
        'date_validite_accord',
        'region',
        'district',
        'ville_village',
        'commune',
        'quartier',
        'zone_secteur',
        'villa_immeuble_etage_porte',
        'ilot_numero',
        'lot_numero',
        'rue_avenue',
        'point_de_repere',
        'adresse_electronique',
        'telephone_fixe',
        'whatsapp',
        'telephone_mobile',
        'telephone_autre',
        'adresse_postale',
        'civilite',
        'profession',
        'cni_numero',
        'whatsapp_personnel',
        'statut_juridique',
        'representant_civilite',
        'representant_qualite',
        'representant_cni',
        'representant_telephone',
        'representant_whatsapp',
        'representant_email',
        'responsable_civilite',
        'responsable_nom',
        'responsabilite_medicolegale',
        'responsable_profession',
        'responsable_diplome',
        'responsable_specialite',
        'responsable_niveau_formation',
        'responsable_statut_administratif',
        'responsable_email',
        'responsable_telephone',
        'responsable_whatsapp',
        'responsable_numero_ordre',
        'responsable_cni',
        'annee_creation',
        'enregistree_depps',
        'numero_enregistrement',
        'organisme_enregistrement',
        'annee_autorisation',
        'a_certificat_conformite',
        'date_validite_certificat',
        'horaire_ouverture',
        'autre_horaire_ouverture',
        'a_accreditation',
        'engagement_processus_accreditation',
        'certification_qualite',
        'autres_certification',
    ];

    public function up(Schema $schema): void
    {
        foreach (self::COLUMNS as $column) {
            if (!$this->columnExists('temp_etablissement', $column)) {
                $this->addSql("ALTER TABLE temp_etablissement ADD {$column} VARCHAR(255) DEFAULT NULL");
            }
        }
        if (!$this->columnExists('temp_etablissement', 'services')) {
            $this->addSql('ALTER TABLE temp_etablissement ADD services LONGTEXT DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->columnExists('temp_etablissement', 'services')) {
            $this->addSql('ALTER TABLE temp_etablissement DROP services');
        }
        foreach (array_reverse(self::COLUMNS) as $column) {
            if ($this->columnExists('temp_etablissement', $column)) {
                $this->addSql("ALTER TABLE temp_etablissement DROP {$column}");
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
}
