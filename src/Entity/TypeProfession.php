<?php

namespace App\Entity;

use App\Repository\TypeProfessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TypeProfessionRepository::class)]
class TypeProfession
{

    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1",'group2'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1",'group2'])]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1",'group2'])]
    private ?string $code = null;

    /**
     * @var Collection<int, Profession>
     */
    //#[ORM\OneToMany(targetEntity: Profession::class, mappedBy: 'typeProfession')]
    #[ORM\OneToMany(
        targetEntity: Profession::class,
        mappedBy: 'typeProfession',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    #[Group(["group2"])]
    private Collection $professions;

    public function __construct()
    {
        $this->professions = new ArrayCollection();
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

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, Profession>
     */
    public function getProfessions(): Collection
    {
        return $this->professions;
    }

    public function addProfession(Profession $profession): static
    {
        if (!$this->professions->contains($profession)) {
            $this->professions->add($profession);
            $profession->setTypeProfession($this);
        }

        return $this;
    }

    public function removeProfession(Profession $profession): static
    {
        if ($this->professions->removeElement($profession)) {
            // set the owning side to null (unless already changed)
            if ($profession->getTypeProfession() === $this) {
                $profession->setTypeProfession(null);
            }
        }

        return $this;
    }
}
