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

    // Cycle de vie : "en_attente" (défaut) -> "valide" (décaissée) ou "annule",
    // les deux étant des décisions définitives d'un super admin.
    #[ORM\Column(length: 20, options: ["default" => "en_attente"])]
    #[Group(["group1"])]
    private string $statut = 'en_attente';

    // Nom du super admin ayant validé OU annulé la demande, snapshot comme demandePar.
    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1"])]
    private ?string $traiteParNom = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Group(["group1"])]
    private ?\DateTimeImmutable $traiteAt = null;

    // Motif renseigné par le super admin en cas d'annulation (optionnel).
    #[ORM\Column(type: 'text', nullable: true)]
    #[Group(["group1"])]
    private ?string $motifAnnulation = null;

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

    public function getTraiteParNom(): ?string
    {
        return $this->traiteParNom;
    }

    public function setTraiteParNom(?string $traiteParNom): static
    {
        $this->traiteParNom = $traiteParNom;

        return $this;
    }

    public function getTraiteAt(): ?\DateTimeImmutable
    {
        return $this->traiteAt;
    }

    public function setTraiteAt(?\DateTimeImmutable $traiteAt): static
    {
        $this->traiteAt = $traiteAt;

        return $this;
    }

    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function setMotifAnnulation(?string $motifAnnulation): static
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }
}
