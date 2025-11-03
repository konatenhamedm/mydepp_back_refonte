<?php

namespace App\Service;

use App\Entity\Etablissement;
use App\Entity\Professionnel;
use App\Entity\Document;
use App\Entity\DocumentOep;
use App\Entity\User;
use App\Repository\CiviliteRepository;
use App\Repository\CommuneRepository;
use App\Repository\DistrictRepository;
use App\Repository\DocumentOepTempRepository;
use App\Repository\DocumentRepository;
use App\Repository\DocumentOepRepository;
use App\Repository\EtablissementRepository;
use App\Repository\LieuDiplomeRepository;
use App\Repository\NiveauInterventionRepository;
use App\Repository\PaysRepository;
use App\Repository\ProfessionRepository;
use App\Repository\RegionRepository;
use App\Repository\SituationProfessionnelleRepository;
use App\Repository\StatusProRepository;
use App\Repository\TempEtablissementRepository;
use App\Repository\TempProfessionnelRepository;
use App\Repository\TypeDiplomeRepository;
use App\Repository\TypePersonneRepository;
use App\Repository\VilleRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Adaptateur pour la logique métier existante
 * Ce service contient toutes les méthodes qui étaient dans PaiementService
 * et qui gèrent la création/mise à jour des entités métier
 */
