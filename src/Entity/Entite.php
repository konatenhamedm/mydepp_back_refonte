<?php

namespace App\Entity;

use App\Repository\EntiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Table;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups as Group;




#[ORM\Entity(repositoryClass: EntiteRepository::class)]
#[Table(name: 'personne')]
#[InheritanceType("JOINED")]
#[DiscriminatorColumn(name: "discr", type: "string", length: 18)]
#[DiscriminatorMap([
    'entite' => Entite::class,
    'professionnel' => Professionnel::class,
    'etablissement' => Etablissement::class,
    'administrateur' => Administrateur::class,
])]
class Entite
{

    use TraitEntity; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(['group_pro',"group_user_trx"])]
    private ?int $id = null;


    #[ORM\Column(nullable: true)]
    #[Group(['group_pro',])]
    private ?string $appartenirOrganisation = null;

   

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'personne')]
    private Collection $users;

    #[ORM\ManyToOne(inversedBy: 'entites')]
    #[Group(["group_pro"])]
    private ?Genre $genre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $reason = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $status = null;

    /**
     * @var Collection<int, ValidationWorkflow>
     */
    #[ORM\OneToMany(targetEntity: ValidationWorkflow::class, mappedBy: 'personne')]
    private Collection $validationWorkflows;

    #[ORM\Column(nullable: true)]
    #[Group(["group_pro"])]
    private ?bool $actived = null;

 
    public function __construct()
    {
    
        $this->users = new ArrayCollection();
        $this->validationWorkflows = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getAppartenirOrganisation(): ?string
    {
        return $this->appartenirOrganisation;
    }

    public function setAppartenirOrganisation(string $appartenirOrganisation): static
    {
        $this->appartenirOrganisation = $appartenirOrganisation;

        return $this;
    }



    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setPersonne($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getPersonne() === $this) {
                $user->setPersonne(null);
            }
        }

        return $this;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    public function setGenre(?Genre $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, ValidationWorkflow>
     */
    public function getValidationWorkflows(): Collection
    {
        return $this->validationWorkflows;
    }

    public function addValidationWorkflow(ValidationWorkflow $validationWorkflow): static
    {
        if (!$this->validationWorkflows->contains($validationWorkflow)) {
            $this->validationWorkflows->add($validationWorkflow);
            $validationWorkflow->setPersonne($this);
        }

        return $this;
    }

    public function removeValidationWorkflow(ValidationWorkflow $validationWorkflow): static
    {
        if ($this->validationWorkflows->removeElement($validationWorkflow)) {
            // set the owning side to null (unless already changed)
            if ($validationWorkflow->getPersonne() === $this) {
                $validationWorkflow->setPersonne(null);
            }
        }

        return $this;
    }

    public function isActived(): ?bool
    {
        return $this->actived;
    }

    public function setActived(?bool $actived): static
    {
        $this->actived = $actived;

        return $this;
    }

}
