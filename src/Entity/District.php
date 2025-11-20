<?php

namespace App\Entity;

use App\Repository\DistrictRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;


#[ORM\Entity(repositoryClass: DistrictRepository::class)]
class District
{

    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1","group_pro","group_region"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1","group_pro","group_region"])]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'districts')]
    #[Group(["group1","group_pro"])]
    private ?Region $region = null;

    /**
     * @var Collection<int, Ville>
     */
    #[ORM\OneToMany(targetEntity: Ville::class, mappedBy: 'district')]
    private Collection $villes;

    /**
     * @var Collection<int, Professionnel>
     */
    #[ORM\OneToMany(targetEntity: Professionnel::class, mappedBy: 'district')]
    private Collection $professionnels;

    public function __construct()
    {
        $this->villes = new ArrayCollection();
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

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): static
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection<int, Ville>
     */
    public function getVilles(): Collection
    {
        return $this->villes;
    }

    public function addVille(Ville $ville): static
    {
        if (!$this->villes->contains($ville)) {
            $this->villes->add($ville);
            $ville->setDistrict($this);
        }

        return $this;
    }

    public function removeVille(Ville $ville): static
    {
        if ($this->villes->removeElement($ville)) {
            // set the owning side to null (unless already changed)
            if ($ville->getDistrict() === $this) {
                $ville->setDistrict(null);
            }
        }

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
            $professionnel->setDistrict($this);
        }

        return $this;
    }

    public function removeProfessionnel(Professionnel $professionnel): static
    {
        if ($this->professionnels->removeElement($professionnel)) {
            // set the owning side to null (unless already changed)
            if ($professionnel->getDistrict() === $this) {
                $professionnel->setDistrict(null);
            }
        }

        return $this;
    }
}
