<?php

namespace App\Entity;

use App\Repository\DocumentTemporaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DocumentTemporaireRepository::class)]
class DocumentTemporaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'documentTemporaires')]
    private ?TempEtablissement $tempEtablissement = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(["fichier", "group_pro"])]
    private ?Fichier $path = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'documentTemporaires')]
    private ?LibelleGroupe $libelleGroupe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTempEtablissement(): ?TempEtablissement
    {
        return $this->tempEtablissement;
    }

    public function setTempEtablissement(?TempEtablissement $tempEtablissement): static
    {
        $this->tempEtablissement = $tempEtablissement;

        return $this;
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

    public function setLibelle(string $libelle): static
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
}
