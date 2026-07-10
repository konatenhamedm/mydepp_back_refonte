<?php


namespace App\Controller\Apis;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Etablissement;
use App\Entity\Professionnel;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\CiviliteRepository;
use App\Repository\EtablissementRepository;
use App\Repository\ProfessionnelRepository;
use App\Repository\ProfessionRepository;
use App\Repository\SpecialiteRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;

#[Route('/api/statistique')]
class ApiStatistiqueController extends ApiInterface
{

    #[Route('/web-site-statistique', methods: ['GET'])]
    #[OA\Tag(name: 'statistiques')]
    public function webSiteStatistique(EtablissementRepository $etablissementRepository, ProfessionnelRepository $professionnelRepository)
    {
        try {
            //iiiiiiii
            $tab = [
                'countEtablissement' => count($etablissementRepository->findAll()),
                'countProfessionnel' => count($professionnelRepository->findAll()),
                'professionnelAjour' => count($professionnelRepository->allProfAjour())
            ];

            $response = $this->responseData($tab, 'group_user', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }

    #[Route('/stats-card', methods: ['GET'])]
    #[OA\Tag(name: 'statistiques')]
    public function statsCard(EtablissementRepository $etablissementRepository, ProfessionnelRepository $professionnelRepository)
    {
        try {
            $tab = [

                "professionnel" => [
                    "total" => count($professionnelRepository->findAll()),
                    "attente" => count($professionnelRepository->findBy(['status' => 'attente'])),
                    "accepte" => count($professionnelRepository->findBy(['status' => 'accepte'])),
                    "ajour" => count($professionnelRepository->findBy(['status' => 'ajour'])),
                    "refuse" => count($professionnelRepository->findBy(['status' => 'refuse'])),
                    "rejete" => count($professionnelRepository->findBy(['status' => 'rejete'])),
                    "valide" => count($professionnelRepository->findBy(['status' => 'valide'])),
                    "renouvellement" => count($professionnelRepository->findBy(['status' => 'renouvellement']))
                ],
                "etablissement" => [
                    "total" => count($etablissementRepository->findAll()),
                    "acp_attente_dossier_depot_service_courrier" => count($etablissementRepository->findBy(['status' => 'acp_attente_dossier_depot_service_courrier'])),
                    "acp_dossier_attente_validation_directrice" => count($etablissementRepository->findBy(['status' => 'acp_dossier_attente_validation_directrice'])),
                    "acp_dossier_valide_directrice" => count($etablissementRepository->findBy(['status' => 'acp_dossier_valide_directrice'])),
                    "oep_demande_initie" => count($etablissementRepository->findBy(['status' => 'oep_demande_initie'])),
                    "oep_dossier_imputer" => count($etablissementRepository->findBy(['status' => 'oep_dossier_imputer'])),
                    "oep_dossier_imputer_conforme_attente_planification_visite" => count($etablissementRepository->findBy(['status' => 'oep_dossier_imputer_conforme_attente_planification_visite'])),
                    "oep_dossier_imputer_non_conforme" => count($etablissementRepository->findBy(['status' => 'oep_dossier_imputer_non_conforme'])),
                    "oep_dossier_visite_programme" => count($etablissementRepository->findBy(['status' => 'oep_dossier_visite_programme'])),
                    "oep_visite_effectue_attente_validation_directrice" => count($etablissementRepository->findBy(['status' => 'oep_visite_effectue_attente_validation_directrice'])),
                    "oep_dossier_conforme" => count($etablissementRepository->findBy(['status' => 'oep_dossier_conforme'])),
                    "oep_dossier_non_conforme" => count($etablissementRepository->findBy(['status' => 'oep_dossier_non_conforme'])),
                ]
            ];

            $response = $this->responseData($tab, 'group_user', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {

            $this->setMessage($exception->getMessage());
            $response = $this->response('[]');
        }

        return $response;
    }




    #[Route('/info-dashboard', methods: ['GET'])]
    /**
     * Retourne les stats du dashboard.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'statistiques')]
    // 
    public function index(EtablissementRepository $etablissementRepository, ProfessionnelRepository $professionnelRepository): Response
    {
        try {


            $tab = [
                'countEtablissement' => count($etablissementRepository->findAll()),
                'countProfessionnel' => count($professionnelRepository->findAll()),
                'professionnelAjour' => count($professionnelRepository->allProfAjour())
            ];

            $response = $this->responseData($tab, 'group_user', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/info-dashboard/by/typeuser/{type}/{idUser}', methods: ['GET'])]
    /**
     * Retourne les stats du dashboard.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'statistiques')]
    // 
    public function indexByTypeUser(EtablissementRepository $etablissementRepository, TransactionRepository $transactionRepository, ProfessionnelRepository $professionnelRepository, $type, $idUser): Response
    {
        try {


            /*        • Combien de dossier sont en attente de traitement et imprimable
• Combien de dossier sont acceptés ou rejetés et imprimable
• Combien de dossiers sont traités et validés et imprimable
• Combien de dossiers sont traités et refusés et imprimable
• Faire un état des personnes inscrite par profession */
            if ($type == "INSTRUCTEUR") {
                $pAccepte = $professionnelRepository->findBy(['status' => 'accepte', 'imputation' => $idUser]);
                $pAttente = $professionnelRepository->findBy(['status' => 'attente', 'imputation' => $idUser]);
                $pRejet = $professionnelRepository->findBy(['status' => 'rejete', 'imputation' => $idUser]);
                $pRefuse = $professionnelRepository->findBy(['status' => 'refuse', 'imputation' => $idUser]);
                $pValide = $professionnelRepository->findBy(['status' => 'valide', 'imputation' => $idUser]);

                $eAccepte = $etablissementRepository->findBy(['status' => 'accepte', 'imputation' => $idUser]);
                $eAttente = $etablissementRepository->findBy(['status' => 'attente', 'imputation' => $idUser]);
                $eRejet = $etablissementRepository->findBy(['status' => 'rejete', 'imputation' => $idUser]);
                $eRefuse = $etablissementRepository->findBy(['status' => 'refuse', 'imputation' => $idUser]);
                $eValide = $etablissementRepository->findBy(['status' => 'valide', 'imputation' => $idUser]);

                $tab = [
                    'atttente' => count($pAttente) + count($eAttente),
                    'accepte' => count($pAccepte) + count($eAccepte),
                    'rejete' => count($pRejet) + count($eRejet),
                    'valide' => count($pValide) + count($eValide),
                    'a_jour' => count($pValide) + count($eValide),
                    'refuse' => count($pRefuse) + count($eRefuse),
                ];
            } elseif ($type == "SOUS-DIRECTEUR") {
                $tab = [
                    'atttente' => count($professionnelRepository->findBy(['status' => 'attente'])) + count($etablissementRepository->findBy(['status' => 'attente'])),
                    'accepte' => count($professionnelRepository->findBy(['status' => 'accepte'])) + count($etablissementRepository->findBy(['status' => 'accepte'])),
                    'rejete' => count($professionnelRepository->findBy(['status' => 'rejete'])) + count($etablissementRepository->findBy(['status' => 'rejete'])),
                    'valide' => count($professionnelRepository->findBy(['status' => 'valide'])) + count($etablissementRepository->findBy(['status' => 'valide'])),
                    'a_jour' => count($professionnelRepository->findBy(['status' => 'valide'])) + count($etablissementRepository->findBy(['status' => 'valide'])),
                    'refuse' => count($professionnelRepository->findBy(['status' => 'refuse'])) + count($etablissementRepository->findBy(['status' => 'refuse']))
                ];
            } elseif ($type == "COMPTABLE") {

                $allTransactions = $transactionRepository->getHistorique();

                // dd($allTransactions);

                ///recupere les transactions ou le champ data n'est pas null
                $dataValide = array_filter($allTransactions, fn($transaction) => $transaction);
                $tab = [
                    'montantTotal' => $transactionRepository->montantTotal(),
                    'nombreSuccess' => count($transactionRepository->findBy(['state' => 1])),
                    'nombreFail' => count($transactionRepository->findBy(['state' => 0])),
                    'toDayTransactionFail' => count($transactionRepository->transactionsEchoueesDuJour(0)),
                    'toDayTransactionSuccess' => count($transactionRepository->transactionsEchoueesDuJour(1)),

                ];
            } else {
                $tab = [
                    'countEtablissement' => count($etablissementRepository->findAll()),
                    'countProfessionnel' => count($professionnelRepository->findAll()),
                    'professionnelAjour' => count($professionnelRepository->allProfAjour()),
                    'atttente' => count($professionnelRepository->findBy(['status' => 'attente'])),
                    'accepte' => count($professionnelRepository->findBy(['status' => 'accepte'])),
                    'rejete' => count($professionnelRepository->findBy(['status' => 'rejete'])),
                    'valide' => count($professionnelRepository->findBy(['status' => 'valide'])),
                    'refuse' => count($professionnelRepository->findBy(['status' => 'refuse']))
                ];
            }





            $response = $this->responseData($tab, 'group_user', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/civilite', methods: ['GET'])]
    /**
     * Retourne les stats du dashboard.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'statistiques')]
    // 
    public function indexCivilite(EtablissementRepository $etablissementRepository, ProfessionnelRepository $professionnelRepository, CiviliteRepository $civiliteRepository): Response
    {
        try {
            $stats = $professionnelRepository->countProByCivilite();

            $formattedStats = [];
            $isFirst = true; // Pour le premier élément sélectionné dans le Pie Chart

            foreach ($stats as $index => $stat) {
                $nombre = $stat['nombre'];
                if ($nombre > 0) {
                    $formattedStats[] = [
                        'name' => $stat['libelle'],
                        'y' => (int) $stat['nombre'],
                        'sliced' => $isFirst,
                        'selected' => $isFirst
                    ];
                }

                $isFirst = false; // Désactiver la sélection après le premier élément
            }

            $formattedStats = array_reverse($formattedStats);

            $result = [
                'nombre' => $stats,
                'pieChart' => $formattedStats
            ];


            $response = $this->responseData($result, 'group_user', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/generale', methods: ['GET'])]
    public function indexGeneral(
        EtablissementRepository $etablissementRepository,
        ProfessionRepository $professionRepository,
        ProfessionnelRepository $professionnelRepository,
        CiviliteRepository $civiliteRepository,
        Request $request
    ): Response {

        try {
            $periode = $request->query->get('periode');
            $annee = $request->query->get('annee');
            $mois = $request->query->get('mois');
            $tranche = $request->query->get('tranche');
            $startDate = null;
            $endDate = null;
            if ($annee !== "null" && $annee !== null && $periode !== "null" && $periode !== null) {
                [$startDate, $endDate] = $this->getDateRangeFromPeriode((int)$annee, $periode, (int)$mois, (int)$tranche);
                $stats2 = $professionnelRepository->findDiplomeStats(new \DateTime($startDate), new \DateTime($endDate));
            } else {
                $stats2 = $professionnelRepository->findDiplomeStats(null, null);
            }
            //dd($stats2);
            //dd($startDate,$endDate,$annee);



            //dd($periode, $annee);
            $stats = $professionnelRepository->countProByProfession((int)$annee, $periode, (int)$mois, (int)$tranche);
            $dataTrancheAge = $professionnelRepository->countProByTrancheAge((int)$annee, $periode, (int)$mois, (int)$tranche);
            $dataGenre = $professionnelRepository->countProByCiviliteGeneral((int)$annee, $periode, (int)$mois, (int)$tranche);
            $dataAnnee = $professionnelRepository->countProByAnnee();
            //dd($dataAnnee,$stats,$dataTrancheAge,$dataGenre);
            //dd($dataAnnee);

            $dataVille = $professionnelRepository->countProByVille((int)$annee, $periode, (int)$mois, (int)$tranche);
            $dataRegion = $professionnelRepository->countProByRegion((int)$annee, $periode, (int)$mois, (int)$tranche);
            $dataPays = $professionnelRepository->countProByPays((int)$annee, $periode, (int)$mois, (int)$tranche);
            $isFirst = true; // Pour le premier élément sélectionné dans le Pie Chart

            //dd($stats,$dataTrancheAge,$dataGenre,$dataAnnee,$dataVille,$dataRegion,$dataPays,$stats2);

            // Préchargement des professions
            //$codes = array_column($stats, 'libelle');
            /*   $professions = $professionRepository->findBy(['code' => $codes]);
            $professionMap = [];
            foreach ($professions as $profession) {
                $professionMap[$profession->getCode()] = $profession->getLibelle();
            } */

            $statsProfession = [];
            $statsYear = [];
            foreach ($stats as $stat) {
                if ($stat['nombre'] > 0) {
                    $statsProfession[] = [
                        'name' => $stat['libelle'] ?? 'Inconnu',
                        'y' => (int) $stat['nombre'],
                        'sliced' => $isFirst,
                        'selected' => $isFirst
                    ];
                }
                $isFirst = false; // Désactiver la sélection après le premier élément

            }

            foreach ($dataAnnee as $key => $value) {

                $statsYear[] = [
                    'libelle' => $value['libelle'],
                    'id' => (int) $value['libelle'],

                ];
            }

            // Formattage générique
            $statsVille = $this->formatStats($dataVille, 'libelle', true);
            $statsPays = $this->formatStats($dataPays, 'libelle', true);
            $statsRegions = $this->formatStats($dataRegion, 'libelle', true);
            $statsGenre = $this->formatStats($dataGenre, 'civilite', true);
            $statsAnnee = $this->formatStats($dataAnnee, 'libelle', true);
            $statsTrancheAge = $this->formatStats($dataTrancheAge, 'tranche', true);


            $result = [
                'professions' => array_reverse($statsProfession),
                'villes' => array_reverse($statsVille),
                'annees' => array_reverse($statsAnnee),
                'pays' => array_reverse($statsPays),
                'regions' => array_reverse($statsRegions),
                'genres' => array_reverse($statsGenre),
                'tranches_age' => $statsTrancheAge,
                'all_annees' => $statsYear,
                'dates' => [
                    'debut' => $startDate,
                    'fin' => $endDate
                ],
                'statistiques' => $stats2
            ];

            return $this->json([
                'code' => 200,
                'message' => 'Operation effectuée avec succes',
                'data' => $result,
                'errors' => []
            ]);
        } catch (\Exception $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
        }
    }


    private function getDateRangeFromPeriode(?int $annee, ?string $periode, ?int $mois, ?int $tranche): array
    {
        // Valeurs par défaut
        $annee = $annee ?: (int) date('Y');
        $mois = $mois ?: (int) date('m');
        $tranche = (int) $tranche;

        switch ($periode) {
            case 'mois':
                $start = new \DateTime("$annee-$mois-01");
                $end = (clone $start)->modify('last day of this month');
                break;

            case 'trimestre':
                // Définition des trimestres
                $trimestres = [
                    1 => ['start' => '01-01', 'end' => '03-31'],
                    2 => ['start' => '04-01', 'end' => '06-30'],
                    3 => ['start' => '07-01', 'end' => '09-30'],
                    4 => ['start' => '10-01', 'end' => '12-31'],
                ];
                // Trimestre par défaut = 1
                $t = $trimestres[$tranche] ?? $trimestres[1];
                $start = new \DateTime("$annee-{$t['start']}");
                $end = new \DateTime("$annee-{$t['end']}");
                break;

            case 'semestre':
                // Définition des semestres
                $semestres = [
                    1 => ['start' => '01-01', 'end' => '06-30'],
                    2 => ['start' => '07-01', 'end' => '12-31'],
                ];
                // Semestre par défaut = 1
                $s = $semestres[$tranche] ?? $semestres[1];
                $start = new \DateTime("$annee-{$s['start']}");
                $end = new \DateTime("$annee-{$s['end']}");
                break;

            case 'annee':
            default:
                $start = new \DateTime("$annee-01-01");
                $end = new \DateTime("$annee-12-31");
                break;
        }


        return [$start->format('Y-m-d'), $end->format('Y-m-d')];
    }

    private function formatStats(array $data, string $labelKey = 'libelle', bool $markFirst = false): array
    {
        $result = [];
        $isFirst = true;

        foreach ($data as $item) {
            if ($item['nombre'] > 0) {
                $entry = [
                    'name' => $item[$labelKey] ?? 'Inconnu',
                    'y' => (int) $item['nombre'],
                ];

                if ($markFirst && $isFirst) {
                    $entry['sliced'] = true;
                    $entry['selected'] = true;
                    $isFirst = false;
                } else {
                    $entry['sliced'] = false;
                    $entry['selected'] = false;
                }

                $result[] = $entry;
            }
        }

        return $result;
    }

    /* private function formatStats(array $data, string $labelKey = 'libelle'): array
    {
        return array_values(array_filter(array_map(function ($item) use ($labelKey) {
            if ($item['nombre'] > 0) {
                return [
                    'name' => $item[$labelKey] ?? 'Inconnu',
                    'y' => (int) $item['nombre'],
                ];
            }
            return null;
        }, $data)));
    } */

    #[Route('/ville', methods: ['GET'])]
    /**
     * Retourne les stats du dashboard.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'statistiques')]
    // 
    public function indexGeolocalisation(EtablissementRepository $etablissementRepository, ProfessionnelRepository $professionnelRepository, CiviliteRepository $civiliteRepository): Response
    {
        try {
            $stats = $professionnelRepository->countProByVille();

            $formattedStats = [];
            $isFirst = true; // Pour le premier élément sélectionné dans le Pie Chart


            foreach ($stats as $index => $stat) {

                $nombre = $stat['nombre'];
                if ($nombre > 0) {

                    $formattedStats[] = [
                        'name' => $stat['libelle'],
                        'y' => (int)$stat['nombre'],
                        'sliced' => $isFirst,
                        'selected' => $isFirst
                    ];
                }


                $isFirst = false; // Désactiver la sélection après le premier élément
            }

            $formattedStats = array_reverse($formattedStats);


            $result = [
                'nombre' => $stats,
                'pieChart' => $formattedStats
            ];


            $response = $this->responseData($result, 'group_user', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }
    #[Route('/specialite/{genre}', methods: ['GET'])]
    /**
     * Retourne les stats du dashboard.
     * 
     */
    #[OA\Response(
        response: 200,
        description: 'Returns the rewards of an user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Transaction::class, groups: ['full']))
        )
    )]
    #[OA\Tag(name: 'statistiques')]
    // 
    public function indexSpecialite($genre, EtablissementRepository $etablissementRepository, ProfessionRepository $professionRepository, SpecialiteRepository $specialiteRepository): Response
    {
        try {
            $stats = $professionRepository->countSpecialiteProfByGenre($genre);


            $formattedStats = [];
            $isFirst = true; // Pour le premier élément sélectionné dans le Pie Chart

            foreach ($stats as $index => $stat) {
                $formattedStats[] = [
                    'name' => $stat['civilite'],
                    'y' => (int) $stat['nombre'],
                    'sliced' => $isFirst,
                    'selected' => $isFirst
                ];
                $isFirst = false; // Désactiver la sélection après le premier élément
            }

            $formattedStats = array_reverse($formattedStats);

            $result = [
                'nombre' => $stats,
                'pieChart' => $formattedStats
            ];


            $response = $this->responseData($result, 'group_user', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            $this->setMessage("");
            $response = $this->response('[]');
        }

        // On envoie la réponse
        return $response;
    }

    #[Route('/admin/general', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Statistiques générales pour l’administrateur (comptes, transactions, dossiers)',
        content: new OA\JsonContent(type: 'object')
    )]
    #[OA\Tag(name: 'statistiques')]
    public function indexAdminGeneral(
        Request $request,
        UserRepository $userRepository,
        TransactionRepository $transactionRepository,
        ProfessionnelRepository $professionnelRepository,
        EtablissementRepository $etablissementRepository
    ): Response {
        try {
            /** @var User $userConnected */
            $userConnected = $this->getUser();
            if (!$userConnected || $userConnected->getTypeUser() !== 'ADMINISTRATEUR') {
                return $this->setStatusCode(403)->setMessage("Cette ressource est réservée aux administrateurs.")->response('[]');
            }

            [$startDate, $endDate] = $this->resolveDateRangeFromQuery($request);

            // 1. Analyse des Utilisateurs
            $users = [
                'total' => $this->countEntitiesInRange(User::class, ['deleteAt' => null], $startDate, $endDate),
                'professionnels' => $this->countEntitiesInRange(User::class, ['typeUser' => 'PROFESSIONNEL', 'deleteAt' => null], $startDate, $endDate),
                'etablissements' => $this->countEntitiesInRange(User::class, ['typeUser' => 'ETABLISSEMENT', 'deleteAt' => null], $startDate, $endDate),
                'administrateurs' => $this->countEntitiesInRange(User::class, ['typeUser' => 'ADMINISTRATEUR', 'deleteAt' => null], $startDate, $endDate),
            ];

            // 2. Analyse des Transactions (Chiffre d'Affaires)
            $totalSuccessfulAmount = $this->sumTransactionFieldInRange('montant', $startDate, $endDate);
            $totalFee = $this->sumTransactionFieldInRange('fee', $startDate, $endDate);
            $transactions = [
                'montant_total' => $totalSuccessfulAmount,
                'succes' => $this->countEntitiesInRange(Transaction::class, ['state' => 1], $startDate, $endDate),
                'echec' => $this->countEntitiesInRange(Transaction::class, ['state' => 0], $startDate, $endDate),
                'fee_total' => $totalFee,
                'solde_retirable' => $totalSuccessfulAmount - $totalFee,
            ];

            // 3. Dossiers Professionnels
            $professionnels = [
                'total' => $this->countEntitiesInRange(Professionnel::class, [], $startDate, $endDate),
                'ajour' => $this->countEntitiesInRange(Professionnel::class, ['status' => 'a_jour'], $startDate, $endDate),
                'attente' => $this->countEntitiesInRange(Professionnel::class, ['status' => 'attente'], $startDate, $endDate),
                'rejete' => $this->countEntitiesInRange(Professionnel::class, ['status' => 'rejete'], $startDate, $endDate),
                'accepte' => $this->countEntitiesInRange(Professionnel::class, ['status' => 'accepte'], $startDate, $endDate),
            ];

            // 4. Dossiers Établissements
            $etablissements = [
                'total' => $this->countEntitiesInRange(Etablissement::class, [], $startDate, $endDate),
                'valides' => $this->countEntitiesInRange(Etablissement::class, ['status' => 'accepte'], $startDate, $endDate), // Adapté selon les statuts réels
                'ajour' => $this->countEntitiesInRange(Etablissement::class, ['status' => 'accepte'], $startDate, $endDate),
                'en_attente' => $this->countEntitiesInRange(Etablissement::class, ['status' => 'attente'], $startDate, $endDate),
                'rejete' => $this->countEntitiesInRange(Etablissement::class, ['status' => 'rejete'], $startDate, $endDate),
            ];

            $tab = [
                'utilisateurs' => $users,
                'transactions' => $transactions,
                'professionnels' => $professionnels,
                'etablissements' => $etablissements,
            ];

            return $this->json([
                'code'    => 200,
                'message' => 'Operation effectuée avec succes',
                'data'    => $tab,
                'errors'  => [],
            ]);
        } catch (\Exception $exception) {
            $this->setMessage($exception->getMessage());
            return $this->response('[]');
        }
    }

    /**
     * Résout la période (année, mois/trimestre/semestre) transmise en query params
     * en un intervalle de dates. Retourne [null, null] si aucune période n'est fournie.
     *
     * @return array{0: ?\DateTimeImmutable, 1: ?\DateTimeImmutable}
     */
    private function resolveDateRangeFromQuery(Request $request): array
    {
        $annee = $request->query->get('annee');
        $periode = $request->query->get('periode');
        $mois = $request->query->get('mois');
        $tranche = $request->query->get('tranche');

        if (!$annee || $annee === 'null' || !$periode || $periode === 'null') {
            return [null, null];
        }

        $annee = (int) $annee;
        $mois = $mois ? (int) $mois : (int) date('m');
        $tranche = (int) ($tranche ?: 1);

        switch ($periode) {
            case 'mois':
                $start = new \DateTimeImmutable(sprintf('%d-%02d-01 00:00:00', $annee, $mois));
                $end = $start->modify('last day of this month')->setTime(23, 59, 59);
                break;

            case 'trimestre':
                $trimestres = [1 => [1, 3], 2 => [4, 6], 3 => [7, 9], 4 => [10, 12]];
                [$m1, $m2] = $trimestres[$tranche] ?? $trimestres[1];
                $start = new \DateTimeImmutable(sprintf('%d-%02d-01 00:00:00', $annee, $m1));
                $end = (new \DateTimeImmutable(sprintf('%d-%02d-01', $annee, $m2)))->modify('last day of this month')->setTime(23, 59, 59);
                break;

            case 'semestre':
                $semestres = [1 => [1, 6], 2 => [7, 12]];
                [$m1, $m2] = $semestres[$tranche] ?? $semestres[1];
                $start = new \DateTimeImmutable(sprintf('%d-%02d-01 00:00:00', $annee, $m1));
                $end = (new \DateTimeImmutable(sprintf('%d-%02d-01', $annee, $m2)))->modify('last day of this month')->setTime(23, 59, 59);
                break;

            case 'annee':
            default:
                $start = new \DateTimeImmutable(sprintf('%d-01-01 00:00:00', $annee));
                $end = new \DateTimeImmutable(sprintf('%d-12-31 23:59:59', $annee));
                break;
        }

        return [$start, $end];
    }

    private function countEntitiesInRange(string $entityClass, array $criteria, ?\DateTimeImmutable $start, ?\DateTimeImmutable $end): int
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from($entityClass, 'e');

        foreach ($criteria as $field => $value) {
            if ($value === null) {
                $qb->andWhere("e.{$field} IS NULL");
            } else {
                $qb->andWhere("e.{$field} = :{$field}")->setParameter($field, $value);
            }
        }

        if ($start && $end) {
            $qb->andWhere('e.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function sumTransactionFieldInRange(string $field, ?\DateTimeImmutable $start, ?\DateTimeImmutable $end): int
    {
        $qb = $this->em->createQueryBuilder()
            ->select("COALESCE(SUM(t.{$field}), 0)")
            ->from(Transaction::class, 't')
            ->andWhere('t.state = :state')
            ->setParameter('state', 1);

        if ($start && $end) {
            $qb->andWhere('t.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    #[Route('/comptable/bilan', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Bilan complet et soft pour le comptable',
        content: new OA\JsonContent(type: 'object')
    )]
    #[OA\Tag(name: 'statistiques')]
    public function comptableBilan(Request $request, TransactionRepository $transactionRepository): Response
    {
        try {
            $startDate = $request->query->get('startDate');
            $endDate = $request->query->get('endDate');
            $professionId = $request->query->get('profession');
            $regionId = $request->query->get('region');

            if ($startDate === 'null' || $startDate === '') $startDate = null;
            if ($endDate === 'null' || $endDate === '') $endDate = null;
            if ($professionId === 'null' || $professionId === '') $professionId = null;
            if ($regionId === 'null' || $regionId === '') $regionId = null;

            $rawTransactions = $transactionRepository->getComptableBilanData($startDate, $endDate, $professionId, $regionId);

            $montantTotal = 0;
            $nombreSuccess = 0;
            $nombreFail = 0;

            $byChannelMap = [];
            $byTypeUserMap = [];
            $byProfessionMap = [];
            $byRegionMap = [];
            $byMonthMap = [];

            foreach ($rawTransactions as $t) {
                $state = (int)$t['state'];
                if ($state === 1) {
                    $montant = (int)$t['montant'];
                    $montantTotal += $montant;
                    $nombreSuccess++;

                    // Channel
                    $chan = $t['channel'] ?: 'Inconnu';
                    if (!isset($byChannelMap[$chan])) {
                        $byChannelMap[$chan] = ['name' => $chan, 'count' => 0, 'montant' => 0];
                    }
                    $byChannelMap[$chan]['count']++;
                    $byChannelMap[$chan]['montant'] += $montant;

                    // Type User
                    $typeUser = $t['typeUser'] ?: 'Inconnu';
                    $typeUserUpper = strtoupper($typeUser);
                    if (!isset($byTypeUserMap[$typeUserUpper])) {
                        $byTypeUserMap[$typeUserUpper] = ['name' => $typeUserUpper, 'count' => 0, 'montant' => 0];
                    }
                    $byTypeUserMap[$typeUserUpper]['count']++;
                    $byTypeUserMap[$typeUserUpper]['montant'] += $montant;

                    // Month
                    $dateStr = $t['createdAt'];
                    $monthYear = 'Inconnu';
                    if ($dateStr) {
                        try {
                            $dt = new \DateTime($dateStr);
                            $monthYear = $this->frenchMonth($dt->format('n')) . ' ' . $dt->format('Y');
                        } catch (\Exception $e) {}
                    }
                    if (!isset($byMonthMap[$monthYear])) {
                        $byMonthMap[$monthYear] = ['name' => $monthYear, 'count' => 0, 'montant' => 0];
                    }
                    $byMonthMap[$monthYear]['count']++;
                    $byMonthMap[$monthYear]['montant'] += $montant;

                    // Profession & Region only for PROFESSIONNEL
                    if ($typeUserUpper === 'PROFESSIONNEL') {
                        $prof = $t['professionLibelle'] ?: 'Sans profession';
                        if (!isset($byProfessionMap[$prof])) {
                            $byProfessionMap[$prof] = ['name' => $prof, 'count' => 0, 'montant' => 0];
                        }
                        $byProfessionMap[$prof]['count']++;
                        $byProfessionMap[$prof]['montant'] += $montant;

                        $reg = $t['regionLibelle'] ?: 'Sans région';
                        if (!isset($byRegionMap[$reg])) {
                            $byRegionMap[$reg] = ['name' => $reg, 'count' => 0, 'montant' => 0];
                        }
                        $byRegionMap[$reg]['count']++;
                        $byRegionMap[$reg]['montant'] += $montant;
                    }
                } elseif ($state === 0) {
                    $nombreFail++;
                }
            }

            $avgAmount = $nombreSuccess > 0 ? (int)($montantTotal / $nombreSuccess) : 0;

            // Sort maps by amount descending
            $sortFn = function($a, $b) {
                return $b['montant'] <=> $a['montant'];
            };
            usort($byChannelMap, $sortFn);
            usort($byTypeUserMap, $sortFn);
            usort($byProfessionMap, $sortFn);
            usort($byRegionMap, $sortFn);

            // Keep monthly order (we can sort by date, but let's just convert map values to array)
            $byMonth = array_values($byMonthMap);

            $result = [
                'totals' => [
                    'montantTotal' => $montantTotal,
                    'nombreSuccess' => $nombreSuccess,
                    'nombreFail' => $nombreFail,
                    'avgAmount' => $avgAmount
                ],
                'byChannel' => array_values($byChannelMap),
                'byTypeUser' => array_values($byTypeUserMap),
                'byProfession' => array_values($byProfessionMap),
                'byRegion' => array_values($byRegionMap),
                'byMonth' => $byMonth
            ];

            return $this->json([
                'code' => 200,
                'message' => 'Bilan comptable généré avec succès',
                'data' => $result,
                'errors' => []
            ]);

        } catch (\Exception $exception) {
            return $this->json([
                'code' => 500,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ], 500);
        }
    }

    private function frenchMonth(int $m): string
    {
        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        return $months[$m] ?? '';
    }
}
