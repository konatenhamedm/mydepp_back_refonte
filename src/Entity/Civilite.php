<?php

namespace App\Entity;

use App\Repository\CiviliteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CiviliteRepository::class)]
class Civilite
{
    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1","group_pro"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_pro"])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_pro"])]
    private ?string $libelle = null;

    private int $nombre ;

    /**
     * @var Collection<int, Professionnel>
     */
    #[ORM\OneToMany(targetEntity: Professionnel::class, mappedBy: 'civilite')]
    private Collection $professionnels;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1", "group_pro"])]
    private ?string $codeGeneration = null;

    /**
     * @var Collection<int, CodeGenerateur>
     */
    #[ORM\OneToMany(targetEntity: CodeGenerateur::class, mappedBy: 'civilite')]
    private Collection $codeGenerateurs;

    public function __construct()
    {
        $this->professionnels = new ArrayCollection();
        $this->codeGenerateurs = new ArrayCollection();
    } 

    public function getId(): ?int
    {
        return $this->id;
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
            $professionnel->setCivilite($this);
        }

        return $this;
    }

    public function removeProfessionnel(Professionnel $professionnel): static
    {
        if ($this->professionnels->removeElement($professionnel)) {
            // set the owning side to null (unless already changed)
            if ($professionnel->getCivilite() === $this) {
                $professionnel->setCivilite(null);
            }
        }

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
            $codeGenerateur->setCivilite($this);
        }

        return $this;
    }

    public function removeCodeGenerateur(CodeGenerateur $codeGenerateur): static
    {
        if ($this->codeGenerateurs->removeElement($codeGenerateur)) {
            // set the owning side to null (unless already changed)
            if ($codeGenerateur->getCivilite() === $this) {
                $codeGenerateur->setCivilite(null);
            }
        }

        return $this;
    }

}
