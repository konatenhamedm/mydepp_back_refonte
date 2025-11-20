<?php

namespace App\Service;

use App\Controller\FileTrait;
use App\Entity\Civilite;
use App\Entity\Document;
use App\Entity\DocumentOep;
use App\Entity\Etablissement;
use App\Entity\Genre;
use App\Entity\Organisation;
use App\Entity\Professionnel;
use App\Entity\TempEtablissement;
use App\Entity\TempProfessionnel;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\CiviliteRepository;
use App\Repository\CommuneRepository;
use App\Repository\DistrictRepository;
use App\Repository\DocumentOepRepository;
use App\Repository\DocumentOepTempRepository;
use App\Repository\DocumentRepository;
use App\Repository\EtablissementRepository;
use App\Repository\GenreRepository;
use App\Repository\LieuDiplomeRepository;
use App\Repository\NiveauInterventionRepository;
use App\Repository\PaysRepository;
use App\Repository\ProfessionRepository;
use App\Repository\RegionRepository;
use App\Repository\SituationProfessionnelleRepository;
use App\Repository\SpecialiteRepository;
use App\Repository\StatusProRepository;
use App\Repository\TempEtablissementRepository;
use App\Repository\TempProfessionnelRepository;
use App\Repository\TransactionRepository;
use App\Repository\TypeDiplomeRepository;
use App\Repository\TypePersonneRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaiementService
{
    use FileTrait;
    protected const UPLOAD_PATH = 'media_deeps';


    private string $apiKey;
    private string $merchantId;
    private string $paiementUrl;

    public function __construct(
        private TransactionRepository $transactionRepository,
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $em,
        private ParameterBagInterface $params,
        private Utils $utils,
        private ValidatorInterface $validator,
        private UrlGeneratorInterface $urlGenerator,
        private CiviliteRepository $civiliteRepository,
        private GenreRepository $genreRepository,
        private SpecialiteRepository $specialiteRepository,
        private TempProfessionnelRepository $tempProfessionnelRepository,
        private SituationProfessionnelleRepository $situationProfessionnelleRepository,
        private TempEtablissementRepository $tempEtablissementRepository,
        private TypePersonneRepository $typePersonneRepository,
        private NiveauInterventionRepository $niveauInterventionRepository,
        private VilleRepository $villeRepository,
        private SendMailService $sendMailService,
        private PaysRepository $paysRepository,
        private UserPasswordHasherInterface $hasher,
        private  RegionRepository $regionRepository,
        private DistrictRepository $districtRepository,
        private CommuneRepository $communeRepository,
        private UserRepository $userRepository,
        private ProfessionRepository $professionRepository,
        private StatusProRepository $statusProRepository,
        private TypeDiplomeRepository $typeDiplomeRepository,
        private LieuDiplomeRepository $lieuDiplomeRepository,
        private DocumentOepTempRepository $documentOepTempRepository,
        private DocumentRepository $documentRepository,
        private DocumentOepRepository $documentOepRepository,
        private EtablissementRepository $etablissementRepository,
        


    ) {
        $this->apiKey = $params->get('API_KEY');
        $this->merchantId = $params->get('MERCHANT_ID');
        $this->paiementUrl = $params->get('PAIEMENT_URL');
    }

    private function numero()
    {

        $query = $this->em->createQueryBuilder();
        $query->select("count(a.id)")
            ->from(User::class, 'a');

        $nb = $query->getQuery()->getSingleScalarResult();
        if ($nb == 0) {
            $nb = 1;
        } else {
            $nb = $nb + 1;
        }
        return str_pad($nb, 3, '0', STR_PAD_LEFT);
    }


    public function methodeWebHook(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        $transaction = $this->transactionRepository->findOneBy(['reference' => $data['codePaiement']]);

        $transaction->setReferenceChannel($data['referencePaiement']);
        if ($data['code'] == 200) {
            $transaction->setState(1);

            $transaction->setChannel($data['moyenPaiement']);
            $transaction->setData(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->transactionRepository->add($transaction, true);
            $response = $transaction->getTypeUser() == "professionnel" ?  $this->updateProfessionnel($data['codePaiement']) :  $this->updateEtablissement($data['codePaiement']);
            if ($response) {
                if ($transaction->getTypeUser() == "professionnel") {
                    $temp =  $this->tempProfessionnelRepository->findOneBy(['reference' => $data['codePaiement']]);
                    $this->tempProfessionnelRepository->remove($temp, true);
                } else {
                    $temp =  $this->tempEtablissementRepository->findOneBy(['reference' => $data['codePaiement']]);
                    $this->tempEtablissementRepository->remove($temp, true);
                }
            }


              $info_user = [
                    'login' =>  $transaction->getUser()->getEmail(),

                ];

                $context = compact('info_user');

                $this->sendMailService->send(
                    'depps@leadagro.net',
                    $transaction->getUser()->getEmail(),
                    'Informations',
                    'content_mail',
                    $context
                );
        } else {
            $response = [
                'message' => 'Echec',
                'code' => 400
            ];
        }


        return $response;
    }
    public function methodeWebHookOep(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        $transaction = $this->transactionRepository->findOneBy(['reference' => $data['codePaiement']]);
        $etablissement = $this->etablissementRepository->findOneBy(['id' => $transaction->getUser()->getPersonne()]);

        $transaction->setReferenceChannel($data['referencePaiement']);
        if ($data['code'] == 200) {
            $transaction->setState(1);

            $transaction->setChannel($data['moyenPaiement']);
            $transaction->setData(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $this->transactionRepository->add($transaction, true);
            $response =  $this->updateDocumentOep($data['codePaiement']);
         /*    if ($response) {

                $temp =  $this->documentOepTempRepository->findBy(['reference' => $data['codePaiement']]);

                foreach ($temp as $t) {
                    $this->documentOepTempRepository->remove($t, true);
                }
            } */
            $etablissement->setStatus('oep_demande_initie');
            $this->em->persist($etablissement);
            $this->em->flush();
        } else {
            $response = [
                'message' => 'Echec',
                'code' => 400
            ];
        }


        return $response;
    }
    public function methodeWebHookRenouvellement(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        $transaction = $this->transactionRepository->findOneBy(['reference' => $data['codePaiement']]);

        $professionnel = $this->userRepository->find($transaction->getUser())->getPersonne();

        $transaction->setReferenceChannel($data['referencePaiement']);

        $dernierAbonnement = $this->transactionRepository->findOneBy(
            ['user' => $transaction->getUser(), 'state' => 1],
            ['createdAt' => 'DESC']
        );

        $now = new \DateTime();
        if (!$dernierAbonnement) {
            // Aucun abonnement encore
            $dateRenouvellement = $now;
        } else {
            $expiration = (clone $dernierAbonnement->getCreatedAt())->modify('+1 year');

            if ($expiration < $now) {
                // L'ancien est expiré
                $dateRenouvellement = $now;
            } else {
                // Encore actif, on prolonge à partir de la date d’expiration actuelle
                $dateRenouvellement = $expiration;
            }
        }

        if ($data['code'] == 200) {
            $transaction->setState(1);

            $transaction->setChannel($data['moyenPaiement']);
            $transaction->setData(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->transactionRepository->add($transaction, true);

            $professionnel->setStatus("a_jour");
            $professionnel->setDateValidation($dateRenouvellement);
            $professionnel->add($professionnel, true);
        } else {
            $response = [
                'message' => 'Echec',
                'code' => 400
            ];
        }


        return $response;
    }


    public function traiterPaiement(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        $montant = $request->get('type') == "professionnel" ? $this->professionRepository->findOneByCode($request->get('profession'))->getMontantNouvelleDemande() : $this->niveauInterventionRepository->find($request->get('niveauIntervention'))->getMontant();

        $transaction = new Transaction();
        $transaction->setChannel("");
        $transaction->setReference($this->genererNumero());
        $transaction->setMontant($montant);
        $transaction->setReferenceChannel("");
        $transaction->setType("NOUVELLE DEMANDE");
        $transaction->setTypeUser($request->get('type'));
        $transaction->setState(0);
        $transaction->setCreatedAtValue(new \DateTime());
        $transaction->setUpdatedAt(new \DateTime());

        $this->transactionRepository->add($transaction, true);

        if ($request->get('type') == "professionnel") {
            $requestData = [
                "code_paiement" => $transaction->getReference(),
                "nom_usager" => $request->get('nom'),
                "prenom_usager" => $request->get('prenoms'),
                "telephone" => $request->get('numero'),
                "email" => $request->get('email'),
                "libelle_article" => "DEMANDE D'ADHESION",
                "quantite" => 1,
                "montant" => $montant,
                "lib_order" => "PAIEMENT ONMCI",
                "Url_Retour" => "https://mydepp-front.pages.dev/inscription/" . $request->get('type'),
                "Url_Callback" => "https://backend.leadagro.net/api/paiement/info-paiement"
            ];
        } else {
            $requestData = [
                "code_paiement" => $transaction->getReference(),
                "nom_usager" => "Mydepps",
                "prenom_usager" => "Mydepps",
                "telephone" => "0704314164",
                "email" => $request->get('email'),
                "libelle_article" => "DEMANDE D'ACCORD DE PRINCIPE",
                "quantite" => 1,
                "montant" => $montant,
                "lib_order" => "PAIEMENT ONMCI",
                "Url_Retour" => "https://mydepp-front.pages.dev/inscription/" . $request->get('type'),
                "Url_Callback" => "https://backend.leadagro.net/api/paiement/info-paiement"
            ];
        }


        $response = $this->httpClient->request('POST', $this->paiementUrl, [
            'json' => $requestData,
            'headers' => [
                "ApiKey" => $this->apiKey,
                "MerchantId" => $this->merchantId,
                "Accept" => "application/json",
                "Content-Type" => "application/json",
            ],
            'verify_peer' => false,
            'verify_host' => false
        ]);

        $dataResponse = $response->toArray();

        return [
            'code' => 200,
            'url' => $dataResponse['url'] ?? null,
            'reference' => $transaction->getReference(),
            'type' => $request->get('type')
        ];
    }
    public function traiterPaiementOpe(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        $montant =  $this->niveauInterventionRepository->find($request->get('niveauIntervention'))->getMontantRenouvellement();

        $transaction = new Transaction();
        $transaction->setChannel("");
        $transaction->setUser($this->userRepository->find($request->get('user')));
        $transaction->setReference($this->genererNumero());
        $transaction->setMontant($montant);
        $transaction->setReferenceChannel("");
        $transaction->setType("OUVERTURE D'EXPLOITATION");
        $transaction->setTypeUser('etablissement');
        $transaction->setState(0);
        $transaction->setCreatedAtValue(new \DateTime());
        $transaction->setUpdatedAt(new \DateTime());

        $this->transactionRepository->add($transaction, true);


        $requestData = [
            "code_paiement" => $transaction->getReference(),
            "nom_usager" => "Mydepps",
            "prenom_usager" => "Mydepps",
            "telephone" => "0704314164",
            "email" => $request->get('email'),
            "libelle_article" => "OUVERTURE D'EXPLOITATION",
            "quantite" => 1,
            "montant" => $montant,
            "lib_order" => "PAIEMENT ONMCI",
            "Url_Retour" => "https://mydepp-front.pages.dev/site/dashboard_etablissement",
            "Url_Callback" => "https://backend.leadagro.net/api/paiement/info-paiement-oep"
        ];



        $response = $this->httpClient->request('POST', $this->paiementUrl, [
            'json' => $requestData,
            'headers' => [
                "ApiKey" => $this->apiKey,
                "MerchantId" => $this->merchantId,
                "Accept" => "application/json",
                "Content-Type" => "application/json",
            ],
            'verify_peer' => false,
            'verify_host' => false
        ]);

        $dataResponse = $response->toArray();

        return [
            'code' => 200,
            'url' => $dataResponse['url'] ?? null,
            'reference' => $transaction->getReference(),
            'type' => 'etablissement'
        ];
    }
    public function traiterPaiementRenouvellement(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->em->getRepository(User::class)->find($data['user']);

       // $montant = $this->professionRepository->findOneByCode($user->getPersonne()->getProfession())->getMontantRenouvellement();
        $montant = $user->getPersonne()->getProfession()->getMontantRenouvellement();

        $transaction = new Transaction();
        $transaction->setChannel("");
        $transaction->setReference($this->genererNumero());
        $transaction->setMontant($montant);
        $transaction->setReferenceChannel("");
        $transaction->setType("RENOUVELLEMENT");
        $transaction->setTypeUser($data['type']);
        $transaction->setUser($user);
        $transaction->setState(0);
        $transaction->setCreatedAtValue(new \DateTime());
        $transaction->setUpdatedAt(new \DateTime());

        $this->transactionRepository->add($transaction, true);

        $requestData = [
            "code_paiement" => $transaction->getReference(),
            "nom_usager" => $data['nom'],
            "prenom_usager" => $data['prenoms'],
            "telephone" => $data['numero'],
            "email" => $data['email'],
            "libelle_article" => "RENOUVELLEMENT D'ADHESION",
            "quantite" => 1,
            "montant" => $montant,
            "lib_order" => "PAIEMENT ONMCI",
            "Url_Retour" => "https://mydepp-front.pages.dev/site/dashboard",
            "Url_Callback" => "https://backend.leadagro.net/api/paiement/info-paiement-renouvellement"
        ];

        $response = $this->httpClient->request('POST', $this->paiementUrl, [
            'json' => $requestData,
            'headers' => [
                "ApiKey" => $this->apiKey,
                "MerchantId" => $this->merchantId,
                "Accept" => "application/json",
                "Content-Type" => "application/json",
            ],
            'verify_peer' => false,
            'verify_host' => false
        ]);

        $dataResponse = $response->toArray();

        return [
            'code' => 200,
            'url' => $dataResponse['url'] ?? null,
            'reference' => $transaction->getReference(),
            'type' => $data['type']
        ];
    }



    public function updateDocumentOep($reference)
    {

        $dataTemp = $this->documentOepTempRepository->findBy(['reference' => $reference]);
        $transaction = $this->transactionRepository->findOneBy(['reference' =>  $reference]);


        if ($dataTemp) {
            foreach ($dataTemp as $doc) {
                $user = $this->em->getRepository(User::class)->findOneBy(['personne' => $doc->getEtablissement()]);
                $etbalissement = $this->etablissementRepository->find($doc->getEtablissement());
                $document = new DocumentOep();
                $libelle = $doc->getLibelle() ?: 'Document sans libellé';
                $document->setPath($doc->getPath());
                $document->setLibelle($libelle);
                $document->setLibelleGroupe($doc->getLibelleGroupe());
                $document->setEtablissement($etbalissement);


                $transaction->setUser($user);
                $transaction->setCreatedBy($user);
                $transaction->setUpdatedBy($user);
                $this->transactionRepository->add($transaction, true);



                $this->em->persist($document);
                $this->em->flush();
            }
        }

        return  [
            'code' => 200,
        ];
    }
    public function updateProfessionnel($reference)
    {

        $dataTemp = $this->tempProfessionnelRepository->findOneBy(['reference' => $reference]);
        $transaction = $this->transactionRepository->findOneBy(['reference' =>  $reference]);

        /* dd($dataTemp); */

        $professionnel = new Professionnel();



        $professionnel->setPoleSanitaire($dataTemp->getPoleSanitaire());
        $professionnel->setLieuObtentionDiplome($this->lieuDiplomeRepository->find($dataTemp->getLieuObtentionDiplome()));
        $professionnel->setDateValidation(new DateTime());
        $professionnel->getLieuDiplome($dataTemp->getLieuDiplome());

       // $professionnel->setLieuObtentionDiplome();
        $professionnel->setRegion($this->regionRepository->find($dataTemp->getRegion()));
        $professionnel->setDistrict($this->districtRepository->find($dataTemp->getDistrict()));
        $professionnel->setVille($this->villeRepository->find($dataTemp->getVille()));
        $professionnel->setCommune($this->communeRepository->find($dataTemp->getCommune()));
        $professionnel->setQuartier($dataTemp->getQuartier());

        $professionnel->setStatusPro($this->statusProRepository->find($dataTemp->getStatusPro()));
        $professionnel->setTypeDiplome($this->typeDiplomeRepository->find($dataTemp->getTypeDiplome()));

        $professionnel->setNom($dataTemp->getNom());
        $professionnel->setPrenoms($dataTemp->getPrenoms());
        $professionnel->setProfessionnel($dataTemp->getProfessionnel());
        $professionnel->setSpecialiteAutre($dataTemp->getSpecialiteAutre());
        $professionnel->setEmail($dataTemp->getEmailAutre());
        $professionnel->setLieuExercicePro($dataTemp->getLieuExercicePro());



        if ($dataTemp->getCode()) {
            $professionnel->setCode($dataTemp->getCode());
            $professionnel->setStatus("renouvellement");
        } else {
            $professionnel->setStatus("attente");
        }

        $professionnel->setNumber($dataTemp->getNumber());
        $professionnel->setEmailPro($dataTemp->getEmailPro());
        $professionnel->setProfession($this->professionRepository->findOneBy(['code' => $dataTemp->getProfession()]));
        //$professionnel->setSpecialite($this->professionRepository->findOneBy(['code' => $dataTemp->getProfession()]));
        $professionnel->setAppartenirOrganisation($dataTemp->getAppartenirOrganisation());
        $professionnel->setAppartenirOrdre($dataTemp->getAppartenirOrdre());
        $professionnel->setLieuDiplome($dataTemp->getLieuDiplome());
        if ($dataTemp->getCivilite())
            $professionnel->setCivilite($this->civiliteRepository->find($dataTemp->getCivilite()));

        $professionnel->setDateDiplome(new DateTimeImmutable(($dataTemp->getDateDiplome())));
        if ($dataTemp->getNationate())
            $professionnel->setNationate($this->paysRepository->find($dataTemp->getNationate()));
        /*  $professionnel->setSituationPro($dataTemp->getSituationPro()); */
        $professionnel->setDiplome($dataTemp->getDiplome());
        $professionnel->setSituation($dataTemp->getSituation());
        $professionnel->setDateNaissance(new DateTimeImmutable(($dataTemp->getDateNaissance())));
        $professionnel->setPoleSanitairePro($dataTemp->getPoleSanitairePro());
        $professionnel->setSituationPro($this->situationProfessionnelleRepository->find($dataTemp->getSituationPro()));

        $professionnel->setDatePremierDiplome($dataTemp->getDatePremierDiplome());


        if ($dataTemp->getAppartenirOrganisation() == "oui") {


            $professionnel->setOrganisationNom($dataTemp->getOrganisationNom());
        }
        if ($dataTemp->getAppartenirOrdre() == "oui") {
            $professionnel->setNumeroInscription($dataTemp->getNumeroInscription());
        }


        $professionnel->setCv($dataTemp->getCv());
        $professionnel->setPhoto($dataTemp->getPhoto());
        $professionnel->setCasier($dataTemp->getCasier());
        $professionnel->setCni($dataTemp->getCni());
        $professionnel->setDiplomeFile($dataTemp->getDiplomeFile());
        $professionnel->setCertificat($dataTemp->getCertificat());
        $professionnel->setUpdatedAt(new DateTime());
        $professionnel->setCreatedAtValue(new DateTime());

        $this->em->persist($professionnel);
        $this->em->flush();



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

        $professionnel->setCreatedBy($user);
        $professionnel->setUpdatedBy($user);
        $this->em->persist($professionnel);
        $this->em->flush();

        $transaction->setUser($user);
        $transaction->setCreatedBy($user);
        $transaction->setUpdatedBy($user);
        $this->transactionRepository->add($transaction, true);



        $info_user = [
            'login' => $dataTemp->getEmail(),

        ];

        $context = compact('info_user');

        // TO DO
        $this->sendMailService->send(
            'depps@leadagro.net',
            $dataTemp->getEmail(),
            'Informations',
            'content_mail',
            $context
        );

        return  [
            'code' => 200,
            'data' => $professionnel
        ];
    }
    public function updateEtablissement($reference)
    {
        $dataTemp = $this->tempEtablissementRepository->findOneBy(['reference' => $reference]);
        $transaction = $this->transactionRepository->findOneBy(['reference' => $reference]);

        $etablissement = new Etablissement();
        $etablissement->setStatus('acp_attente_dossier_depot_service_courrier');

        // Informations générales
        if ($dataTemp->getTypePersonne()) {
            $etablissement->setTypePersonne(
                $this->typePersonneRepository->findOneByCode($dataTemp->getTypePersonne())
            );
        }

        if ($dataTemp->getDocumentTemporaires()) {
            foreach ($dataTemp->getDocumentTemporaires() as $doc) {
                $document = new Document();
                $document->setPath($doc->getPath());
                $document->setLibelle($doc->getLibelle() ?: 'Document sans libellé');
                $document->setLibelleGroupe($doc->getLibelleGroupe());
                $etablissement->addDocument($document);
            }
        }

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

        // Création user
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

        $etablissement->setCreatedBy($user);
        $etablissement->setCreatedAtValue(new DateTime());
        $etablissement->setUpdatedBy($user);
        $this->em->persist($etablissement);
        $this->em->flush();

        $transaction->setUser($user);
        $transaction->setCreatedBy($user);
        $transaction->setUpdatedBy($user);
        $this->transactionRepository->add($transaction, true);

      
        foreach ($dataTemp->getDocumentTemporaires() as $doc) {
            $this->em->remove($doc);
        }
        $this->em->remove($dataTemp);
        $this->em->flush();

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
            'data' => $etablissement
        ];
    }


    public function genererNumero(): string
    {
        $query = $this->em->createQueryBuilder();
        $query->select("count(a.id)")
            ->from(Transaction::class, 'a');

        $nb = $query->getQuery()->getSingleScalarResult();
        return ('DEPPS' . date("y") . date("m") . date("d") . date("H") . date("i") . date("s") . str_pad($nb + 1, 3, '0', STR_PAD_LEFT));
    }

    public function errorResponse($DTO, string $customMessage = ''): ?JsonResponse
    {
        $errors = $this->validator->validate($DTO);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            //array_push($arerrorMessagesray, 4)

            $response = [
                'code' => 400,
                'message' => 'Validation failed',
                'errors' => $errorMessages
            ];

            return new JsonResponse($response, 400);
        } elseif ($customMessage != '') {
            $errorMessages[] = $customMessage;
            $response = [
                'code' => 400,
                'message' => 'Validation failed',
                'errors' => $errorMessages
            ];

            return new JsonResponse($response, 400);
        }

        return null; // Pas d'erreurs, donc pas de réponse d'erreur
    }
}
