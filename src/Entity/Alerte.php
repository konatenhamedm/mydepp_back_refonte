<?php

namespace App\Entity;

use App\Repository\AlerteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: AlerteRepository::class)]
class Alerte
{
    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'alertes')]
    #[Group(["group1"])]
    private ?User $user = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Group(["group1"])]
    private string $objet;

    #[ORM\Column(type: Types::TEXT)]
    #[Group(["group1"])]
    private string $message;


    #[ORM\Column(type: "string", length: 255,nullable:true)]
    #[Group(["group1"])]
    private string $lecteur;

    

    #[ORM\ManyToOne(inversedBy: 'alertes')]
    #[Group(["group1"])]
    private ?Destinateur $destinateur = null;


    public function __construct()
    {

       
    }


    public function getObjet(): string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): self
    {
        $this->objet = $objet;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }



    public function getLecteur(): string
    {
        return $this->lecteur;
    }

    public function setLecteur(string $lecteur): self
    {
        $this->lecteur = $lecteur;
        return $this;
    }

    
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

    public function getDestinateur(): ?Destinateur
    {
        return $this->destinateur;
    }

    public function setDestinateur(?Destinateur $destinateur): static
    {
        $this->destinateur = $destinateur;

        return $this;
    }
}
