<?php

namespace App\Entity;

use App\Repository\NiveauInterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: NiveauInterventionRepository::class)]
class NiveauIntervention
{ use TraitEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["group1"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["group1"])]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    #[Groups(["group1"])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Groups(["group1"])]
    private ?string $montant = null;

    /**
     * @var Collection<int, Etablissement>
     */
    #[ORM\OneToMany(targetEntity: Etablissement::class, mappedBy: 'niveauIntervention')]
    private Collection $etablissements;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["group1"])]
    private ?string $montantRenouvellement = null;

    /**
     * @var Collection<int, TypeDocument>
     */
    #[ORM\ManyToMany(targetEntity: TypeDocument::class, mappedBy: 'niveauInterventions')]
    #[Groups(["group1"])]
    private Collection $typeDocuments;

    public function __construct()
    {
        $this->etablissements = new ArrayCollection();
        $this->typeDocuments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * @return Collection<int, Etablissement>
     */
    public function getEtablissements(): Collection
    {
        return $this->etablissements;
    }

    public function addEtablissement(Etablissement $etablissement): static
    {
        if (!$this->etablissements->contains($etablissement)) {
            $this->etablissements->add($etablissement);
            $etablissement->setNiveauIntervention($this);
        }

        return $this;
    }

    public function removeEtablissement(Etablissement $etablissement): static
    {
        if ($this->etablissements->removeElement($etablissement)) {
            // set the owning side to null (unless already changed)
            if ($etablissement->getNiveauIntervention() === $this) {
                $etablissement->setNiveauIntervention(null);
            }
        }

        return $this;
    }

    public function getMontantRenouvellement(): ?string
    {
        return $this->montantRenouvellement;
    }

    public function setMontantRenouvellement(?string $montantRenouvellement): static
    {
        $this->montantRenouvellement = $montantRenouvellement;

        return $this;
    }

    /**
     * @return Collection<int, TypeDocument>
     */
    public function getTypeDocuments(): Collection
    {
        return $this->typeDocuments;
    }

    public function addTypeDocument(TypeDocument $typeDocument): static
    {
        if (!$this->typeDocuments->contains($typeDocument)) {
            $this->typeDocuments->add($typeDocument);
            $typeDocument->addNiveauIntervention($this);
        }

        return $this;
    }

    public function removeTypeDocument(TypeDocument $typeDocument): static
    {
        if ($this->typeDocuments->removeElement($typeDocument)) {
            $typeDocument->removeNiveauIntervention($this);
        }

        return $this;
    }
}
