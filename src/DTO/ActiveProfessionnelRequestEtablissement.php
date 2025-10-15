<?php


namespace App\DTO;

use App\Entity\Fichier;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

class ActiveProfessionnelRequestEtablissement
{
    #[Assert\NotBlank(message: "Le champ status est requis.")]
    #[Assert\Choice(
        choices: [
            "soumission_validation",
            "validation_directrice",
            "rejet_directrice",
            "initiation_demande_exploitation",
            "imputation_dossier",
            "imputation_conforme",
            "imputation_non_conforme",
            "programmation_visite",
            "visite_effectuee",
            "validation_finale",
            "rejet_final"
        ],
        message: "Le statut doit être l'une des valeurs suivantes : acceptation, rejet, validation, renouvellement, mis_a_jour."
    )]
    public ?string $status = null;


    public ?string $raison = null;

    public ?string $dateVisite = null;
    public ?string $email = null;
    public ?string $userUpdate = null;

    public ?Fichier $rapportExamen = null;
}
