<?php

namespace App\Entity;

use App\Repository\AdministrateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: AdministrateurRepository::class)]
class Administrateur extends Entite
{
  

    #[ORM\Column(length: 255)]
    #[Group(["group1", "group_user", 'group_pro'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1", "group_user", 'group_pro'])]
    private ?string $prenoms = null;



    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenoms(): ?string
    {
        return $this->prenoms;
    }

    public function setPrenoms(string $prenoms): static
    {
        $this->prenoms = $prenoms;

        return $this;
    }
}
