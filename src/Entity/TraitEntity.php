<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\Groups as Group;


trait TraitEntity
{
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Group(["group_user","group1","group_user_trx","group_pro","group_pro_validate",'group_pro_validate_'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Group(["group_user"])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["group_pro","group_pro_validate"])]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $updatedBy = null;


    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new DateTimeImmutable();
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $user): self
    {
        $this->createdBy = $user;
        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $user): self
    {
        $this->updatedBy = $user;
        return $this;
    }
}
