<?php

namespace App\Entity;

use App\Repository\TypePersonneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: TypePersonneRepository::class)]
class TypePersonne
{
    use TraitEntity; 
 
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1","group_pro"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_pro"])]
    private ?string $libelle = null;

    /**
     * @var Collection<int, Etablissement>
     */
    #[ORM\OneToMany(targetEntity: Etablissement::class, mappedBy: 'typePersonne')]
    private Collection $etablissements;

    /**
     * @var Collection<int, TypeDocument>
     */
    #[ORM\OneToMany(targetEntity: TypeDocument::class, mappedBy: 'typePersonne')]
    private Collection $typeDocuments;

    #[ORM\Column(length: 10, nullable: true)]
    #[Group(["group1","group_pro"])]
    private ?string $code = null;

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

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

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
            $etablissement->setTypePersonne($this);
        }

        return $this;
    }

    public function removeEtablissement(Etablissement $etablissement): static
    {
        if ($this->etablissements->removeElement($etablissement)) {
            // set the owning side to null (unless already changed)
            if ($etablissement->getTypePersonne() === $this) {
                $etablissement->setTypePersonne(null);
            }
        }

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
            $typeDocument->setTypePersonne($this);
        }

        return $this;
    }

    public function removeTypeDocument(TypeDocument $typeDocument): static
    {
        if ($this->typeDocuments->removeElement($typeDocument)) {
            // set the owning side to null (unless already changed)
            if ($typeDocument->getTypePersonne() === $this) {
                $typeDocument->setTypePersonne(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }
}
