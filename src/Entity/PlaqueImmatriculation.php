<?php

namespace App\Entity;

use App\Repository\PlaqueImmatriculationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Plaque d'immatriculation d'un établissement sanitaire privé, importée depuis une fiche Word.
 * La fiche contient deux tableaux :
 *  - "GRAND PUBLIC"  → donneesPubliques (QR code public)
 *  - "SERVICE DEPPS" → donneesPrivees   (QR code privé, protégé par code d'accès)
 */
#[ORM\Entity(repositoryClass: PlaqueImmatriculationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PlaqueImmatriculation
{
    use TraitEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Identifiant public non devinable, utilisé dans les URL des QR codes
    #[ORM\Column(length: 64, unique: true)]
    private ?string $token = null;

    #[ORM\Column(length: 255)]
    private ?string $denomination = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nature = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $immatriculation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numeroDossier = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $localisation = null;

    #[ORM\Column(type: Types::JSON)]
    private array $donneesPubliques = [];

    #[ORM\Column(type: Types::JSON)]
    private array $donneesPrivees = [];

    // Chemin relatif (sous /uploads) de l'image "signe de visibilité"
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signeVisibilite = null;

    // Nom d'origine du fichier Word importé
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomFichier = null;

    // Nom du fichier Word stocké hors du dossier public (var/plaques)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierStocke = null;

    // Position corrigée à la main sur la carte (sinon : position estimée depuis la localisation)
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $longitude = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function getDenomination(): ?string
    {
        return $this->denomination;
    }

    public function setDenomination(string $denomination): static
    {
        $this->denomination = $denomination;
        return $this;
    }

    public function getNature(): ?string
    {
        return $this->nature;
    }

    public function setNature(?string $nature): static
    {
        $this->nature = $nature;
        return $this;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(?string $immatriculation): static
    {
        $this->immatriculation = $immatriculation;
        return $this;
    }

    public function getNumeroDossier(): ?string
    {
        return $this->numeroDossier;
    }

    public function setNumeroDossier(?string $numeroDossier): static
    {
        $this->numeroDossier = $numeroDossier;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): static
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function getDonneesPubliques(): array
    {
        return $this->donneesPubliques;
    }

    public function setDonneesPubliques(array $donneesPubliques): static
    {
        $this->donneesPubliques = $donneesPubliques;
        return $this;
    }

    public function getDonneesPrivees(): array
    {
        return $this->donneesPrivees;
    }

    public function setDonneesPrivees(array $donneesPrivees): static
    {
        $this->donneesPrivees = $donneesPrivees;
        return $this;
    }

    public function getSigneVisibilite(): ?string
    {
        return $this->signeVisibilite;
    }

    public function setSigneVisibilite(?string $signeVisibilite): static
    {
        $this->signeVisibilite = $signeVisibilite;
        return $this;
    }

    public function getNomFichier(): ?string
    {
        return $this->nomFichier;
    }

    public function setNomFichier(?string $nomFichier): static
    {
        $this->nomFichier = $nomFichier;
        return $this;
    }

    public function getFichierStocke(): ?string
    {
        return $this->fichierStocke;
    }

    public function setFichierStocke(?string $fichierStocke): static
    {
        $this->fichierStocke = $fichierStocke;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /** null/null : retour à la position estimée depuis la localisation. */
    public function setPosition(?float $latitude, ?float $longitude): static
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        return $this;
    }
}
