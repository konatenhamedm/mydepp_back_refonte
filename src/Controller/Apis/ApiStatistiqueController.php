<?php


namespace App\Controller\Apis;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

use Symfony\Component\HttpFoundation\Request;
use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Etablissement;
use App\Entity\Transaction;
use App\Repository\CiviliteRepository;
use App\Repository\EtablissementRepository;
use App\Repository\ProfessionnelRepository;
use App\Repository\ProfessionRepository;
use App\Repository\SpecialiteRepository;
use App\Repository\TransactionRepository;

#[Route('/api/statistique')]
class ApiStatistiqueController extends ApiInterface
{

    #[Route('/web-site-statistique', methods: ['GET'])]
    #[OA\Tag(name: 'statistiques')]
    public function webSiteStatistique(EtablissementRepository $etablissementRepository, ProfessionnelRepository $professionnelRepository)
    {
        try {
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
                    "total"=>count($etablissementRepository->findAll()),
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
                $dataAccepte = $professionnelRepository->findBy(['status' => 'accepte', 'imputation' => $idUser]);
                $dataAttente = $professionnelRepository->findBy(['status' => 'attente', 'imputation' => $idUser]);
                $dataRejet = $professionnelRepository->findBy(['status' => 'rejete', 'imputation' => $idUser]);
                $dataRefuse = $professionnelRepository->findBy(['status' => 'refuse', 'imputation' => $idUser]);
                $dataValide = $professionnelRepository->findBy(['status' => 'valide', 'imputation' => $idUser]);

                $tab = [
                    'atttente' => $dataAttente ?  count($dataAttente) : 0,
                    'accepte' => $dataAccepte ?  count($dataAccepte) : 0,
                    'rejete' => $dataRejet ?  count($dataRejet) : 0,
                    'valide' => $dataValide ?  count($dataValide) : 0,
                    'refuse' => $dataRefuse ?  count($dataRefuse) : 0,
                ];
            } elseif ($type == "SOUS-DIRECTEUR") {
                $tab = [
                    'atttente' => count($professionnelRepository->findBy(['status' => 'attente'])),
                    'accepte' => count($professionnelRepository->findBy(['status' => 'accepte'])),
                    'rejete' => count($professionnelRepository->findBy(['status' => 'rejete'])),
                    'valide' => count($professionnelRepository->findBy(['status' => 'valide'])),
                    'refuse' => count($professionnelRepository->findBy(['status' => 'refuse']))
                ];
            } elseif ($type == "COMPTABLE") {

                //dd($transactionRepository->montantTotal());
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
                    'professionnelAjour' => count($professionnelRepository->allProfAjour())
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
            // dd($mois,$periode,$annee,$tranche);
            // Calcul de la plage de dates
            [$startDate, $endDate] = $this->getDateRangeFromPeriode((int)$annee, $periode, (int)$mois, (int)$tranche);

            //dd($startDate,$endDate);
            // dd($startDate,$endDate,$annee,$mois,$tranche);

            // Requête optimisée sans filtres supplémentaires
            $stats2 = $professionnelRepository->findDiplomeStats(new \DateTime($startDate), new \DateTime($endDate));
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

            return $this->responseData($result, 'group_user', ['Content-Type' => 'application/json']);
        } catch (\Exception $exception) {
            return $this->response($exception->getMessage());
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
}
