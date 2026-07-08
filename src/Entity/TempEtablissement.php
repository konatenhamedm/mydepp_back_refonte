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



    #[ORM\Column(length: 100, nullable: true)]
    private ?string $reference = null;


    public function getId(): ?int
    {
        return $this->id;
    }



    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $status = null;


    #[ORM\Column(length: 100, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $typePersonne = null;

    /**
     * @var Collection<int, DocumentTemporaire>
     */
    #[ORM\OneToMany(targetEntity: DocumentTemporaire::class, mappedBy: 'tempEtablissement' , orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $documentTemporaires;

    #[ORM\Column(length: 255)]
    private ?string $typeUser = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $prenoms = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $emailAutre = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $typeSociete = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $bp = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $denomination = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nomRepresentant = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $niveauIntervention = null;

    // ---- Structure ----
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $typeDemandeEtablissement = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $typeEtablissement = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $natureEtablissement = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $typeOrganisation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $accordMinistere = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $dateValiditeAccord = null;

    // ---- Adresses et Contacts ----
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $district = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $villeVillage = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $commune = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $quartier = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $zoneSecteur = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $villaImmeubleEtagePorte = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ilotNumero = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lotNumero = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $rueAvenue = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $pointDeRepere = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $adresseElectronique = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $telephoneFixe = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $whatsapp = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $telephoneMobile = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $telephoneAutre = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $adressePostale = null;

    // ---- Personne physique ----
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $civilite = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $profession = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $cniNumero = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $whatsappPersonnel = null;

    // ---- Personne morale / Représentant ----
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $statutJuridique = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $representantCivilite = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $representantQualite = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $representantCni = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $representantTelephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $representantWhatsapp = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $representantEmail = null;

    // ---- Responsable médicolégal ----
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableCivilite = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableNom = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsabiliteMedicolegale = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableProfession = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableDiplome = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableSpecialite = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableNiveauFormation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableStatutAdministratif = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableEmail = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableTelephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableWhatsapp = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableNumeroOrdre = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $responsableCni = null;

    // ---- Enregistrement / Certificat / Horaires ----
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $anneeCreation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $enregistreeDepps = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numeroEnregistrement = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $organismeEnregistrement = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $anneeAutorisation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $aCertificatConformite = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $dateValiditeCertificat = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $horaireOuverture = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $autreHoraireOuverture = null;

    // ---- Contrôle Qualité et Services ----
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $aAccreditation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $engagementProcessusAccreditation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $certificationQualite = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $autresCertification = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $services = null;

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

    public function getTypeDemandeEtablissement(): ?string
    {
        return $this->typeDemandeEtablissement;
    }

    public function setTypeDemandeEtablissement(?string $typeDemandeEtablissement): static
    {
        $this->typeDemandeEtablissement = $typeDemandeEtablissement;

        return $this;
    }

    public function getTypeEtablissement(): ?string
    {
        return $this->typeEtablissement;
    }

    public function setTypeEtablissement(?string $typeEtablissement): static
    {
        $this->typeEtablissement = $typeEtablissement;

        return $this;
    }

    public function getNatureEtablissement(): ?string
    {
        return $this->natureEtablissement;
    }

    public function setNatureEtablissement(?string $natureEtablissement): static
    {
        $this->natureEtablissement = $natureEtablissement;

        return $this;
    }

    public function getTypeOrganisation(): ?string
    {
        return $this->typeOrganisation;
    }

    public function setTypeOrganisation(?string $typeOrganisation): static
    {
        $this->typeOrganisation = $typeOrganisation;

        return $this;
    }

    public function getAccordMinistere(): ?string
    {
        return $this->accordMinistere;
    }

    public function setAccordMinistere(?string $accordMinistere): static
    {
        $this->accordMinistere = $accordMinistere;

        return $this;
    }

    public function getDateValiditeAccord(): ?string
    {
        return $this->dateValiditeAccord;
    }

    public function setDateValiditeAccord(?string $dateValiditeAccord): static
    {
        $this->dateValiditeAccord = $dateValiditeAccord;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function setDistrict(?string $district): static
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

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): static
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

    public function getStatutJuridique(): ?string
    {
        return $this->statutJuridique;
    }

    public function setStatutJuridique(?string $statutJuridique): static
    {
        $this->statutJuridique = $statutJuridique;

        return $this;
    }

    public function getRepresentantCivilite(): ?string
    {
        return $this->representantCivilite;
    }

    public function setRepresentantCivilite(?string $representantCivilite): static
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

    public function getResponsableCivilite(): ?string
    {
        return $this->responsableCivilite;
    }

    public function setResponsableCivilite(?string $responsableCivilite): static
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

    public function getResponsabiliteMedicolegale(): ?string
    {
        return $this->responsabiliteMedicolegale;
    }

    public function setResponsabiliteMedicolegale(?string $responsabiliteMedicolegale): static
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

    public function getResponsableNiveauFormation(): ?string
    {
        return $this->responsableNiveauFormation;
    }

    public function setResponsableNiveauFormation(?string $responsableNiveauFormation): static
    {
        $this->responsableNiveauFormation = $responsableNiveauFormation;

        return $this;
    }

    public function getResponsableStatutAdministratif(): ?string
    {
        return $this->responsableStatutAdministratif;
    }

    public function setResponsableStatutAdministratif(?string $responsableStatutAdministratif): static
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

    public function getEnregistreeDepps(): ?string
    {
        return $this->enregistreeDepps;
    }

    public function setEnregistreeDepps(?string $enregistreeDepps): static
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

    public function getOrganismeEnregistrement(): ?string
    {
        return $this->organismeEnregistrement;
    }

    public function setOrganismeEnregistrement(?string $organismeEnregistrement): static
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

    public function getACertificatConformite(): ?string
    {
        return $this->aCertificatConformite;
    }

    public function setACertificatConformite(?string $aCertificatConformite): static
    {
        $this->aCertificatConformite = $aCertificatConformite;

        return $this;
    }

    public function getDateValiditeCertificat(): ?string
    {
        return $this->dateValiditeCertificat;
    }

    public function setDateValiditeCertificat(?string $dateValiditeCertificat): static
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

    public function getAAccreditation(): ?string
    {
        return $this->aAccreditation;
    }

    public function setAAccreditation(?string $aAccreditation): static
    {
        $this->aAccreditation = $aAccreditation;

        return $this;
    }

    public function getEngagementProcessusAccreditation(): ?string
    {
        return $this->engagementProcessusAccreditation;
    }

    public function setEngagementProcessusAccreditation(?string $engagementProcessusAccreditation): static
    {
        $this->engagementProcessusAccreditation = $engagementProcessusAccreditation;

        return $this;
    }

    public function getCertificationQualite(): ?string
    {
        return $this->certificationQualite;
    }

    public function setCertificationQualite(?string $certificationQualite): static
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

    public function getServices(): ?string
    {
        return $this->services;
    }

    public function setServices(?string $services): static
    {
        $this->services = $services;

        return $this;
    }
}
