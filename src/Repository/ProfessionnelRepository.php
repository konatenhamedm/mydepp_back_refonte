<?php

namespace App\Repository;

use App\Entity\Civilite;
use App\Entity\Pays;
use App\Entity\Professionnel;
use App\Entity\Region;
use App\Entity\Ville;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Professionnel>
 */
class ProfessionnelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Professionnel::class);
    }

    public function add(Professionnel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Professionnel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
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

        return [$start, $end];
    }



    public function getProfessionnelByetat($status)
    {

        return $this->createQueryBuilder('p')

            ->andWhere('p.status = :val')
            ->setParameter('val', $status)
            ->getQuery()
            ->getResult();
    }
    public function allProfAjour()
    {

        return $this->createQueryBuilder('p')

            ->andWhere('p.status = :val')
            ->setParameter('val', 'ACCEPT')
            ->getQuery()
            ->getResult();
    }
    public function countProByCivilite()
    {

        return $this->getEntityManager()->createQueryBuilder()
            ->select('c.libelle AS civilite, COUNT(e.id) AS nombre')
            ->from(Civilite::class, 'c')
            ->leftJoin(Professionnel::class, 'e', 'WITH', 'e.civilite = c.id')
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }
    public function countProByCiviliteGeneral(?int $annee = null, ?string $periode = null, ?int $mois = null, ?int $tranche = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c.libelle AS civilite, COUNT(e.id) AS nombre')
            ->from(Civilite::class, 'c')
            ->leftJoin(Professionnel::class, 'e', 'WITH', 'e.civilite = c.id')
            ->groupBy('c.id');

        if ($annee != "null" && $periode != "null") {
            [$start, $end] = $this->getDateRangeFromPeriode($annee, $periode,$mois,$tranche);
            $qb->andWhere("DATE_FORMAT(e.createdAt, '%Y-%m-%d') BETWEEN :start AND :end")
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        return $qb->getQuery()->getResult();
    }
    public function countProByProfession(?int $annee = null, ?string $periode = null, ?int $mois = null, ?int $tranche = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.profession AS libelle, COUNT(p.id) AS nombre')
            ->groupBy('libelle');

        if ($annee != "null" && $periode != "null") {
            [$start, $end] = $this->getDateRangeFromPeriode($annee, $periode,$mois,$tranche);
            $qb->where("DATE_FORMAT(p.createdAt, '%Y-%m-%d') BETWEEN :start AND :end")
                ->setParameter('start', $start->format('Y-m-d'))
                ->setParameter('end', $end->format('Y-m-d'));
        }

        return $qb->getQuery()->getResult();
    }
    public function countProByAnnee()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('year(c.createdAt) AS libelle, COUNT(c.id) AS nombre')
            ->from(Professionnel::class, 'c')
            ->groupBy('libelle')
            ->orderBy('libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countProByVille(?int $annee = null, ?string $periode = null, ?int $mois = null, ?int $tranche = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c.libelle AS libelle, COUNT(e.id) AS nombre')
            ->from(Ville::class, 'c')
            ->leftJoin(Professionnel::class, 'e', 'WITH', 'e.ville = c.id')
            ->groupBy('c.id');

        if ($annee != "null" && $periode != "null") {
            [$start, $end] = $this->getDateRangeFromPeriode($annee, $periode,$mois,$tranche);
            $qb->andWhere("DATE_FORMAT(e.createdAt, '%Y-%m-%d') BETWEEN :start AND :end")
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        return $qb->getQuery()->getResult();
    }

    public function countProByRegion(?int $annee = null, ?string $periode = null, ?int $mois = null, ?int $tranche = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c.libelle AS libelle, COUNT(e.id) AS nombre')
            ->from(Region::class, 'c')
            ->leftJoin(Professionnel::class, 'e', 'WITH', 'e.region = c.id')
            ->groupBy('c.id');

        if ($annee != "null" && $periode != "null") {
            [$start, $end] = $this->getDateRangeFromPeriode($annee, $periode,$mois,$tranche);
            $qb->andWhere("DATE_FORMAT(e.createdAt, '%Y-%m-%d') BETWEEN :start AND :end")
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        return $qb->getQuery()->getResult();
    }

    public function countProByPays(?int $annee = null, ?string $periode = null, ?int $mois = null, ?int $tranche = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c.libelle AS libelle, COUNT(e.id) AS nombre')
            ->from(Pays::class, 'c')
            ->leftJoin(Professionnel::class, 'e', 'WITH', 'e.nationate = c.id')
            ->groupBy('c.id');

        if ($annee != "null" && $periode != "null") {
            [$start, $end] = $this->getDateRangeFromPeriode($annee, $periode,$mois,$tranche);
            $qb->andWhere("DATE_FORMAT(e.createdAt, '%Y-%m-%d') BETWEEN :start AND :end")
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        return $qb->getQuery()->getResult();
    }


    public function countProByTrancheAge(?int $annee = null, ?string $periode = null, ?int $mois = null, ?int $tranche = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select(
                "CASE 
                    WHEN TIMESTAMPDIFF(YEAR, p.dateNaissance, CURRENT_DATE()) < 25 THEN '< 25 ans'
                    WHEN TIMESTAMPDIFF(YEAR, p.dateNaissance, CURRENT_DATE()) BETWEEN 25 AND 34 THEN '25–34 ans'
                    WHEN TIMESTAMPDIFF(YEAR, p.dateNaissance, CURRENT_DATE()) BETWEEN 35 AND 44 THEN '35–44 ans'
                    WHEN TIMESTAMPDIFF(YEAR, p.dateNaissance, CURRENT_DATE()) BETWEEN 45 AND 54 THEN '45–54 ans'
                    ELSE '55 ans et plus'
                 END AS tranche",
                'COUNT(p.id) as nombre'
            )
            ->groupBy('tranche');

        if ($annee != "null" && $periode != "null") {
            [$start, $end] = $this->getDateRangeFromPeriode($annee, $periode,$mois,$tranche);
            $qb->andWhere("DATE_FORMAT(p.createdAt, '%Y-%m-%d') BETWEEN :start AND :end")
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        return $qb->getQuery()->getResult();
    }


   
    public function findDiplomeStats(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select([
                'IDENTITY(p.lieuObtentionDiplome) as lieu_id',
                'l.libelle as lieu_nom',
                'IDENTITY(p.civilite) as civilite_id',
                'c.libelle as civilite_libelle',
                'p.dateNaissance',
                'p.id'
            ])
            ->leftJoin('p.lieuObtentionDiplome', 'l')
            ->leftJoin('p.civilite', 'c')
            ->where('p.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        $results = $qb->getQuery()->getArrayResult();

        // Grouper et calculer les tranches d'âge en PHP
        $grouped = [];
        foreach ($results as $row) {
            $age = (new \DateTime())->diff(new \DateTime($row['dateNaissance']))->y;

            if ($age < 25) {
                $trancheAge = '< 25 ans';
            } elseif ($age >= 25 && $age <= 34) {
                $trancheAge = '25-34 ans';
            } elseif ($age >= 35 && $age <= 44) {
                $trancheAge = '35-44 ans';
            } elseif ($age >= 45 && $age <= 54) {
                $trancheAge = '45-54 ans';
            } else {
                $trancheAge = '55 ans et plus';
            }

            $key = $row['lieu_id'] . '|' . $row['civilite_id'] . '|' . $trancheAge;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'lieu_id' => $row['lieu_id'],
                    'lieu_nom' => $row['lieu_nom'],
                    'civilite_id' => $row['civilite_id'],
                    'civilite_libelle' => $row['civilite_libelle'],
                    'tranche_age' => $trancheAge,
                    'count' => 0
                ];
            }

            $grouped[$key]['count']++;
        }

        return $this->formatStats(array_values($grouped));
    }

    private function formatStats(array $results): array
    {
        $formatted = [
            'tableau_croise' => [],
            'total' => 0,
            'par_lieu' => [],
            'par_tranche_age' => [],
            'par_civilite' => []
        ];

        foreach ($results as $row) {
            $formatted['tableau_croise'][] = [
                'lieu' => [
                    'id' => $row['lieu_id'],
                    'nom' => $row['lieu_nom']
                ],
                'civilite' => [
                    'id' => $row['civilite_id'],
                    'libelle' => $row['civilite_libelle']
                ],
                'tranche_age' => $row['tranche_age'],
                'count' => $row['count']
            ];

            // Aggrégations
            $formatted['par_lieu'][$row['lieu_id']] = [
                'nom' => $row['lieu_nom'],
                'count' => ($formatted['par_lieu'][$row['lieu_id']]['count'] ?? 0) + $row['count']
            ];

            $formatted['par_civilite'][$row['civilite_id']] = [
                'libelle' => $row['civilite_libelle'],
                'count' => ($formatted['par_civilite'][$row['civilite_id']]['count'] ?? 0) + $row['count']
            ];

            $formatted['par_tranche_age'][$row['tranche_age']] =
                ($formatted['par_tranche_age'][$row['tranche_age']] ?? 0) + $row['count'];

            $formatted['total'] += $row['count'];
        }

        return $formatted;
    }

    //    /**
    //     * @return Professionnel[] Returns an array of Professionnel objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Professionnel
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
