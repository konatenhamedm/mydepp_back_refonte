<?php

namespace App\Entity;

use App\Repository\TypeDemandeEtablissementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;

#[ORM\Entity(repositoryClass: TypeDemandeEtablissementRepository::class)]
class TypeDemandeEtablissement
{
    use TraitEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1", "group_pro"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1", "group_pro"])]
    private ?string $libelle = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Group(["group1", "group_pro"])]
    private ?string $code = null;

    /**
     * @var Collection<int, Etablissement>
     */
    #[ORM\OneToMany(targetEntity: Etablissement::class, mappedBy: 'typeDemandeEtablissement')]
    private Collection $etablissements;

    public function __construct()
    {
        $this->etablissements = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

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
            $etablissement->setTypeDemandeEtablissement($this);
        }

        return $this;
    }

    public function removeEtablissement(Etablissement $etablissement): static
    {
        if ($this->etablissements->removeElement($etablissement)) {
            if ($etablissement->getTypeDemandeEtablissement() === $this) {
                $etablissement->setTypeDemandeEtablissement(null);
            }
        }

        return $this;
    }
}
