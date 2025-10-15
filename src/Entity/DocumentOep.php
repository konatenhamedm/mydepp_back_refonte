<?php

namespace App\Entity;

use App\Repository\DocumentOepRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DocumentOepRepository::class)]
class DocumentOep
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

      #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(["fichier", "group_pro"])]
    private ?Fichier $path = null;


    #[ORM\ManyToOne(inversedBy: 'documentOeps')]
    private ?Etablissement $etablissement = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["group_pro"])]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'documentOeps')]
/*     #[Groups(["group_pro"])] */
    private ?LibelleGroupe $libelleGroupe = null;

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

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): static
    {
        $this->etablissement = $etablissement;

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
}
