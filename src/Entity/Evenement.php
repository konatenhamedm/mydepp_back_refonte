<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
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

    #[ORM\Column(length: 500, nullable: true)]
    #[Group(["group1"])]
    private ?string $lien = null;

    #[ORM\Column(length: 50)]
    #[Group(["group1"])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Group(["group1"])]
    private ?\DateTimeInterface $dateEvenement = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group1"])]
    private ?string $texte = null;

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

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(?string $lien): static
    {
        $this->lien = $lien;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getDateEvenement(): ?\DateTimeInterface
    {
        return $this->dateEvenement;
    }

    public function setDateEvenement(?\DateTimeInterface $dateEvenement): static
    {
        $this->dateEvenement = $dateEvenement;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(?string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }
}
