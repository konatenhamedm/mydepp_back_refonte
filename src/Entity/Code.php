<?php

namespace App\Entity;

use App\Repository\CodeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CodeRepository::class)]
class Code
{
    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type: "string", length: 255,unique:true)]
    private string $code;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $state = 0;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable(); 
        $this->updatedAt = new \DateTimeImmutable(); 
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;
        return $this;
    }
}
