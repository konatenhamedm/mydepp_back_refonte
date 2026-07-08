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
    #[Group(["group_pro","group_user", "group_user_trx"])]
    private ?TypePersonne $typePersonne = null;

    /**
     * @var Collection<int, Document>
     */
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'etablissement', cascade: ['persist', 'remove'])]
    #[Group(["group_pro"])]
    private Collection $documents;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro","group_user"])]
    private ?string $nom = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro","group_user"])]
    private ?string $prenoms = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $bp = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $typeSociete = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro","group_user"])]
    private ?string $denomination = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $adresse = null;

    #[ORM\Column(length: 100, nullable: true)]
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

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $emailAutre = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    private ?NiveauIntervention $niveauIntervention = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    private ?User $imputation = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $code = null;

    /**
     * @var Collection<int, DocumentOep>
     */
    #[ORM\OneToMany(targetEntity: DocumentOep::class, mappedBy: 'etablissement')]
    private Collection $documentOeps;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValidation = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[Group(["group_pro"])]
    private ?TypeDemandeEtablissement $typeDemandeEtablissement = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[Group(["group_pro"])]
    private ?TypeEtablissement $typeEtablissement = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[Group(["group_pro"])]
    private ?NatureEtablissement $natureEtablissement = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[Group(["group_pro"])]
    private ?TypeOrganisation $typeOrganisation = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group_pro"])]
    private ?bool $accordMinistere = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Group(["group_pro"])]
    private ?\DateTimeInterface $dateValiditeAccord = null;

    #[ORM\ManyToOne]
    #[Group(["group_pro"])]
    private ?Region $region = null;

    #[ORM\ManyToOne]
    #[Group(["group_pro"])]
    private ?District $district = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $villeVillage = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $commune = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $quartier = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $zoneSecteur = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $villaImmeubleEtagePorte = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $ilotNumero = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $lotNumero = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $rueAvenue = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $pointDeRepere = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $adresseElectronique = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $telephoneFixe = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $whatsapp = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $telephoneMobile = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $telephoneAutre = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $adressePostale = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[Group(["group_pro"])]
    private ?StatutJuridique $statutJuridique = null;

    #[ORM\ManyToOne]
    #[Group(["group_pro"])]
    private ?Civilite $civilite = null;

    #[ORM\ManyToOne]
    #[Group(["group_pro"])]
    private ?Profession $profession = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $cniNumero = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $whatsappPersonnel = null;

    #[ORM\ManyToOne]
    #[Group(["group_pro"])]
    private ?Civilite $representantCivilite = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $representantQualite = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $representantCni = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $representantTelephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $representantWhatsapp = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $representantEmail = null;

    #[ORM\ManyToOne]
    #[Group(["group_pro"])]
    private ?Civilite $responsableCivilite = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $responsableNom = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[Group(["group_pro"])]
    private ?ResponsabiliteMedicolegale $responsabiliteMedicolegale = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $responsableProfession = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $responsableDiplome = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $responsableSpecialite = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[Group(["group_pro"])]
    private ?NiveauFormation $responsableNiveauFormation = null;

    #[ORM\ManyToOne]
    #[Group(["group_pro"])]
    private ?StatusPro $responsableStatutAdministratif = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $responsableEmail = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $responsableTelephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $responsableWhatsapp = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $responsableNumeroOrdre = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $responsableCni = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $anneeCreation = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group_pro"])]
    private ?bool $enregistreeDepps = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $numeroEnregistrement = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[Group(["group_pro"])]
    private ?OrganismeEnregistrement $organismeEnregistrement = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $anneeAutorisation = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group_pro"])]
    private ?bool $aCertificatConformite = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Group(["group_pro"])]
    private ?\DateTimeInterface $dateValiditeCertificat = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $horaireOuverture = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $autreHoraireOuverture = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group_pro"])]
    private ?bool $aAccreditation = null;

    #[ORM\Column(nullable: true)]
    #[Group(["group_pro"])]
    private ?bool $engagementProcessusAccreditation = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[Group(["group_pro"])]
    private ?CertificationQualite $certificationQualite = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $autresCertification = null;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\ManyToMany(targetEntity: Service::class, inversedBy: 'etablissements')]
    #[Group(["group_pro"])]
    private Collection $services;

    public function __construct()
    {
        parent::__construct();
        $this->documents = new ArrayCollection();
        $this->documentOeps = new ArrayCollection();
        $this->services = new ArrayCollection();
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

    public function getTypeDemandeEtablissement(): ?TypeDemandeEtablissement
    {
        return $this->typeDemandeEtablissement;
    }

    public function setTypeDemandeEtablissement(?TypeDemandeEtablissement $typeDemandeEtablissement): static
    {
        $this->typeDemandeEtablissement = $typeDemandeEtablissement;

        return $this;
    }

    public function getTypeEtablissement(): ?TypeEtablissement
    {
        return $this->typeEtablissement;
    }

    public function setTypeEtablissement(?TypeEtablissement $typeEtablissement): static
    {
        $this->typeEtablissement = $typeEtablissement;

        return $this;
    }

    public function getNatureEtablissement(): ?NatureEtablissement
    {
        return $this->natureEtablissement;
    }

    public function setNatureEtablissement(?NatureEtablissement $natureEtablissement): static
    {
        $this->natureEtablissement = $natureEtablissement;

        return $this;
    }

    public function getTypeOrganisation(): ?TypeOrganisation
    {
        return $this->typeOrganisation;
    }

    public function setTypeOrganisation(?TypeOrganisation $typeOrganisation): static
    {
        $this->typeOrganisation = $typeOrganisation;

        return $this;
    }

    public function isAccordMinistere(): ?bool
    {
        return $this->accordMinistere;
    }

    public function setAccordMinistere(?bool $accordMinistere): static
    {
        $this->accordMinistere = $accordMinistere;

        return $this;
    }

    public function getDateValiditeAccord(): ?\DateTimeInterface
    {
        return $this->dateValiditeAccord;
    }

    public function setDateValiditeAccord(?\DateTimeInterface $dateValiditeAccord): static
    {
        $this->dateValiditeAccord = $dateValiditeAccord;

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): static
    {
        $this->district = $district;

        return $this;
    }

    public function getVilleVillage(): ?string
    {
        return $this->villeVillage;
    }

    public function setVilleVillage(?string $villeVillage): static
    {
        $this->villeVillage = $villeVillage;

        return $this;
    }

    public function getCommune(): ?string
    {
        return $this->commune;
    }

    public function setCommune(?string $commune): static
    {
        $this->commune = $commune;

        return $this;
    }

    public function getQuartier(): ?string
    {
        return $this->quartier;
    }

    public function setQuartier(?string $quartier): static
    {
        $this->quartier = $quartier;

        return $this;
    }

    public function getZoneSecteur(): ?string
    {
        return $this->zoneSecteur;
    }

    public function setZoneSecteur(?string $zoneSecteur): static
    {
        $this->zoneSecteur = $zoneSecteur;

        return $this;
    }

    public function getVillaImmeubleEtagePorte(): ?string
    {
        return $this->villaImmeubleEtagePorte;
    }

    public function setVillaImmeubleEtagePorte(?string $villaImmeubleEtagePorte): static
    {
        $this->villaImmeubleEtagePorte = $villaImmeubleEtagePorte;

        return $this;
    }

    public function getIlotNumero(): ?string
    {
        return $this->ilotNumero;
    }

    public function setIlotNumero(?string $ilotNumero): static
    {
        $this->ilotNumero = $ilotNumero;

        return $this;
    }

    public function getLotNumero(): ?string
    {
        return $this->lotNumero;
    }

    public function setLotNumero(?string $lotNumero): static
    {
        $this->lotNumero = $lotNumero;

        return $this;
    }

    public function getRueAvenue(): ?string
    {
        return $this->rueAvenue;
    }

    public function setRueAvenue(?string $rueAvenue): static
    {
        $this->rueAvenue = $rueAvenue;

        return $this;
    }

    public function getPointDeRepere(): ?string
    {
        return $this->pointDeRepere;
    }

    public function setPointDeRepere(?string $pointDeRepere): static
    {
        $this->pointDeRepere = $pointDeRepere;

        return $this;
    }

    public function getAdresseElectronique(): ?string
    {
        return $this->adresseElectronique;
    }

    public function setAdresseElectronique(?string $adresseElectronique): static
    {
        $this->adresseElectronique = $adresseElectronique;

        return $this;
    }

    public function getTelephoneFixe(): ?string
    {
        return $this->telephoneFixe;
    }

    public function setTelephoneFixe(?string $telephoneFixe): static
    {
        $this->telephoneFixe = $telephoneFixe;

        return $this;
    }

    public function getWhatsapp(): ?string
    {
        return $this->whatsapp;
    }

    public function setWhatsapp(?string $whatsapp): static
    {
        $this->whatsapp = $whatsapp;

        return $this;
    }

    public function getTelephoneMobile(): ?string
    {
        return $this->telephoneMobile;
    }

    public function setTelephoneMobile(?string $telephoneMobile): static
    {
        $this->telephoneMobile = $telephoneMobile;

        return $this;
    }

    public function getTelephoneAutre(): ?string
    {
        return $this->telephoneAutre;
    }

    public function setTelephoneAutre(?string $telephoneAutre): static
    {
        $this->telephoneAutre = $telephoneAutre;

        return $this;
    }

    public function getAdressePostale(): ?string
    {
        return $this->adressePostale;
    }

    public function setAdressePostale(?string $adressePostale): static
    {
        $this->adressePostale = $adressePostale;

        return $this;
    }

    public function getStatutJuridique(): ?StatutJuridique
    {
        return $this->statutJuridique;
    }

    public function setStatutJuridique(?StatutJuridique $statutJuridique): static
    {
        $this->statutJuridique = $statutJuridique;

        return $this;
    }

    public function getCivilite(): ?Civilite
    {
        return $this->civilite;
    }

    public function setCivilite(?Civilite $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getProfession(): ?Profession
    {
        return $this->profession;
    }

    public function setProfession(?Profession $profession): static
    {
        $this->profession = $profession;

        return $this;
    }

    public function getCniNumero(): ?string
    {
        return $this->cniNumero;
    }

    public function setCniNumero(?string $cniNumero): static
    {
        $this->cniNumero = $cniNumero;

        return $this;
    }

    public function getWhatsappPersonnel(): ?string
    {
        return $this->whatsappPersonnel;
    }

    public function setWhatsappPersonnel(?string $whatsappPersonnel): static
    {
        $this->whatsappPersonnel = $whatsappPersonnel;

        return $this;
    }

    public function getRepresentantCivilite(): ?Civilite
    {
        return $this->representantCivilite;
    }

    public function setRepresentantCivilite(?Civilite $representantCivilite): static
    {
        $this->representantCivilite = $representantCivilite;

        return $this;
    }

    public function getRepresentantQualite(): ?string
    {
        return $this->representantQualite;
    }

    public function setRepresentantQualite(?string $representantQualite): static
    {
        $this->representantQualite = $representantQualite;

        return $this;
    }

    public function getRepresentantCni(): ?string
    {
        return $this->representantCni;
    }

    public function setRepresentantCni(?string $representantCni): static
    {
        $this->representantCni = $representantCni;

        return $this;
    }

    public function getRepresentantTelephone(): ?string
    {
        return $this->representantTelephone;
    }

    public function setRepresentantTelephone(?string $representantTelephone): static
    {
        $this->representantTelephone = $representantTelephone;

        return $this;
    }

    public function getRepresentantWhatsapp(): ?string
    {
        return $this->representantWhatsapp;
    }

    public function setRepresentantWhatsapp(?string $representantWhatsapp): static
    {
        $this->representantWhatsapp = $representantWhatsapp;

        return $this;
    }

    public function getRepresentantEmail(): ?string
    {
        return $this->representantEmail;
    }

    public function setRepresentantEmail(?string $representantEmail): static
    {
        $this->representantEmail = $representantEmail;

        return $this;
    }

    public function getResponsableCivilite(): ?Civilite
    {
        return $this->responsableCivilite;
    }

    public function setResponsableCivilite(?Civilite $responsableCivilite): static
    {
        $this->responsableCivilite = $responsableCivilite;

        return $this;
    }

    public function getResponsableNom(): ?string
    {
        return $this->responsableNom;
    }

    public function setResponsableNom(?string $responsableNom): static
    {
        $this->responsableNom = $responsableNom;

        return $this;
    }

    public function getResponsabiliteMedicolegale(): ?ResponsabiliteMedicolegale
    {
        return $this->responsabiliteMedicolegale;
    }

    public function setResponsabiliteMedicolegale(?ResponsabiliteMedicolegale $responsabiliteMedicolegale): static
    {
        $this->responsabiliteMedicolegale = $responsabiliteMedicolegale;

        return $this;
    }

    public function getResponsableProfession(): ?string
    {
        return $this->responsableProfession;
    }

    public function setResponsableProfession(?string $responsableProfession): static
    {
        $this->responsableProfession = $responsableProfession;

        return $this;
    }

    public function getResponsableDiplome(): ?string
    {
        return $this->responsableDiplome;
    }

    public function setResponsableDiplome(?string $responsableDiplome): static
    {
        $this->responsableDiplome = $responsableDiplome;

        return $this;
    }

    public function getResponsableSpecialite(): ?string
    {
        return $this->responsableSpecialite;
    }

    public function setResponsableSpecialite(?string $responsableSpecialite): static
    {
        $this->responsableSpecialite = $responsableSpecialite;

        return $this;
    }

    public function getResponsableNiveauFormation(): ?NiveauFormation
    {
        return $this->responsableNiveauFormation;
    }

    public function setResponsableNiveauFormation(?NiveauFormation $responsableNiveauFormation): static
    {
        $this->responsableNiveauFormation = $responsableNiveauFormation;

        return $this;
    }

    public function getResponsableStatutAdministratif(): ?StatusPro
    {
        return $this->responsableStatutAdministratif;
    }

    public function setResponsableStatutAdministratif(?StatusPro $responsableStatutAdministratif): static
    {
        $this->responsableStatutAdministratif = $responsableStatutAdministratif;

        return $this;
    }

    public function getResponsableEmail(): ?string
    {
        return $this->responsableEmail;
    }

    public function setResponsableEmail(?string $responsableEmail): static
    {
        $this->responsableEmail = $responsableEmail;

        return $this;
    }

    public function getResponsableTelephone(): ?string
    {
        return $this->responsableTelephone;
    }

    public function setResponsableTelephone(?string $responsableTelephone): static
    {
        $this->responsableTelephone = $responsableTelephone;

        return $this;
    }

    public function getResponsableWhatsapp(): ?string
    {
        return $this->responsableWhatsapp;
    }

    public function setResponsableWhatsapp(?string $responsableWhatsapp): static
    {
        $this->responsableWhatsapp = $responsableWhatsapp;

        return $this;
    }

    public function getResponsableNumeroOrdre(): ?string
    {
        return $this->responsableNumeroOrdre;
    }

    public function setResponsableNumeroOrdre(?string $responsableNumeroOrdre): static
    {
        $this->responsableNumeroOrdre = $responsableNumeroOrdre;

        return $this;
    }

    public function getResponsableCni(): ?string
    {
        return $this->responsableCni;
    }

    public function setResponsableCni(?string $responsableCni): static
    {
        $this->responsableCni = $responsableCni;

        return $this;
    }

    public function getAnneeCreation(): ?string
    {
        return $this->anneeCreation;
    }

    public function setAnneeCreation(?string $anneeCreation): static
    {
        $this->anneeCreation = $anneeCreation;

        return $this;
    }

    public function isEnregistreeDepps(): ?bool
    {
        return $this->enregistreeDepps;
    }

    public function setEnregistreeDepps(?bool $enregistreeDepps): static
    {
        $this->enregistreeDepps = $enregistreeDepps;

        return $this;
    }

    public function getNumeroEnregistrement(): ?string
    {
        return $this->numeroEnregistrement;
    }

    public function setNumeroEnregistrement(?string $numeroEnregistrement): static
    {
        $this->numeroEnregistrement = $numeroEnregistrement;

        return $this;
    }

    public function getOrganismeEnregistrement(): ?OrganismeEnregistrement
    {
        return $this->organismeEnregistrement;
    }

    public function setOrganismeEnregistrement(?OrganismeEnregistrement $organismeEnregistrement): static
    {
        $this->organismeEnregistrement = $organismeEnregistrement;

        return $this;
    }

    public function getAnneeAutorisation(): ?string
    {
        return $this->anneeAutorisation;
    }

    public function setAnneeAutorisation(?string $anneeAutorisation): static
    {
        $this->anneeAutorisation = $anneeAutorisation;

        return $this;
    }

    public function isACertificatConformite(): ?bool
    {
        return $this->aCertificatConformite;
    }

    public function setACertificatConformite(?bool $aCertificatConformite): static
    {
        $this->aCertificatConformite = $aCertificatConformite;

        return $this;
    }

    public function getDateValiditeCertificat(): ?\DateTimeInterface
    {
        return $this->dateValiditeCertificat;
    }

    public function setDateValiditeCertificat(?\DateTimeInterface $dateValiditeCertificat): static
    {
        $this->dateValiditeCertificat = $dateValiditeCertificat;

        return $this;
    }

    public function getHoraireOuverture(): ?string
    {
        return $this->horaireOuverture;
    }

    public function setHoraireOuverture(?string $horaireOuverture): static
    {
        $this->horaireOuverture = $horaireOuverture;

        return $this;
    }

    public function getAutreHoraireOuverture(): ?string
    {
        return $this->autreHoraireOuverture;
    }

    public function setAutreHoraireOuverture(?string $autreHoraireOuverture): static
    {
        $this->autreHoraireOuverture = $autreHoraireOuverture;

        return $this;
    }

    public function isAAccreditation(): ?bool
    {
        return $this->aAccreditation;
    }

    public function setAAccreditation(?bool $aAccreditation): static
    {
        $this->aAccreditation = $aAccreditation;

        return $this;
    }

    public function isEngagementProcessusAccreditation(): ?bool
    {
        return $this->engagementProcessusAccreditation;
    }

    public function setEngagementProcessusAccreditation(?bool $engagementProcessusAccreditation): static
    {
        $this->engagementProcessusAccreditation = $engagementProcessusAccreditation;

        return $this;
    }

    public function getCertificationQualite(): ?CertificationQualite
    {
        return $this->certificationQualite;
    }

    public function setCertificationQualite(?CertificationQualite $certificationQualite): static
    {
        $this->certificationQualite = $certificationQualite;

        return $this;
    }

    public function getAutresCertification(): ?string
    {
        return $this->autresCertification;
    }

    public function setAutresCertification(?string $autresCertification): static
    {
        $this->autresCertification = $autresCertification;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        $this->services->removeElement($service);

        return $this;
    }
}
