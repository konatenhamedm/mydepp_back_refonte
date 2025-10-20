<?php

namespace App\Entity;

use App\Repository\EtablissementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: EtablissementRepository::class)]
#[Table(name: 'membre_etablissement')]
class Etablissement extends Entite
{
    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[Group(["group_pro","group_user"])]
    private ?TypePersonne $typePersonne = null;

    /**
     * @var Collection<int, Document>
     */
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'etablissement', cascade: ['persist', 'remove'])]
    #[Group(["group_pro"])]
    private Collection $documents;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user"])]
    private ?string $prenoms = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $bp = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $typeSociete = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user"])]
    private ?string $denomination = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $nomRepresentant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Group(["group_pro"])]
    private ?\DateTimeInterface $dateVisite = null;


    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group_pro"])]
    private ?Fichier $rapportExamen = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Group(["group_pro"])]
    private ?\DateTimeInterface $dateExamenRapport = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $emailAutre = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    private ?NiveauIntervention $niveauIntervention = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    private ?User $imputation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $code = null;

    /**
     * @var Collection<int, DocumentOep>
     */
    #[ORM\OneToMany(targetEntity: DocumentOep::class, mappedBy: 'etablissement')]
    private Collection $documentOeps;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValidation = null;

    public function __construct()
    {
        parent::__construct();
        $this->documents = new ArrayCollection();
        $this->documentOeps = new ArrayCollection();
    }


    public function getTypePersonne(): ?TypePersonne
    {
        return $this->typePersonne;
    }

    public function setTypePersonne(?TypePersonne $typePersonne): static
    {
        $this->typePersonne = $typePersonne;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setEtablissement($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getEtablissement() === $this) {
                $document->setEtablissement(null);
            }
        }

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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

    public function getTypeSociete(): ?string
    {
        return $this->typeSociete;
    }

    public function setTypeSociete(?string $typeSociete): static
    {
        $this->typeSociete = $typeSociete;

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

    public function getDateVisite(): ?\DateTimeInterface
    {
        return $this->dateVisite;
    }

    public function setDateVisite(?\DateTimeInterface $dateVisite): static
    {
        $this->dateVisite = $dateVisite;

        return $this;
    }

    public function getRapportExamen(): ?Fichier
    {
        return $this->rapportExamen;
    }

    public function setRapportExamen(?Fichier $rapportExamen): static
    {
        $this->rapportExamen = $rapportExamen;

        return $this;
    }

    public function getDateExamenRapport(): ?\DateTimeInterface
    {
        return $this->dateExamenRapport;
    }

    public function setDateExamenRapport(?\DateTimeInterface $dateExamenRapport): static
    {
        $this->dateExamenRapport = $dateExamenRapport;

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

    public function getNiveauIntervention(): ?NiveauIntervention
    {
        return $this->niveauIntervention;
    }

    public function setNiveauIntervention(?NiveauIntervention $niveauIntervention): static
    {
        $this->niveauIntervention = $niveauIntervention;

        return $this;
    }

    public function getImputation(): ?User
    {
        return $this->imputation;
    }

    public function setImputation(?User $imputation): static
    {
        $this->imputation = $imputation;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, DocumentOep>
     */
    public function getDocumentOeps(): Collection
    {
        return $this->documentOeps;
    }

    public function addDocumentOep(DocumentOep $documentOep): static
    {
        if (!$this->documentOeps->contains($documentOep)) {
            $this->documentOeps->add($documentOep);
            $documentOep->setEtablissement($this);
        }

        return $this;
    }

    public function removeDocumentOep(DocumentOep $documentOep): static
    {
        if ($this->documentOeps->removeElement($documentOep)) {
            // set the owning side to null (unless already changed)
            if ($documentOep->getEtablissement() === $this) {
                $documentOep->setEtablissement(null);
            }
        }

        return $this;
    }

    public function getDateValidation(): ?\DateTimeInterface
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTimeInterface $dateValidation): static
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }
}
