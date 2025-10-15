<?php


namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ActiveProfessionnelRequest
{
    #[Assert\NotBlank(message: "Le champ status est requis.")]
    #[Assert\Choice(
        choices: ["acceptation", "rejet", "validation", "renouvellement", "mis_a_jour"],
        message: "Le statut doit être l'une des valeurs suivantes : acceptation, rejet, validation, renouvellement, mis_a_jour."
    )]
    public ?string $status = null;


    public ?string $raison = null;
}
