<?php

namespace App\Entity;

use App\Repository\ValidationWorkflowRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;


#[ORM\Entity(repositoryClass: ValidationWorkflowRepository::class)]
class ValidationWorkflow
{
    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1","group_pro_validate",'group_pro_validate_'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1","group_pro_validate",'group_pro_validate_'])]
    private ?string $etape = null;

    #[ORM\ManyToOne(inversedBy: 'validationWorkflows')]
    #[Group(["group1","group_pro_validate",'group_pro_validate_'])]
    private ?Entite $personne = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group1","group_pro_validate",'group_pro_validate_'])]
    private ?string $raison = null;

    public function getId(): ?int
    {
        return $this->id;
    }

   

    public function getEtape(): ?string
    {
        return $this->etape;
    }

    public function setEtape(?string $etape): static
    {
        $this->etape = $etape;

        return $this;
    }

    public function getPersonne(): ?Entite
    {
        return $this->personne;
    }

    public function setPersonne(?Entite $personne): static
    {
        $this->personne = $personne;

        return $this;
    }

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(?string $raison): static
    {
        $this->raison = $raison;

        return $this;
    }
}
