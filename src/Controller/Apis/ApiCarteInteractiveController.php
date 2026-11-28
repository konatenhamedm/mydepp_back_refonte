<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Etablissement;
use App\Repository\EtablissementRepository;
use App\Repository\ProfessionnelRepository;
use App\Repository\RegionRepository;
use App\Service\PlaqueGeolocalisation;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/carte-interactive')]
class ApiCarteInteractiveController extends ApiInterface
{
    #[Route('/stats-regions', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne les statistiques des professionnels par région'
    )]
    #[OA\Tag(name: 'carte-interactive')]
    public function getStatsRegions(
        RegionRepository $regionRepository,
        ProfessionnelRepository $professionnelRepository
    ): Response {
        try {
            $regions = $regionRepository->findAll();
            $stats = [];

            foreach ($regions as $region) {
                $qb = $professionnelRepository->createQueryBuilder('p');
                $qb->select('count(p.id)')
                    ->where('p.region = :region')
                    ->setParameter('region', $region);

                $count = $qb->getQuery()->getSingleScalarResult();

                $qbDetails = $professionnelRepository->createQueryBuilder('p');
                $qbDetails->select('p.id', 'p.nom', 'p.prenoms', 'p.code', 'p.number', 'p.email', 'prof.libelle as profession', 'nat.libelle as nationalite', 'v.libelle as ville', 'd.libelle as district')
                    ->leftJoin('p.profession', 'prof')
                    ->leftJoin('p.nationate', 'nat')
                    ->leftJoin('p.ville', 'v')
                    ->leftJoin('p.district', 'd')
                    ->where('p.region = :region')
                    ->setParameter('region', $region);

                $professionnels = $qbDetails->getQuery()->getResult();

                $stats[] = [
                    'id' => $region->getId(),
                    'code' => $region->getCode(),
                    'libelle' => $region->getLibelle(),
                    'count' => (int) $count,
                    'professionnels' => $professionnels,
                ];
            }

            return $this->json([
                'status' => 'success',
                'data' => $stats,
            ]);
        } catch (\Exception $exception) {
            return $this->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des données',
                'error' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retourne les établissements sanitaires privés (ESPR) autorisés avec leurs coordonnées GPS (WGS84),
     * leur région, leur district, leur adresse et leur état de validité pour affichage sur la carte interactive.
     */
    #[Route('/etablissements', methods: ['GET'])]
    #[OA\Parameter(name: 'region', in: 'query', description: 'ID de la région sanitaire', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'district', in: 'query', description: 'ID du district sanitaire', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'nature', in: 'query', description: 'ID de la nature d’établissement', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'niveau', in: 'query', description: 'ID du niveau d’intervention', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'statutJuridique', in: 'query', description: 'ID du statut juridique', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'etatAutorisation', in: 'query', description: 'valide | expire | tous', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'q', in: 'query', description: 'Recherche libre (dénomination, dossier, adresse, ville…)', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Liste géolocalisée des établissements autorisés et indicateurs agrégés')]
    #[OA\Tag(name: 'carte-interactive')]
    public function getEtablissementsCarte(
        Request $request,
        EtablissementRepository $etablissementRepository
    ): Response {
        try {
            $qb = $etablissementRepository->createQueryBuilder('e')
                ->leftJoin('e.region', 'r')->addSelect('r')
                ->leftJoin('e.district', 'd')->addSelect('d')
                ->leftJoin('e.natureEtablissement', 'nat')->addSelect('nat')
                ->leftJoin('e.niveauIntervention', 'niv')->addSelect('niv')
                ->leftJoin('e.statutJuridique', 'sj')->addSelect('sj')
                ->leftJoin('e.typePersonne', 'tp')->addSelect('tp');

            if (!$request->query->getBoolean('includeAll', false)) {
                $qb->andWhere('e.status IN (:statuses)')
                    ->setParameter('statuses', ['oep_dossier_conforme', 'renouvellement']);
            }

            if ($regionId = $request->query->get('region')) {
                $qb->andWhere('r.id = :regionId')->setParameter('regionId', (int) $regionId);
            }
            if ($districtId = $request->query->get('district')) {
                $qb->andWhere('d.id = :districtId')->setParameter('districtId', (int) $districtId);
            }
            if ($natureId = $request->query->get('nature')) {
                $qb->andWhere('nat.id = :natureId')->setParameter('natureId', (int) $natureId);
            }
            if ($niveauId = $request->query->get('niveau')) {
                $qb->andWhere('niv.id = :niveauId')->setParameter('niveauId', (int) $niveauId);
            }
            if ($statutId = $request->query->get('statutJuridique')) {
                $qb->andWhere('sj.id = :statutId')->setParameter('statutId', (int) $statutId);
            }

            $search = trim((string) $request->query->get('q', ''));
            if ($search !== '') {
                $qb->andWhere(
                    'LOWER(e.denomination) LIKE :q OR LOWER(e.adresse) LIKE :q OR LOWER(e.commune) LIKE :q OR LOWER(e.villeVillage) LIKE :q OR LOWER(e.numeroDossier) LIKE :q OR LOWER(e.immatriculation) LIKE :q'
                )->setParameter('q', '%' . mb_strtolower($search) . '%');
            }

            $qb->orderBy('e.denomination', 'ASC');

            /** @var list<Etablissement> $etablissements */
            $etablissements = $qb->getQuery()->getResult();

            $now = new \DateTimeImmutable('today');
            $etatFilter = strtolower(trim((string) $request->query->get('etatAutorisation', 'tous')));

            $items = [];
            $actifs = 0;
            $expires = 0;
            $geolocalises = 0;
            $parNature = [];
            $parRegion = [];

            foreach ($etablissements as $etab) {
                $dateExp = $etab->getDateExpirationArrete() ?? $etab->getDateValiditeAccord();
                $estExpire = ($dateExp !== null && $dateExp < $now) || $etab->getStatus() === 'renouvellement';

                if ($etatFilter === 'valide' && $estExpire) {
                    continue;
                }
                if ($etatFilter === 'expire' && !$estExpire) {
                    continue;
                }

                $position = $this->resolvePosition($etab);
                if ($position !== null) {
                    $geolocalises++;
                }
                if ($estExpire) {
                    $expires++;
                } else {
                    $actifs++;
                }

                $natLib = $etab->getNatureEtablissement()?->getLibelle() ?? 'Non renseignée';
                $parNature[$natLib] = ($parNature[$natLib] ?? 0) + 1;

                $regLib = $etab->getRegion()?->getLibelle() ?? 'Non renseignée';
                $parRegion[$regLib] = ($parRegion[$regLib] ?? 0) + 1;

                $items[] = $this->formatEtablissementMarker($etab, $position, $estExpire);
            }

            arsort($parNature);
            arsort($parRegion);

            return $this->json([
                'status' => 'success',
                'meta' => [
                    'total' => \count($items),
                    'autorisationsValides' => $actifs,
                    'autorisationsExpirees' => $expires,
                    'geolocalises' => $geolocalises,
                    'parNature' => array_map(static fn ($k, $v) => ['libelle' => $k, 'count' => $v], array_keys($parNature), $parNature),
                    'parRegion' => array_map(static fn ($k, $v) => ['libelle' => $k, 'count' => $v], array_keys($parRegion), $parRegion),
                ],
                'data' => $items,
            ]);
        } catch (\Exception $exception) {
            return $this->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des établissements pour la carte',
                'error' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retourne la répartition par région sanitaire des établissements autorisés (même structure que /stats-regions).
     */
    #[Route('/stats-regions-etablissements', methods: ['GET'])]
    #[OA\Response(response: 200, description: 'Retourne les statistiques et la liste des établissements autorisés par région sanitaire')]
    #[OA\Tag(name: 'carte-interactive')]
    public function getStatsRegionsEtablissements(
        RegionRepository $regionRepository,
        EtablissementRepository $etablissementRepository
    ): Response {
        try {
            $regions = $regionRepository->findAll();
            $etablissements = $etablissementRepository->createQueryBuilder('e')
                ->leftJoin('e.region', 'r')->addSelect('r')
                ->leftJoin('e.district', 'd')->addSelect('d')
                ->leftJoin('e.natureEtablissement', 'nat')->addSelect('nat')
                ->leftJoin('e.niveauIntervention', 'niv')->addSelect('niv')
                ->leftJoin('e.statutJuridique', 'sj')->addSelect('sj')
                ->where('e.status IN (:statuses)')
                ->setParameter('statuses', ['oep_dossier_conforme', 'renouvellement'])
                ->orderBy('e.denomination', 'ASC')
                ->getQuery()
                ->getResult();

            $byRegionId = [];
            $now = new \DateTimeImmutable('today');

            foreach ($etablissements as $etab) {
                $regId = $etab->getRegion()?->getId();
                if ($regId === null) {
                    continue;
                }
                $dateExp = $etab->getDateExpirationArrete() ?? $etab->getDateValiditeAccord();
                $estExpire = ($dateExp !== null && $dateExp < $now) || $etab->getStatus() === 'renouvellement';
                $position = $this->resolvePosition($etab);

                $byRegionId[$regId]['items'][] = $this->formatEtablissementMarker($etab, $position, $estExpire);
                $byRegionId[$regId]['actifs'] = ($byRegionId[$regId]['actifs'] ?? 0) + ($estExpire ? 0 : 1);
                $byRegionId[$regId]['expires'] = ($byRegionId[$regId]['expires'] ?? 0) + ($estExpire ? 1 : 0);
            }

            $stats = [];
            foreach ($regions as $region) {
                $rid = $region->getId();
                $list = $byRegionId[$rid]['items'] ?? [];
                $stats[] = [
                    'id' => $rid,
                    'code' => $region->getCode(),
                    'libelle' => $region->getLibelle(),
                    'count' => \count($list),
                    'autorisationsValides' => $byRegionId[$rid]['actifs'] ?? 0,
                    'autorisationsExpirees' => $byRegionId[$rid]['expires'] ?? 0,
                    'etablissements' => $list,
                ];
            }

            return $this->json([
                'status' => 'success',
                'data' => $stats,
            ]);
        } catch (\Exception $exception) {
            return $this->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des statistiques régionales des établissements',
                'error' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Permet à un administrateur d'affiner manuellement la position GPS d'un établissement sur la carte
     * (ou de réinitialiser à l'estimation automatique en passant latitude=null, longitude=null).
     */
    #[Route('/etablissements/{id}/position', methods: ['PUT', 'POST'], requirements: ['id' => '\d+'])]
    #[OA\Tag(name: 'carte-interactive')]
    public function updatePositionEtablissement(
        int $id,
        Request $request,
        EtablissementRepository $etablissementRepository
    ): Response {
        $user = $this->getUser();
        if (!$user || ($user->getTypeUser() !== 'ADMINISTRATEUR' && !\in_array('ROLE_ADMIN', $user->getRoles(), true) && !\in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true))) {
            return $this->json(['status' => 'error', 'message' => 'Action réservée à l’administrateur.'], Response::HTTP_FORBIDDEN);
        }

        $etab = $etablissementRepository->find($id);
        if (!$etab) {
            return $this->json(['status' => 'error', 'message' => 'Établissement introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true) ?: [];
        $lat = $body['latitude'] ?? null;
        $lng = $body['longitude'] ?? null;

        if ($lat === null && $lng === null) {
            $geo = PlaqueGeolocalisation::estimerAvecContexte(
                $etab->getAdresse(),
                $etab->getImmatriculation(),
                $etab->getDistrict()?->getLibelle(),
                $etab->getRegion()?->getLibelle(),
                ($etab->getNumeroDossier() ?? '') . '|' . ($etab->getDenomination() ?? '')
            );
            $etab->setLatitude($geo !== null ? (string) $geo['latitude'] : null);
            $etab->setLongitude($geo !== null ? (string) $geo['longitude'] : null);
            $etab->setLieuGeolocalisation($geo['lieu'] ?? null);
            $etab->setPrecisionGeolocalisation($geo['precision'] ?? null);
        } elseif (!is_numeric($lat) || !is_numeric($lng)) {
            return $this->json(['status' => 'error', 'message' => 'Latitude et longitude numériques attendues.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            $latFloat = (float) $lat;
            $lngFloat = (float) $lng;
            if ($latFloat < 3.5 || $latFloat > 11.5 || $lngFloat < -9.5 || $lngFloat > -1.5) {
                return $this->json(['status' => 'error', 'message' => 'Position hors de la Côte d’Ivoire.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $etab->setLatitude((string) round($latFloat, 6));
            $etab->setLongitude((string) round($lngFloat, 6));
            $etab->setPrecisionGeolocalisation('manuelle');
        }

        $etab->setUpdatedAt();
        $etablissementRepository->add($etab, true);

        $dateExp = $etab->getDateExpirationArrete() ?? $etab->getDateValiditeAccord();
        $estExpire = ($dateExp !== null && $dateExp < new \DateTimeImmutable('today')) || $etab->getStatus() === 'renouvellement';

        return $this->json([
            'status' => 'success',
            'message' => $etab->getPrecisionGeolocalisation() === 'manuelle' ? 'Position enregistrée.' : 'Position estimée rétablie.',
            'data' => $this->formatEtablissementMarker($etab, $this->resolvePosition($etab), $estExpire),
        ]);
    }

    /**
     * @return array{latitude: float, longitude: float, lieu: ?string, precision: string}|null
     */
    private function resolvePosition(Etablissement $etab): ?array
    {
        if ($etab->getLatitude() !== null && $etab->getLongitude() !== null && is_numeric($etab->getLatitude()) && is_numeric($etab->getLongitude())) {
            return [
                'latitude' => (float) $etab->getLatitude(),
                'longitude' => (float) $etab->getLongitude(),
                'lieu' => $etab->getLieuGeolocalisation() ?? $etab->getCommune() ?? $etab->getVilleVillage(),
                'precision' => $etab->getPrecisionGeolocalisation() ?? 'ville',
            ];
        }

        return PlaqueGeolocalisation::estimerAvecContexte(
            $etab->getAdresse(),
            $etab->getImmatriculation(),
            $etab->getDistrict()?->getLibelle(),
            $etab->getRegion()?->getLibelle(),
            ($etab->getNumeroDossier() ?? '') . '|' . ($etab->getDenomination() ?? '')
        );
    }

    /**
     * @param array{latitude: float, longitude: float, lieu: ?string, precision: string}|null $position
     * @return array<string, mixed>
     */
    private function formatEtablissementMarker(Etablissement $etab, ?array $position, bool $estExpire): array
    {
        $dateSig = $etab->getDateSignatureArrete() ?? $etab->getDateValidation();
        $dateExp = $etab->getDateExpirationArrete() ?? $etab->getDateValiditeAccord();

        return [
            'id' => $etab->getId(),
            'code' => $etab->getCode(),
            'denomination' => $etab->getDenomination(),
            'status' => $etab->getStatus(),
            'estExpire' => $estExpire,
            'nature' => $etab->getNatureEtablissement() ? [
                'id' => $etab->getNatureEtablissement()->getId(),
                'libelle' => $etab->getNatureEtablissement()->getLibelle(),
            ] : null,
            'niveauIntervention' => $etab->getNiveauIntervention() ? [
                'id' => $etab->getNiveauIntervention()->getId(),
                'libelle' => $etab->getNiveauIntervention()->getLibelle(),
            ] : null,
            'statutJuridique' => $etab->getStatutJuridique() ? [
                'id' => $etab->getStatutJuridique()->getId(),
                'libelle' => $etab->getStatutJuridique()->getLibelle(),
            ] : null,
            'region' => $etab->getRegion() ? [
                'id' => $etab->getRegion()->getId(),
                'code' => $etab->getRegion()->getCode(),
                'libelle' => $etab->getRegion()->getLibelle(),
            ] : null,
            'district' => $etab->getDistrict() ? [
                'id' => $etab->getDistrict()->getId(),
                'libelle' => $etab->getDistrict()->getLibelle(),
            ] : null,
            'villeVillage' => $etab->getVilleVillage(),
            'commune' => $etab->getCommune(),
            'quartier' => $etab->getQuartier(),
            'adresse' => $etab->getAdresse(),
            'telephone' => $etab->getTelephone(),
            'telephoneAutre' => $etab->getTelephoneAutre(),
            'numeroDossier' => $etab->getNumeroDossier() ?? $etab->getNumeroEnregistrement(),
            'arreteAutorisation' => $etab->getArreteAutorisation(),
            'numeroArrete' => $etab->getNumeroArrete(),
            'dateSignatureArrete' => $dateSig?->format('Y-m-d'),
            'dateExpirationArrete' => $dateExp?->format('Y-m-d'),
            'immatriculation' => $etab->getImmatriculation(),
            'promoteurProprietaire' => $etab->getPromoteurProprietaire(),
            'nomRepresentant' => $etab->getNomRepresentant(),
            'responsableNom' => $etab->getResponsableNom(),
            'responsableProfession' => $etab->getResponsableProfession(),
            'responsableNumeroOrdre' => $etab->getResponsableNumeroOrdre(),
            'responsableSexe' => $etab->getResponsableSexe(),
            'position' => $position,
        ];
    }
}
