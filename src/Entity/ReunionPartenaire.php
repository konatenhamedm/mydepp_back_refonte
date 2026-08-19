<?php

namespace App\Entity;

use App\Repository\ReunionPartenaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;

#[ORM\Entity(repositoryClass: ReunionPartenaireRepository::class)]
#[ORM\Table(name: 'reunion_partenaire')]
class ReunionPartenaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1"])]
    private ?string $nom = null;

    #[ORM\ManyToOne(targetEntity: Fichier::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Group(["group1"])]
    private ?Fichier $logo = null;

    #[ORM\ManyToOne(targetEntity: Reunion::class, inversedBy: 'partenaires')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Reunion $reunion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getLogo(): ?Fichier
    {
        return $this->logo;
    }

    public function setLogo(?Fichier $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getReunion(): ?Reunion
    {
        return $this->reunion;
    }

    public function setReunion(?Reunion $reunion): static
    {
        $this->reunion = $reunion;

        return $this;
    }
}
