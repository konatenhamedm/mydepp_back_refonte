<?php

namespace App\Entity;

use App\Repository\ProfessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ProfessionRepository::class)]
#[UniqueEntity(fields: 'libelle', message: 'Cette profession existe deja')]
class Profession
{

    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1","group2","group_pro","group_autre"])]
    private ?int $id = null;


    #[Group(["group1","group2","group_pro","group_autre"])]
    #[ORM\Column(type: 'string', unique: true, nullable: true,length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group2","group_pro","group_autre"])]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'professions')]
    #[Group(["group1"])]
    private ?TypeProfession $typeProfession = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group2"])]
    private ?string $montantNouvelleDemande = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group2"])]
    private ?string $montantRenouvellement = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1","group2"])]
    private ?string $codeGeneration = null;

    /**
     * @var Collection<int, CodeGenerateur>
     */
    #[ORM\OneToMany(targetEntity: CodeGenerateur::class, mappedBy: 'profession')]
    private Collection $codeGenerateurs;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1","group2"])]
    private ?string $maxCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1","group2"])]
    private ?string $chronoMax = null;

    /**
     * @var Collection<int, Professionnel>
     */
    #[ORM\OneToMany(targetEntity: Professionnel::class, mappedBy: 'profession')]
    private Collection $professionnels;

    public function __construct()
    {
        $this->codeGenerateurs = new ArrayCollection();
        $this->professionnels = new ArrayCollection();
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

    public function getTypeProfession(): ?TypeProfession
    {
        return $this->typeProfession;
    }

    public function setTypeProfession(?TypeProfession $typeProfession): static
    {
        $this->typeProfession = $typeProfession;

        return $this;
    }

    public function getMontantNouvelleDemande(): ?string
    {
        return $this->montantNouvelleDemande;
    }

    public function setMontantNouvelleDemande(string $montantNouvelleDemande): static
    {
        $this->montantNouvelleDemande = $montantNouvelleDemande;

        return $this;
    }

    public function getMontantRenouvellement(): ?string
    {
        return $this->montantRenouvellement;
    }

    public function setMontantRenouvellement(string $montantRenouvellement): static
    {
        $this->montantRenouvellement = $montantRenouvellement;

        return $this;
    }

    public function getCodeGeneration(): ?string
    {
        return $this->codeGeneration;
    }

    public function setCodeGeneration(?string $codeGeneration): static
    {
        $this->codeGeneration = $codeGeneration;

        return $this;
    }

    /**
     * @return Collection<int, CodeGenerateur>
     */
    public function getCodeGenerateurs(): Collection
    {
        return $this->codeGenerateurs;
    }

    public function addCodeGenerateur(CodeGenerateur $codeGenerateur): static
    {
        if (!$this->codeGenerateurs->contains($codeGenerateur)) {
            $this->codeGenerateurs->add($codeGenerateur);
            $codeGenerateur->setProfession($this);
        }

        return $this;
    }

    public function removeCodeGenerateur(CodeGenerateur $codeGenerateur): static
    {
        if ($this->codeGenerateurs->removeElement($codeGenerateur)) {
            // set the owning side to null (unless already changed)
            if ($codeGenerateur->getProfession() === $this) {
                $codeGenerateur->setProfession(null);
            }
        }

        return $this;
    }

    public function getMaxCode(): ?string
    {
        return $this->maxCode;
    }

    public function setMaxCode(?string $maxCode): static
    {
        $this->maxCode = $maxCode;

        return $this;
    }

    public function getChronoMax(): ?string
    {
        return $this->chronoMax;
    }

    public function setChronoMax(?string $chronoMax): static
    {
        $this->chronoMax = $chronoMax;

        return $this;
    }

    /**
     * @return Collection<int, Professionnel>
     */
    public function getProfessionnels(): Collection
    {
        return $this->professionnels;
    }

    public function addProfessionnel(Professionnel $professionnel): static
    {
        if (!$this->professionnels->contains($professionnel)) {
            $this->professionnels->add($professionnel);
            $professionnel->setProfession($this);
        }

        return $this;
    }

    public function removeProfessionnel(Professionnel $professionnel): static
    {
        if ($this->professionnels->removeElement($professionnel)) {
            // set the owning side to null (unless already changed)
            if ($professionnel->getProfession() === $this) {
                $professionnel->setProfession(null);
            }
        }

        return $this;
    }
}
