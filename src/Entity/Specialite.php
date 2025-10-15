<?php

namespace App\Entity;

use App\Repository\SpecialiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;

#[ORM\Entity(repositoryClass: SpecialiteRepository::class)]
class Specialite
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
     * @var Collection<int, Professionnel>
     */
    #[ORM\OneToMany(targetEntity: Professionnel::class, mappedBy: 'specialite')]
    private Collection $professionnels;

    #[ORM\Column]
    #[Group(["group1","group_pro"])]
    private ?bool $paiement = null;

    public function __construct()
    {
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
            $professionnel->setSpecialite($this);
        }

        return $this;
    }

    public function removeProfessionnel(Professionnel $professionnel): static
    {
        if ($this->professionnels->removeElement($professionnel)) {
            // set the owning side to null (unless already changed)
            if ($professionnel->getSpecialite() === $this) {
                $professionnel->setSpecialite(null);
            }
        }

        return $this;
    }

    public function isPaiement(): ?bool
    {
        return $this->paiement;
    }

    public function setPaiement(bool $paiement): static
    {
        $this->paiement = $paiement;

        return $this;
    }
}
