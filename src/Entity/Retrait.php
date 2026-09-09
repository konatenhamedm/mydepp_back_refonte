<?php

namespace App\Entity;

use App\Repository\RetraitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RetraitRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Retrait
{
    use TraitEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1"])]
    private ?int $id = null;

    // Utilisateur (comptable/admin) ayant fait la demande — conservé pour traçabilité,
    // pas exposé via la sérialisation (cf. demandePar, snapshot lisible du nom).
    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le montant est obligatoire")]
    #[Group(["group1"])]
    private ?string $montant = null;

    // Numéro (mobile money) vers lequel le retrait doit être envoyé
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le numéro est obligatoire")]
    #[Group(["group1"])]
    private ?string $telephone = null;

    // Nom du demandeur, capturé au moment de la demande (reste lisible même si
    // le compte utilisateur est ensuite modifié/supprimé).
    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1"])]
    private ?string $demandePar = null;

    // Cycle de vie : "en_attente" (défaut) -> "valide" une fois l'argent décaissé
    // et la demande validée par un super admin.
    #[ORM\Column(length: 20, options: ["default" => "en_attente"])]
    #[Group(["group1"])]
    private string $statut = 'en_attente';

    // Nom du super admin ayant validé le décaissement, snapshot comme demandePar.
    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1"])]
    private ?string $valideParNom = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Group(["group1"])]
    private ?\DateTimeImmutable $valideAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getDemandePar(): ?string
    {
        return $this->demandePar;
    }

    public function setDemandePar(?string $demandePar): static
    {
        $this->demandePar = $demandePar;

        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getValideParNom(): ?string
    {
        return $this->valideParNom;
    }

    public function setValideParNom(?string $valideParNom): static
    {
        $this->valideParNom = $valideParNom;

        return $this;
    }

    public function getValideAt(): ?\DateTimeImmutable
    {
        return $this->valideAt;
    }

    public function setValideAt(?\DateTimeImmutable $valideAt): static
    {
        $this->valideAt = $valideAt;

        return $this;
    }
}
