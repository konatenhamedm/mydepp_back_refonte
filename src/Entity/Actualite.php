<?php

namespace App\Entity;

use App\Repository\ActualiteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;

#[ORM\Entity(repositoryClass: ActualiteRepository::class)]
class Actualite
{
    use TraitEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1"])]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["group1"])]
    private ?Fichier $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1"])]
    private ?string $hashtag = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Group(["group1"])]
    private ?string $description = null;

    #[ORM\ManyToOne(fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["group1"])]
    private ?Commune $commune = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Group(["group1"])]
    private ?string $lien = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?Fichier
    {
        return $this->image;
    }

    public function setImage(?Fichier $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getHashtag(): ?string
    {
        return $this->hashtag;
    }

    public function setHashtag(?string $hashtag): static
    {
        $this->hashtag = $hashtag;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    public function setCommune(?Commune $commune): static
    {
        $this->commune = $commune;

        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(?string $lien): static
    {
        $this->lien = $lien;

        return $this;
    }
}