class PaiementBusinessLogicService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CiviliteRepository $civiliteRepository,
        private RegionRepository $regionRepository,
        private DistrictRepository $districtRepository,
        private CommuneRepository $communeRepository,
        private VilleRepository $villeRepository,
        private PaysRepository $paysRepository,
        private ProfessionRepository $professionRepository,
        private StatusProRepository $statusProRepository,
        private TypeDiplomeRepository $typeDiplomeRepository,
        private LieuDiplomeRepository $lieuDiplomeRepository,
        private SituationProfessionnelleRepository $situationProfessionnelleRepository,
        private TempProfessionnelRepository $tempProfessionnelRepository,
        private TempEtablissementRepository $tempEtablissementRepository,
        private TypePersonneRepository $typePersonneRepository,
        private NiveauInterventionRepository $niveauInterventionRepository,
        private DocumentOepTempRepository $documentOepTempRepository,
        private DocumentOepRepository $documentOepRepository,
        private DocumentRepository $documentRepository,
        private EtablissementRepository $etablissementRepository,
        private UserPasswordHasherInterface $hasher,
        private SendMailService $sendMailService,
    ) {}

    /**
     * Met à jour ou crée un professionnel après un paiement réussi
     */
    public function updateProfessionnel(string $reference): array
    {
        $dataTemp = $this->tempProfessionnelRepository->findOneBy(['reference' => $reference]);

        if (!$dataTemp) {
            throw new \Exception('Données temporaires du professionnel non trouvées');
        }

        $professionnel = new Professionnel();

        // Informations de base
        $professionnel->setPoleSanitaire($dataTemp->getPoleSanitaire());
        $professionnel->setLieuObtentionDiplome(
            $this->lieuDiplomeRepository->find($dataTemp->getLieuDiplome())
        );
        $professionnel->setDateValidation(new DateTime());
        
        // Localisation
        $professionnel->setRegion($this->regionRepository->find($dataTemp->getRegion()));
        $professionnel->setDistrict($this->districtRepository->find($dataTemp->getDistrict()));
        $professionnel->setVille($this->villeRepository->find($dataTemp->getVille()));
        $professionnel->setCommune($this->communeRepository->find($dataTemp->getCommune()));
        $professionnel->setQuartier($dataTemp->getQuartier());

        // Informations professionnelles
        $professionnel->setStatusPro($this->statusProRepository->find($dataTemp->getStatusPro()));
        $professionnel->setTypeDiplome($this->typeDiplomeRepository->find($dataTemp->getTypeDiplome()));
        $professionnel->setNom($dataTemp->getNom());
        $professionnel->setPrenoms($dataTemp->getPrenoms());
        $professionnel->setProfessionnel($dataTemp->getProfessionnel());
        $professionnel->setSpecialiteAutre($dataTemp->getSpecialiteAutre());
        $professionnel->setEmail($dataTemp->getEmailAutre());
        $professionnel->setLieuExercicePro($dataTemp->getLieuExercicePro());

        // Statut
        if ($dataTemp->getCode()) {
            $professionnel->setCode($dataTemp->getCode());
            $professionnel->setStatus("renouvellement");
        } else {
            $professionnel->setStatus("attente");
        }

        // Coordonnées
        $professionnel->setNumber($dataTemp->getNumber());
        $professionnel->setEmailPro($dataTemp->getEmailPro());
        $professionnel->setProfession(
            $this->professionRepository->findOneBy(['code' => $dataTemp->getProfession()])
        );

        // Appartenance
        $professionnel->setAppartenirOrganisation($dataTemp->getAppartenirOrganisation());
        $professionnel->setAppartenirOrdre($dataTemp->getAppartenirOrdre());
        $professionnel->setLieuDiplome($dataTemp->getLieuDiplome());

        // Civilité et nationalité
        if ($dataTemp->getCivilite()) {
            $professionnel->setCivilite($this->civiliteRepository->find($dataTemp->getCivilite()));
        }
        if ($dataTemp->getNationate()) {
            $professionnel->setNationate($this->paysRepository->find($dataTemp->getNationate()));
        }

        // Diplômes et dates
        $professionnel->setDateDiplome(new DateTimeImmutable($dataTemp->getDateDiplome()));
        $professionnel->setDiplome($dataTemp->getDiplome());
        $professionnel->setSituation($dataTemp->getSituation());
        $professionnel->setDateNaissance(new DateTimeImmutable($dataTemp->getDateNaissance()));
        $professionnel->setPoleSanitairePro($dataTemp->getPoleSanitairePro());
        $professionnel->setSituationPro(
            $this->situationProfessionnelleRepository->find($dataTemp->getSituationPro())
        );
        $professionnel->setDatePremierDiplome($dataTemp->getDatePremierDiplome());

        // Organisation et ordre
        if ($dataTemp->getAppartenirOrganisation() == "oui") {
            $professionnel->setOrganisationNom($dataTemp->getOrganisationNom());
        }
        if ($dataTemp->getAppartenirOrdre() == "oui") {
            $professionnel->setNumeroInscription($dataTemp->getNumeroInscription());
        }

        // Documents
        $professionnel->setCv($dataTemp->getCv());
        $professionnel->setPhoto($dataTemp->getPhoto());
        $professionnel->setCasier($dataTemp->getCasier());
        $professionnel->setCni($dataTemp->getCni());
        $professionnel->setDiplomeFile($dataTemp->getDiplomeFile());
        $professionnel->setCertificat($dataTemp->getCertificat());

        // Dates de création/modification
        $professionnel->setUpdatedAt(new DateTime());
        $professionnel->setCreatedAtValue(new DateTime());

        $this->em->persist($professionnel);
        $this->em->flush();

        // Création de l'utilisateur
        $user = new User();
        $user->setEmail($dataTemp->getEmail());
        $user->setPassword($this->hasher->hashPassword($user, $dataTemp->getPassword()));
        $user->setRoles(['ROLE_MEMBRE']);
        $user->setPersonne($professionnel);
        $user->setTypeUser(User::TYPE['PROFESSIONNEL']);
        $user->setPayement(User::PAYEMENT['payed']);
        $user->setCreatedBy($user);
        $user->setUpdatedBy($user);
        $user->setUpdatedAt(new DateTime());
        $user->setCreatedAtValue(new DateTime());
        
        $this->em->persist($user);
        $this->em->flush();

        // Mise à jour des références
        $professionnel->setCreatedBy($user);
        $professionnel->setUpdatedBy($user);
        $this->em->persist($professionnel);
        $this->em->flush();

        // Suppression des données temporaires
        $this->tempProfessionnelRepository->remove($dataTemp, true);

        // Envoi du mail
        $info_user = [
            'login' => $dataTemp->getEmail(),
        ];
        $context = compact('info_user');
        
        $this->sendMailService->send(
            'depps@leadagro.net',
            $dataTemp->getEmail(),
            'Informations',
            'content_mail',
            $context
        );

        return [
            'code' => 200,
            'data' => $professionnel,
            'user' => $user
        ];
    }

    /**
     * Met à jour ou crée un établissement après un paiement réussi
     */
    public function updateEtablissement(string $reference): array
    {
        $dataTemp = $this->tempEtablissementRepository->findOneBy(['reference' => $reference]);

        if (!$dataTemp) {
            throw new \Exception('Données temporaires de l\'établissement non trouvées');
        }

        $etablissement = new Etablissement();
        $etablissement->setStatus('acp_attente_dossier_depot_service_courrier');

        // Informations générales
        if ($dataTemp->getTypePersonne()) {
            $etablissement->setTypePersonne(
                $this->typePersonneRepository->findOneByCode($dataTemp->getTypePersonne())
            );
        }

        // Documents
        if ($dataTemp->getDocumentTemporaires()) {
            foreach ($dataTemp->getDocumentTemporaires() as $doc) {
                $document = new Document();
                $document->setPath($doc->getPath());
                $document->setLibelle($doc->getLibelle() ?: 'Document sans libellé');
                $document->setLibelleGroupe($doc->getLibelleGroupe());
                $etablissement->addDocument($document);
            }
        }

        // Informations de base
        $etablissement->setNiveauIntervention(
            $this->niveauInterventionRepository->find($dataTemp->getNiveauIntervention())
        );
        $etablissement->setDenomination($dataTemp->getDenomination());
        $etablissement->setNom($dataTemp->getNom());
        $etablissement->setPrenoms($dataTemp->getPrenoms());
        $etablissement->setBp($dataTemp->getBp());
        $etablissement->setEmailAutre($dataTemp->getEmailAutre());
        $etablissement->setTelephone($dataTemp->getTelephone());
        $etablissement->setTypeSociete($dataTemp->getTypeSociete());
        $etablissement->setAdresse($dataTemp->getAdresse());
        $etablissement->setNomRepresentant($dataTemp->getNomRepresentant());

        $this->em->persist($etablissement);
        $this->em->flush();

        // Création de l'utilisateur
        $user = new User();
        $user->setEmail($dataTemp->getEmail());
        $user->setPassword($this->hasher->hashPassword($user, $dataTemp->getPassword()));
        $user->setRoles(['ROLE_MEMBRE']);
        $user->setPersonne($etablissement);
        $user->setTypeUser(User::TYPE['ETABLISSEMENT']);
        $user->setPayement(User::PAYEMENT['payed']);
        $user->setCreatedBy($user);
        $user->setCreatedAtValue(new DateTime());
        $user->setUpdatedBy($user);
        
        $this->em->persist($user);
        $this->em->flush();

        // Mise à jour des références
        $etablissement->setCreatedBy($user);
        $etablissement->setCreatedAtValue(new DateTime());
        $etablissement->setUpdatedBy($user);
        $this->em->persist($etablissement);
        $this->em->flush();

        // Nettoyage des documents temporaires
        foreach ($dataTemp->getDocumentTemporaires() as $doc) {
            $this->em->remove($doc);
        }
        
        // Suppression des données temporaires
        $this->em->remove($dataTemp);
        $this->em->flush();

        // Envoi du mail
        $info_user = [
            'login' => $dataTemp->getEmail(),
        ];
        $context = compact('info_user');
        
        $this->sendMailService->send(
            'depps@leadagro.net',
            $dataTemp->getEmail(),
            'Informations',
            'content_mail',
            $context
        );

        return [
            'code' => 200,
            'data' => $etablissement,
            'user' => $user
        ];
    }

    /**
     * Met à jour les documents OEP après un paiement réussi
     */
    public function updateDocumentOep(string $reference): array
    {
        $dataTemp = $this->documentOepTempRepository->findBy(['reference' => $reference]);

        if (!$dataTemp) {
            throw new \Exception('Documents OEP temporaires non trouvés');
        }

        $createdDocuments = [];

        foreach ($dataTemp as $doc) {
            $etablissement = $this->etablissementRepository->find($doc->getEtablissement());
            
            if (!$etablissement) {
                continue;
            }

            $document = new DocumentOep();
            $libelle = $doc->getLibelle() ?: 'Document sans libellé';
            $document->setPath($doc->getPath());
            $document->setLibelle($libelle);
            $document->setLibelleGroupe($doc->getLibelleGroupe());
            $document->setEtablissement($etablissement);

            $this->em->persist($document);
            $createdDocuments[] = $document;
        }

        $this->em->flush();

        // Mise à jour du statut de l'établissement
        if (!empty($dataTemp)) {
            $firstDoc = reset($dataTemp);
            $etablissement = $this->etablissementRepository->find($firstDoc->getEtablissement());
            
            if ($etablissement) {
                $etablissement->setStatus('oep_demande_initie');
                $this->em->persist($etablissement);
                $this->em->flush();
            }
        }

        // Nettoyage optionnel des documents temporaires
        // foreach ($dataTemp as $t) {
        //     $this->documentOepTempRepository->remove($t, true);
        // }

        return [
            'code' => 200,
            'documents_created' => count($createdDocuments)
        ];
    }

    /**
     * Traite le renouvellement d'un professionnel
     */
    public function renouvelerProfessionnel(User $user): array
    {
        $professionnel = $user->getPersonne();

        if (!$professionnel instanceof Professionnel) {
            throw new \Exception('L\'utilisateur n\'est pas un professionnel');
        }

        // Calculer la nouvelle date de validation
        $dernierAbonnement = $professionnel->getDateValidation();
        $now = new \DateTime();

        if (!$dernierAbonnement || $dernierAbonnement < $now) {
            // L'ancien est expiré ou inexistant
            $dateRenouvellement = $now;
        } else {
            // Encore actif, on prolonge d'un an
            $dateRenouvellement = (clone $dernierAbonnement)->modify('+1 year');
        }

        $professionnel->setStatus("a_jour");
        $professionnel->setDateValidation($dateRenouvellement);
        
        $this->em->persist($professionnel);
        $this->em->flush();

        return [
            'code' => 200,
            'nouvelle_date_validation' => $dateRenouvellement->format('Y-m-d'),
            'status' => 'a_jour'
        ];
    }

    /**
     * Traite le renouvellement d'un établissement
     */
    public function renouvelerEtablissement(User $user): array
    {
        $etablissement = $user->getPersonne();

        if (!$etablissement instanceof Etablissement) {
            throw new \Exception('L\'utilisateur n\'est pas un établissement');
        }

        // Logique de renouvellement pour l'établissement
        // À adapter selon vos besoins métier
        $now = new \DateTime();
        
        $etablissement->setStatus("a_jour");
        $etablissement->setUpdatedAt($now);
        
        $this->em->persist($etablissement);
        $this->em->flush();

        return [
            'code' => 200,
            'status' => 'a_jour'
        ];
    }
}