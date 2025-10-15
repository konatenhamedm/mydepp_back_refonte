<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;


#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    use TraitEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Group(["group1"])]
    private ?bool $isRead = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1"])]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[Group(["group1"])]
    private ?User $user = null;

    public function __construct()
    {
        $this->isRead = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setRead(bool $isRead): static
    {
        $this->isRead = $isRead;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
