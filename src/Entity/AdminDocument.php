<?php

namespace App\Entity;

use App\Repository\AdminDocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AdminDocumentRepository::class)]
class AdminDocument
{

    use TraitEntity; 


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group_pro","group1"])]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["group_pro",'group1'])]
    private ?Fichier $path = null;


    #[ORM\Column(length: 255)]
    #[Group(["group_pro","group1"])]
    private ?string $libelle = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group1"])]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group1"])]
    private ?string $sousType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?Fichier
    {
        return $this->path;
    }

    public function setPath(Fichier $path): static
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSousType(): ?string
    {
        return $this->sousType;
    }

    public function setSousType(?string $sousType): static
    {
        $this->sousType = $sousType;

        return $this;
    }
}
