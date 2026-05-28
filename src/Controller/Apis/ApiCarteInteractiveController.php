<?php

namespace App\Controller\Apis;

use App\Controller\Apis\Config\ApiInterface;
use App\Entity\Region;
use App\Repository\RegionRepository;
use App\Repository\ProfessionnelRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

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
            // Get all regions
            $regions = $regionRepository->findAll();
            $stats = [];

            foreach ($regions as $region) {
                // Count professionnels for this region. We can do a simple count query
                $qb = $professionnelRepository->createQueryBuilder('p');
                $qb->select('count(p.id)')
                   ->where('p.region = :region')
                   ->setParameter('region', $region);
                
                // You may want to filter active/validated ones only, e.g.
                // ->andWhere('p.status IN (:statuses)')
                // ->setParameter('statuses', ['valide', 'a_jour'])

                $count = $qb->getQuery()->getSingleScalarResult();

                // Get details of professionnels (limit to avoid huge payloads if many)
                $qbDetails = $professionnelRepository->createQueryBuilder('p');
                $qbDetails->select('p.id', 'p.nom', 'p.prenoms', 'p.code', 'p.number', 'p.email', 'prof.libelle as profession')
                    ->leftJoin('p.profession', 'prof')
                    ->where('p.region = :region')
                    ->setParameter('region', $region);

                $professionnels = $qbDetails->getQuery()->getResult();

                $stats[] = [
                    'id' => $region->getId(),
                    'code' => $region->getCode(),
                    'libelle' => $region->getLibelle(),
                    'count' => (int) $count,
                    'professionnels' => $professionnels
                ];
            }

            return $this->json([
                'status' => 'success',
                'data' => $stats
            ]);

        } catch (\Exception $exception) {
            return $this->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des données',
                'error' => $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
