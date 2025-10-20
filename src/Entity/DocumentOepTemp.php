<?php

namespace App\Entity;

use App\Repository\DocumentOepTempRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DocumentOepTempRepository::class)]
class DocumentOepTemp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    
    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(["fichier", "group_pro"])]
    private ?Fichier $path = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'documentOepTemps')]
    private ?LibelleGroupe $libelleGroupe = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    private ?string $etablissement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?Fichier
    {
        return $this->path;
    }

    public function setPath(?Fichier $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getLibelleGroupe(): ?LibelleGroupe
    {
        return $this->libelleGroupe;
    }

    public function setLibelleGroupe(?LibelleGroupe $libelleGroupe): static
    {
        $this->libelleGroupe = $libelleGroupe;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getEtablissement(): ?string
    {
        return $this->etablissement;
    }

    public function setEtablissement(string $etablissement): static
    {
        $this->etablissement = $etablissement;

        return $this;
    }
}
