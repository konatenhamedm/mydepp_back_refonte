<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[Group(["group_user_trx"])]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Group(["group_user_trx"])]
    private ?string $montant = null;

    #[ORM\Column(length: 255)]
    #[Group(["group_user_trx"])]
    private ?string $reference = null;

    #[ORM\Column(length: 255,nullable:true)]
    #[Group(["group_user_trx"])]
    private ?string $reference_channel = null;

    #[ORM\Column(length: 255,nullable:true)]
    #[Group(["group_user_trx"])]
    private ?string $channel = null;

    #[ORM\Column(length: 255)]
    #[Group(["group_user_trx"])]
    private ?string $type = null;

    #[ORM\Column]
    #[Group(["group_user_trx"])]
    private ?int $state = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $data = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_user_trx"])]
    private ?string $typeUser = null;

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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getReferenceChannel(): ?string
    {
        return $this->reference_channel;
    }

    public function setReferenceChannel(string $reference_channel): static
    {
        $this->reference_channel = $reference_channel;

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): static
    {
        $this->channel = $channel;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getTypeUser(): ?string
    {
        return $this->typeUser;
    }

    public function setTypeUser(?string $typeUser): static
    {
        $this->typeUser = $typeUser;

        return $this;
    }
}
