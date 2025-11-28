<?php

namespace App\Entity;

use App\Repository\ProfessionnelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProfessionnelRepository::class)]
#[Table(name: 'membre_professionnel')]
class Professionnel extends Entite
{

    //ETAPE 2 7 CHAMPS

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro", "group_user_trx"])]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro", "group_user_trx"])]
    private ?string $poleSanitaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user", "group_user_trx","group1"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $professionnel = null; // structure 

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user" ,"group_user_trx","group1"])]
    private ?string $prenoms = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro", "group_user_trx"])]
    private ?string $lieuExercicePro = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro", "group_user_trx","group1"])]
    private ?string $email = null;


    //ETAPE 3 12 CHAMPS


    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["group_pro"])]
    private ?Civilite $civilite = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $emailPro = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Group(["group_pro"])]
    private ?\DateTimeInterface $dateDiplome = null;

    //
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Group(["group_pro"])]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro", "group_user_trx"])]
    private ?string $number = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $lieuDiplome = null;

    //


    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["group1", "group_pro"])]
    private ?Pays $nationate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $situation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Group(["group_pro"])]
    private ?\DateTimeInterface $datePremierDiplome = null;



    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poleSanitairePro = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $diplome = null;


    /*  #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $situationPro = null;
 */


    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group_pro"])]
    private ?Fichier $photo = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group_pro"])]
    private ?Fichier $diplomeFile = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group_pro"])]
    private ?Fichier $cni = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group_pro"])]
    private ?Fichier $cv = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group_pro"])]
    private ?Fichier $casier = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group_pro"])]
    private ?Fichier $certificat = null;

    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["group_pro"])]
    private ?Specialite $specialite = null;
    
    
    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["group_pro"])]
    private ?Ville $ville = null;
    
    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["group_pro"])]
    private ?SituationProfessionnelle $situationPro = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $organisationNom = null;

    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["group_pro"])]
    private ?Region $region = null;

    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["group_pro"])]
    private ?District $district = null;

    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["group_pro"])]
    private ?Commune $commune = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user_trx"])]
    private ?string $quartier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $appartenirOrdre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroInscription = null;

    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    private ?User $imputation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValidation = null;

    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["group_pro"])]
    private ?TypeDiplome $typeDiplome = null;

    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["group_pro"])]
    private ?StatusPro $statusPro = null;

    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["fichier", "group_pro"])]
    private ?LieuDiplome $lieuObtentionDiplome = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specialiteAutre = null;

    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    #[Group(["group_pro"])]
    private ?Profession $profession = null;

    #[ORM\ManyToOne(inversedBy: 'professionnels')]
    private ?Ordre $ordre = null;

  

    public function __construct()
    {
        parent::__construct();
       
    }



    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber($number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEmailPro(): ?string
    {
        return $this->emailPro;
    }

    public function setEmailPro(?string $emailPro): static
    {
        $this->emailPro = $emailPro;

        return $this;
    }



    public function getProfessionnel(): ?string
    {
        return $this->professionnel;
    }

    public function setProfessionnel(?string $professionnel): static
    {
        $this->professionnel = $professionnel;

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

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getNationate(): ?Pays
    {
        return $this->nationate;
    }

    public function setNationate(?Pays $nationate): static
    {
        $this->nationate = $nationate;

        return $this;
    }



    public function getSituation(): ?string
    {
        return $this->situation;
    }

    public function setSituation(string $situation): static
    {
        $this->situation = $situation;

        return $this;
    }

    public function getDiplome(): ?string
    {
        return $this->diplome;
    }

    public function setDiplome(string $diplome): static
    {
        $this->diplome = $diplome;

        return $this;
    }

    public function getDateDiplome(): ?\DateTimeInterface
    {
        return $this->dateDiplome;
    }

    public function setDateDiplome(?\DateTimeInterface $dateDiplome): static
    {
        $this->dateDiplome = $dateDiplome;

        return $this;
    }

    /*     public function getSituationPro(): ?string
    {
        return $this->situationPro;
    }

    public function setSituationPro(string $situationPro): static
    {
        $this->situationPro = $situationPro;

        return $this;
    }
 */
    public function getPhoto(): ?Fichier
    {
        return $this->photo;
    }

    public function setPhoto(?Fichier $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getDiplomeFile(): ?Fichier
    {
        return $this->diplomeFile;
    }

    public function setDiplomeFile(?Fichier $diplomeFile): static
    {
        $this->diplomeFile = $diplomeFile;

        return $this;
    }

    public function getCni(): ?Fichier
    {
        return $this->cni;
    }

    public function setCni(?Fichier $cni): static
    {
        $this->cni = $cni;

        return $this;
    }

    public function getCv(): ?Fichier
    {
        return $this->cv;
    }

    public function setCv(?Fichier $cv): static
    {
        $this->cv = $cv;

        return $this;
    }

    public function getCasier(): ?Fichier
    {
        return $this->casier;
    }

    public function setCasier(?Fichier $casier): static
    {
        $this->casier = $casier;

        return $this;
    }

    public function getCertificat(): ?Fichier
    {
        return $this->certificat;
    }

    public function setCertificat(?Fichier $certificat): static
    {
        $this->certificat = $certificat;

        return $this;
    }

    /**
     * Get the value of prenoms
     */
    public function getPrenoms()
    {
        return $this->prenoms;
    }

    /**
     * Set the value of prenoms
     *
     * @return  self
     */
    public function setPrenoms($prenoms)
    {
        $this->prenoms = $prenoms;

        return $this;
    }
    public function getDatePremierDiplome(): ?\DateTimeInterface
    {
        return $this->datePremierDiplome;
    }

    public function setDatePremierDiplome(?\DateTimeInterface $datePremierDiplome): static
    {
        $this->datePremierDiplome = $datePremierDiplome;

        return $this;
    }

    public function getSpecialite(): ?Specialite
    {
        return $this->specialite;
    }

    public function setSpecialite(?Specialite $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }



    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getLieuDiplome(): ?string
    {
        return $this->lieuDiplome;
    }

    public function setLieuDiplome(string $lieuDiplome): static
    {
        $this->lieuDiplome = $lieuDiplome;

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

    public function getPoleSanitaire(): ?string
    {
        return $this->poleSanitaire;
    }

    public function setPoleSanitaire(?string $poleSanitaire): static
    {
        $this->poleSanitaire = $poleSanitaire;

        return $this;
    }

    public function getPoleSanitairePro(): ?string
    {
        return $this->poleSanitairePro;
    }

    public function setPoleSanitairePro(?string $poleSanitairePro): static
    {
        $this->poleSanitairePro = $poleSanitairePro;

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

    public function getLieuExercicePro(): ?string
    {
        return $this->lieuExercicePro;
    }

    public function setLieuExercicePro(?string $lieuExercicePro): static
    {
        $this->lieuExercicePro = $lieuExercicePro;

        return $this;
    }

    public function getSituationPro(): ?SituationProfessionnelle
    {
        return $this->situationPro;
    }

    public function setSituationPro(?SituationProfessionnelle $situationPro): static
    {
        $this->situationPro = $situationPro;

        return $this;
    }

    public function getOrganisationNom(): ?string
    {
        return $this->organisationNom;
    }

    public function setOrganisationNom(?string $organisationNom): static
    {
        $this->organisationNom = $organisationNom;

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

    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    public function setCommune(?Commune $commune): static
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

    public function getAppartenirOrdre(): ?string
    {
        return $this->appartenirOrdre;
    }

    public function setAppartenirOrdre(?string $appartenirOrdre): static
    {
        $this->appartenirOrdre = $appartenirOrdre;

        return $this;
    }

    public function getNumeroInscription(): ?string
    {
        return $this->numeroInscription;
    }

    public function setNumeroInscription(?string $numeroInscription): static
    {
        $this->numeroInscription = $numeroInscription;

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

    public function getDateValidation(): ?\DateTimeInterface
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTimeInterface $dateValidation): static
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }

    public function getTypeDiplome(): ?TypeDiplome
    {
        return $this->typeDiplome;
    }

    public function setTypeDiplome(?TypeDiplome $typeDiplome): static
    {
        $this->typeDiplome = $typeDiplome;

        return $this;
    }

    public function getStatusPro(): ?StatusPro
    {
        return $this->statusPro;
    }

    public function setStatusPro(?StatusPro $statusPro): static
    {
        $this->statusPro = $statusPro;

        return $this;
    }

    public function getLieuObtentionDiplome(): ?LieuDiplome
    {
        return $this->lieuObtentionDiplome;
    }

    public function setLieuObtentionDiplome(?LieuDiplome $lieuObtentionDiplome): static
    {
        $this->lieuObtentionDiplome = $lieuObtentionDiplome;

        return $this;
    }

    public function getSpecialiteAutre(): ?string
    {
        return $this->specialiteAutre;
    }

    public function setSpecialiteAutre(?string $specialiteAutre): static
    {
        $this->specialiteAutre = $specialiteAutre;

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

    public function getOrdre(): ?Ordre
    {
        return $this->ordre;
    }

    public function setOrdre(?Ordre $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

   
}
