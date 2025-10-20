<?php

namespace App\Entity;

use App\Repository\TempEtablissementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TempEtablissementRepository::class)]
class TempEtablissement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(type: 'string',  nullable: true)]
    #[Group(["group1", "group_user", 'group_pro'])]
    private ?string $username = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Email]
    #[Group(["group1", "group_user", 'group_pro'])]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;



    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;


    public function getId(): ?int
    {
        return $this->id;
    }

  

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $status = null;

    
    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $typePersonne = null;

    /**
     * @var Collection<int, DocumentTemporaire>
     */
    #[ORM\OneToMany(targetEntity: DocumentTemporaire::class, mappedBy: 'tempEtablissement' , orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $documentTemporaires;

    #[ORM\Column(length: 255)]
    private ?string $typeUser = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenoms = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailAutre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeSociete = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $denomination = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomRepresentant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $niveauIntervention = null;

    public function __construct()
    {
        $this->documentTemporaires = new ArrayCollection();
    }



    /**
     * Get the value of password
     */ 
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */ 
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

   

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of typePersonne
     */ 
    public function getTypePersonne()
    {
        return $this->typePersonne;
    }

    /**
     * Set the value of typePersonne
     *
     * @return  self
     */ 
    public function setTypePersonne($typePersonne)
    {
        $this->typePersonne = $typePersonne;

        return $this;
    }


    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of reference
     */ 
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set the value of reference
     *
     * @return  self
     */ 
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get the value of username
     */ 
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */ 
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, DocumentTemporaire>
     */
    public function getDocumentTemporaires(): Collection
    {
        return $this->documentTemporaires;
    }

    public function addDocumentTemporaire(DocumentTemporaire $documentTemporaire): static
    {
        if (!$this->documentTemporaires->contains($documentTemporaire)) {
            $this->documentTemporaires->add($documentTemporaire);
            $documentTemporaire->setTempEtablissement($this);
        }

        return $this;
    }

    public function removeDocumentTemporaire(DocumentTemporaire $documentTemporaire): static
    {
        if ($this->documentTemporaires->removeElement($documentTemporaire)) {
            // set the owning side to null (unless already changed)
            if ($documentTemporaire->getTempEtablissement() === $this) {
                $documentTemporaire->setTempEtablissement(null);
            }
        }

        return $this;
    }

    public function getTypeUser(): ?string
    {
        return $this->typeUser;
    }

    public function setTypeUser(string $typeUser): static
    {
        $this->typeUser = $typeUser;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenoms(): ?string
    {
        return $this->prenoms;
    }

    public function setPrenoms(?string $prenoms): static
    {
        $this->prenoms = $prenoms;

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

    public function getEmailAutre(): ?string
    {
        return $this->emailAutre;
    }

    public function setEmailAutre(?string $emailAutre): static
    {
        $this->emailAutre = $emailAutre;

        return $this;
    }

    public function getTypeSociete(): ?string
    {
        return $this->typeSociete;
    }

    public function setTypeSociete(?string $typeSociete): static
    {
        $this->typeSociete = $typeSociete;

        return $this;
    }

    public function getBp(): ?string
    {
        return $this->bp;
    }

    public function setBp(?string $bp): static
    {
        $this->bp = $bp;

        return $this;
    }

    public function getDenomination(): ?string
    {
        return $this->denomination;
    }

    public function setDenomination(?string $denomination): static
    {
        $this->denomination = $denomination;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getNomRepresentant(): ?string
    {
        return $this->nomRepresentant;
    }

    public function setNomRepresentant(?string $nomRepresentant): static
    {
        $this->nomRepresentant = $nomRepresentant;

        return $this;
    }

    public function getNiveauIntervention(): ?string
    {
        return $this->niveauIntervention;
    }

    public function setNiveauIntervention(?string $niveauIntervention): static
    {
        $this->niveauIntervention = $niveauIntervention;

        return $this;
    }
}
