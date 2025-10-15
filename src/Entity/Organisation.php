<?php

namespace App\Entity;

use App\Repository\OrganisationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
class Organisation
{
    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group_pro"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $nom = null;

   

    #[ORM\ManyToOne(inversedBy: 'organisations')]
    private ?Entite $entite = null;

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

 
    public function getEntite(): ?Entite
    {
        return $this->entite;
    }

    public function setEntite(?Entite $entite): static
    {
        $this->entite = $entite;

        return $this;
    }
}
