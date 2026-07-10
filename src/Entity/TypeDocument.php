<?php

namespace App\Entity;

use App\Repository\TypeDocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(options: ["default" => true])]
    #[Group(["group1","group_libelle"])]
    private bool $obligatoire = true;

    /**
     * Niveaux d'intervention pour lesquels ce document est requis.
     * Collection vide = requis pour tous les niveaux.
     *
     * @var Collection<int, NiveauIntervention>
     */
    #[ORM\ManyToMany(targetEntity: NiveauIntervention::class, inversedBy: 'typeDocuments')]
    #[ORM\JoinTable(name: 'type_document_niveau_intervention')]
    #[Group(["group1"])]
    private Collection $niveauInterventions;

    public function __construct()
    {
        $this->niveauInterventions = new ArrayCollection();
    }

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

    public function isObligatoire(): bool
    {
        return $this->obligatoire;
    }

    public function setObligatoire(bool $obligatoire): static
    {
        $this->obligatoire = $obligatoire;

        return $this;
    }

    /**
     * @return Collection<int, NiveauIntervention>
     */
    public function getNiveauInterventions(): Collection
    {
        return $this->niveauInterventions;
    }

    public function addNiveauIntervention(NiveauIntervention $niveauIntervention): static
    {
        if (!$this->niveauInterventions->contains($niveauIntervention)) {
            $this->niveauInterventions->add($niveauIntervention);
        }

        return $this;
    }

    public function removeNiveauIntervention(NiveauIntervention $niveauIntervention): static
    {
        $this->niveauInterventions->removeElement($niveauIntervention);

        return $this;
    }
}
