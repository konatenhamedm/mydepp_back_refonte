<?php

namespace App\Entity;

use App\Repository\TypeDocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TypeDocumentRepository::class)]
class TypeDocument
{
    use TraitEntity; 

    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1","group_", "group_libelle"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_", "group_libelle"])]
    private ?string $libelle = null;

  

    #[ORM\ManyToOne(inversedBy: 'typeDocuments')]
    #[Group(["group1",' group_libelle'])]
    private ?TypePersonne $typePersonne = null;

    #[ORM\Column]
    #[Group(["group1"])]
    private ?int $nombre = null;

    #[ORM\ManyToOne(inversedBy: 'typeDocuments')]
    #[Group(["group1"])]
    private ?LibelleGroupe $libelleGroupe = null;

    public function getId(): ?int
    {
        return $this->id;
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

   

    public function getTypePersonne(): ?TypePersonne
    {
        return $this->typePersonne;
    }

    public function setTypePersonne(?TypePersonne $typePersonne): static
    {
        $this->typePersonne = $typePersonne;

        return $this;
    }

    public function getNombre(): ?int
    {
        return $this->nombre;
    }

    public function setNombre(int $nombre): static
    {
        $this->nombre = $nombre;

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
