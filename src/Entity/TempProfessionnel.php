<?php

namespace App\Entity;

use App\Repository\TempProfessionnelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups as Group;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TempProfessionnelRepository::class)]
class TempProfessionnel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Group(["group1", "group_user", 'group_pro'])]
    private ?string $username = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Email]
    #[Group(["group1", "group_user", 'group_pro'])]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;



    //ETAPE 2

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1", "group_user", 'group_pro'])]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user_trx"])]
    private ?string $poleSanitaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user_trx"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $professionnel = null; // structure 

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user_trx"])]
    private ?string $prenoms = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user_trx"])]
    private ?string $lieuExercicePro = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user_trx"])]
    private ?string $emailAutre = null;


    //ETAPE 3

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $profession = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $civilite = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $emailPro = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $dateDiplome = null;

    //
    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $dateNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro","group_user_trx"])]
    private ?string $number = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $lieuDiplome = null;
    
    //
    
    
    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1","group_pro"])]
    private ?string $nationate = null;

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

    
    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $situationPro = null;

    



    public function getId(): ?int
    {
        return $this->id;
    }

 
    #[ORM\Column]
    #[Group(['group_pro'])]
    private ?string $appartenirOrganisation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $genre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $reason = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $status = null;



    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier","group_pro"])]
    private ?Fichier $photo = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier","group_pro"])]
    private ?Fichier $diplomeFile = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier","group_pro"])]
    private ?Fichier $cni = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier","group_pro"])]
    private ?Fichier $cv = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier","group_pro"])]
    private ?Fichier $casier = null;

    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier","group_pro"])]
    private ?Fichier $certificat = null;

   
    #[Group(["group_pro"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specialite = null;


    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $ville = null;



    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeUser = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group_pro"])]
    private ?string $organisationNom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $district = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commune = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $quartier = null;

    #[ORM\Column(length: 255,nullable:true)]
    private ?string $appartenirOrdre = null;

    #[ORM\Column(length: 255,nullable:true)]
    private ?string $numeroInscription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeDiplome = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statusPro = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieuObtentionDiplome = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specialiteAutre = null;


    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }



    /**
     * Get the value of appartenirOrganisation
     */ 
    public function getAppartenirOrganisation()
    {
        return $this->appartenirOrganisation;
    }

    /**
     * Set the value of appartenirOrganisation
     *
     * @return  self
     */ 
    public function setAppartenirOrganisation($appartenirOrganisation)
    {
        $this->appartenirOrganisation = $appartenirOrganisation;

        return $this;
    }

    /**
     * Get the value of genre
     */ 
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set the value of genre
     *
     * @return  self
     */ 
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get the value of reason
     */ 
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set the value of reason
     *
     * @return  self
     */ 
    public function setReason($reason)
    {
        $this->reason = $reason;

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
     * Get the value of number
     */ 
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set the value of number
     *
     * @return  self
     */ 
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get the value of nom
     */ 
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set the value of nom
     *
     * @return  self
     */ 
    public function setNom($nom)
    {
        $this->nom = $nom;

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

    /**
     * Get the value of emailPro
     */ 
    public function getEmailPro()
    {
        return $this->emailPro;
    }

    /**
     * Set the value of emailPro
     *
     * @return  self
     */ 
    public function setEmailPro($emailPro)
    {
        $this->emailPro = $emailPro;

        return $this;
    }

   

    /**
     * Get the value of professionnel
     */ 
    public function getProfessionnel()
    {
        return $this->professionnel;
    }

    /**
     * Set the value of professionnel
     *
     * @return  self
     */ 
    public function setProfessionnel($professionnel)
    {
        $this->professionnel = $professionnel;

        return $this;
    }

  

    /**
     * Get the value of profession
     */ 
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * Set the value of profession
     *
     * @return  self
     */ 
    public function setProfession($profession)
    {
        $this->profession = $profession;

        return $this;
    }

    /**
     * Get the value of dateNaissance
     */ 
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * Set the value of dateNaissance
     *
     * @return  self
     */ 
    public function setDateNaissance($dateNaissance)
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    /**
     * Get the value of civilite
     */ 
    public function getCivilite()
    {
        return $this->civilite;
    }

    /**
     * Set the value of civilite
     *
     * @return  self
     */ 
    public function setCivilite($civilite)
    {
        $this->civilite = $civilite;

        return $this;
    }

    /**
     * Get the value of nationate
     */ 
    public function getNationate()
    {
        return $this->nationate;
    }

    /**
     * Set the value of nationate
     *
     * @return  self
     */ 
    public function setNationate($nationate)
    {
        $this->nationate = $nationate;

        return $this;
    }


    /**
     * Get the value of situation
     */ 
    public function getSituation()
    {
        return $this->situation;
    }

    /**
     * Set the value of situation
     *
     * @return  self
     */ 
    public function setSituation($situation)
    {
        $this->situation = $situation;

        return $this;
    }

    /**
     * Get the value of diplome
     */ 
    public function getDiplome()
    {
        return $this->diplome;
    }

    /**
     * Set the value of diplome
     *
     * @return  self
     */ 
    public function setDiplome($diplome)
    {
        $this->diplome = $diplome;

        return $this;
    }

    /**
     * Get the value of dateDiplome
     */ 
    public function getDateDiplome()
    {
        return $this->dateDiplome;
    }

    /**
     * Set the value of dateDiplome
     *
     * @return  self
     */ 
    public function setDateDiplome($dateDiplome)
    {
        $this->dateDiplome = $dateDiplome;

        return $this;
    }

 

    /**
     * Get the value of situationPro
     */ 
    public function getSituationPro()
    {
        return $this->situationPro;
    }

    /**
     * Set the value of situationPro
     *
     * @return  self
     */ 
    public function setSituationPro($situationPro)
    {
        $this->situationPro = $situationPro;

        return $this;
    }

    /**
     * Get the value of photo
     */ 
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set the value of photo
     *
     * @return  self
     */ 
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get the value of diplomeFile
     */ 
    public function getDiplomeFile()
    {
        return $this->diplomeFile;
    }

    /**
     * Set the value of diplomeFile
     *
     * @return  self
     */ 
    public function setDiplomeFile($diplomeFile)
    {
        $this->diplomeFile = $diplomeFile;

        return $this;
    }

    /**
     * Get the value of cni
     */ 
    public function getCni()
    {
        return $this->cni;
    }

    /**
     * Set the value of cni
     *
     * @return  self
     */ 
    public function setCni($cni)
    {
        $this->cni = $cni;

        return $this;
    }

    /**
     * Get the value of cv
     */ 
    public function getCv()
    {
        return $this->cv;
    }

    /**
     * Set the value of cv
     *
     * @return  self
     */ 
    public function setCv($cv)
    {
        $this->cv = $cv;

        return $this;
    }

    /**
     * Get the value of casier
     */ 
    public function getCasier()
    {
        return $this->casier;
    }

    /**
     * Set the value of casier
     *
     * @return  self
     */ 
    public function setCasier($casier)
    {
        $this->casier = $casier;

        return $this;
    }

    /**
     * Get the value of certificat
     */ 
    public function getCertificat()
    {
        return $this->certificat;
    }

    /**
     * Set the value of certificat
     *
     * @return  self
     */ 
    public function setCertificat($certificat)
    {
        $this->certificat = $certificat;

        return $this;
    }

    /**
     * Get the value of specialite
     */ 
    public function getSpecialite()
    {
        return $this->specialite;
    }

    /**
     * Set the value of specialite
     *
     * @return  self
     */ 
    public function setSpecialite($specialite)
    {
        $this->specialite = $specialite;

        return $this;
    }

    /**
     * Get the value of lieuDiplome
     */ 
    public function getLieuDiplome()
    {
        return $this->lieuDiplome;
    }

    /**
     * Set the value of lieuDiplome
     *
     * @return  self
     */ 
    public function setLieuDiplome($lieuDiplome)
    {
        $this->lieuDiplome = $lieuDiplome;

        return $this;
    }

    /**
     * Get the value of ville
     */ 
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set the value of ville
     *
     * @return  self
     */ 
    public function setVille($ville)
    {
        $this->ville = $ville;

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


    public function getTypeUser(): ?string
    {
        return $this->typeUser;
    }

    public function setTypeUser(?string $typeUser): static
    {
        $this->typeUser = $typeUser;

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

    public function getLieuExercicePro(): ?string
    {
        return $this->lieuExercicePro;
    }

    public function setLieuExercicePro(?string $lieuExercicePro): static
    {
        $this->lieuExercicePro = $lieuExercicePro;

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

    public function getDatePremierDiplome(): ?\DateTimeInterface
    {
        return $this->datePremierDiplome;
    }

    public function setDatePremierDiplome(?\DateTimeInterface $datePremierDiplome): static
    {
        $this->datePremierDiplome = $datePremierDiplome;

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

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getOrganisationNom(): ?string
    {
        return $this->organisationNom;
    }

    public function setOrganisationNom(string $organisationNom): static
    {
        $this->organisationNom = $organisationNom;

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

    public function getAppartenirOrdre(): ?string
    {
        return $this->appartenirOrdre;
    }

    public function setAppartenirOrdre(string $appartenirOrdre): static
    {
        $this->appartenirOrdre = $appartenirOrdre;

        return $this;
    }

    public function getNumeroInscription(): ?string
    {
        return $this->numeroInscription;
    }

    public function setNumeroInscription(string $numeroInscription): static
    {
        $this->numeroInscription = $numeroInscription;

        return $this;
    }

    public function getTypeDiplome(): ?string
    {
        return $this->typeDiplome;
    }

    public function setTypeDiplome(?string $typeDiplome): static
    {
        $this->typeDiplome = $typeDiplome;

        return $this;
    }

    public function getStatusPro(): ?string
    {
        return $this->statusPro;
    }

    public function setStatusPro(?string $statusPro): static
    {
        $this->statusPro = $statusPro;

        return $this;
    }

    public function getLieuObtentionDiplome(): ?string
    {
        return $this->lieuObtentionDiplome;
    }

    public function setLieuObtentionDiplome(?string $lieuObtentionDiplome): static
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
}
