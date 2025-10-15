<?php

namespace App\Entity;

use App\Repository\LieuDiplomeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: LieuDiplomeRepository::class)]
class LieuDiplome
{ use TraitEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["fichier", "group_pro","group1"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["fichier", "group_pro","group1"])]
    private ?string $libelle = null;

    /**
     * @var Collection<int, Professionnel>
     */
    #[ORM\OneToMany(targetEntity: Professionnel::class, mappedBy: 'lieuObtentionDiplome')]
    private Collection $professionnels;

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
            $professionnel->setLieuObtentionDiplome($this);
        }

        return $this;
    }

    public function removeProfessionnel(Professionnel $professionnel): static
    {
        if ($this->professionnels->removeElement($professionnel)) {
            // set the owning side to null (unless already changed)
            if ($professionnel->getLieuObtentionDiplome() === $this) {
                $professionnel->setLieuObtentionDiplome(null);
            }
        }

        return $this;
    }
}
