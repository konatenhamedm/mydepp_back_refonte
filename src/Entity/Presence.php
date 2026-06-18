<?php

namespace App\Entity;

use App\Repository\PresenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PresenceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Presence
{
    use TraitEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group_presence"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'presences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reunion $reunion = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom et prénoms sont obligatoires")]
    #[Group(["group_presence"])]
    private ?string $nomPrenoms = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_presence"])]
    private ?string $structure = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_presence"])]
    private ?string $fonction = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Group(["group_presence"])]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_presence"])]
    private ?string $email = null;

    // Signature tactile encodée en base64 (data URL)
    #[ORM\Column(type: 'text', nullable: true)]
    #[Group(["group_presence"])]
    private ?string $signature = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReunion(): ?Reunion
    {
        return $this->reunion;
    }

    public function setReunion(?Reunion $reunion): static
    {
        $this->reunion = $reunion;

        return $this;
    }

    public function getNomPrenoms(): ?string
    {
        return $this->nomPrenoms;
    }

    public function setNomPrenoms(string $nomPrenoms): static
    {
        $this->nomPrenoms = $nomPrenoms;

        return $this;
    }

    public function getStructure(): ?string
    {
        return $this->structure;
    }

    public function setStructure(?string $structure): static
    {
        $this->structure = $structure;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(?string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): static
    {
        $this->signature = $signature;

        return $this;
    }
}
