<?php

namespace App\Entity;

use App\Repository\ReunionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReunionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Reunion
{
    use TraitEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1"])]
    private ?int $id = null;

    /**
     * @var Collection<int, Presence>
     */
    #[ORM\OneToMany(targetEntity: Presence::class, mappedBy: 'reunion', cascade: ['remove'])]
    private Collection $presences;

    public function __construct()
    {
        $this->presences = new ArrayCollection();
    }

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'objet de la réunion est obligatoire")]
    #[Group(["group1"])]
    private ?string $objet = null;

    // Identifiant public unique (utilisé dans l'URL du QR code de présence)
    #[ORM\Column(length: 64, unique: true, nullable: true)]
    #[Group(["group1"])]
    private ?string $token = null;

    // Type de rendez-vous : "presentiel" ou "en_ligne"
    #[ORM\Column(length: 50, options: ["default" => "presentiel"])]
    #[Group(["group1"])]
    private ?string $type = 'presentiel';

    // Lien de la réunion (uniquement quand le type est "en_ligne")
    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1"])]
    private ?string $lien = null;

    // Jour de la réunion
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Group(["group1"])]
    private ?\DateTimeInterface $jour = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): static
    {
        $this->objet = $objet;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(?string $lien): static
    {
        $this->lien = $lien;

        return $this;
    }

    public function getJour(): ?\DateTimeInterface
    {
        return $this->jour;
    }

    public function setJour(?\DateTimeInterface $jour): static
    {
        $this->jour = $jour;

        return $this;
    }

    /**
     * @return Collection<int, Presence>
     */
    public function getPresences(): Collection
    {
        return $this->presences;
    }

    public function addPresence(Presence $presence): static
    {
        if (!$this->presences->contains($presence)) {
            $this->presences->add($presence);
            $presence->setReunion($this);
        }

        return $this;
    }

    public function removePresence(Presence $presence): static
    {
        if ($this->presences->removeElement($presence)) {
            if ($presence->getReunion() === $this) {
                $presence->setReunion(null);
            }
        }

        return $this;
    }
}
