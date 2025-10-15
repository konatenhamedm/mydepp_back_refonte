<?php

namespace App\Entity;

use App\Repository\CodeGenerateurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: CodeGenerateurRepository::class)]
class CodeGenerateur
{

    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1","group_pro"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'codeGenerateurs')]
    #[Group(["group1","group_pro"])]
    private ?Profession $profession = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Group(["group1","group_pro"])]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\ManyToOne(inversedBy: 'codeGenerateurs')]
    #[Group(["group1","group_pro"])]
    private ?Civilite $civilite = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_pro"])]
    private ?string $code = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Group(["group1","group_pro"])]
    private ?\DateTimeInterface $dateCreation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfession(): ?Profession
    {
        return $this->profession;
    }

    public function setProfession(?Profession $profession): static
    {
        $this->profession = $profession;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getCivilite(): ?Civilite
    {
        return $this->civilite;
    }

    public function setCivilite(?Civilite $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }
}
