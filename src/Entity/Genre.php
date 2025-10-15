<?php

namespace App\Entity;

use App\Repository\GenreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: GenreRepository::class)]
class Genre
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
    #[ORM\OneToMany(targetEntity: Etablissement::class, mappedBy: 'genre')]
    private Collection $etablissements;

    /**
     * @var Collection<int, Professionnel>
     */
    #[ORM\OneToMany(targetEntity: Professionnel::class, mappedBy: 'genre')]
    private Collection $professionnels;

    /**
     * @var Collection<int, Entite>
     */
    #[ORM\OneToMany(targetEntity: Entite::class, mappedBy: 'genre')]
    private Collection $entites;

    public function __construct()
    {
        $this->etablissements = new ArrayCollection();
        $this->professionnels = new ArrayCollection();
        $this->entites = new ArrayCollection();
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
            $etablissement->setGenre($this);
        }

        return $this;
    }

    public function removeEtablissement(Etablissement $etablissement): static
    {
        if ($this->etablissements->removeElement($etablissement)) {
            // set the owning side to null (unless already changed)
            if ($etablissement->getGenre() === $this) {
                $etablissement->setGenre(null);
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
            $professionnel->setGenre($this);
        }

        return $this;
    }

    public function removeProfessionnel(Professionnel $professionnel): static
    {
        if ($this->professionnels->removeElement($professionnel)) {
            // set the owning side to null (unless already changed)
            if ($professionnel->getGenre() === $this) {
                $professionnel->setGenre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Entite>
     */
    public function getEntites(): Collection
    {
        return $this->entites;
    }

    public function addEntite(Entite $entite): static
    {
        if (!$this->entites->contains($entite)) {
            $this->entites->add($entite);
            $entite->setGenre($this);
        }

        return $this;
    }

    public function removeEntite(Entite $entite): static
    {
        if ($this->entites->removeElement($entite)) {
            // set the owning side to null (unless already changed)
            if ($entite->getGenre() === $this) {
                $entite->setGenre(null);
            }
        }

        return $this;
    }
}
