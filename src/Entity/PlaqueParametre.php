<?php

namespace App\Entity;

use App\Repository\PlaqueParametreRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Paramètres de l'espace "Plaques d'immatriculation" (une seule ligne).
 * Le code d'accès aux fiches privées n'est jamais stocké en clair.
 */
#[ORM\Entity(repositoryClass: PlaqueParametreRepository::class)]
class PlaqueParametre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codeAccesHash = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $updatedByNom = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeAccesHash(): ?string
    {
        return $this->codeAccesHash;
    }

    public function setCodeAccesHash(string $codeAccesHash): static
    {
        $this->codeAccesHash = $codeAccesHash;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedByNom(): ?string
    {
        return $this->updatedByNom;
    }

    public function setUpdatedByNom(?string $updatedByNom): static
    {
        $this->updatedByNom = $updatedByNom;
        return $this;
    }
}
