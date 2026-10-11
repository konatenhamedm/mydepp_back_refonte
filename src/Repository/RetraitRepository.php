<?php

namespace App\Repository;

use App\Entity\Retrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Retrait>
 */
class RetraitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Retrait::class);
    }

    public function add(Retrait $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * QueryBuilder (non exécuté) trié du plus récent au plus ancien, pour que
     * PaginationService/KnpPaginator pagine directement en SQL (LIMIT/OFFSET).
     */
    public function findAllOrderedQueryBuilder(?string $statut = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r')
            ->orderBy('r.id', 'DESC');

        if ($statut) {
            $qb->andWhere('r.statut = :statut')
                ->setParameter('statut', $statut);
        }

        return $qb;
    }

    /**
     * Somme des retraits VALIDÉS (argent effectivement décaissé), à déduire du
     * chiffre d'affaires / solde disponible. Filtre optionnel sur la date de
     * traitement (traiteAt), pour rester cohérent avec les plages de dates
     * utilisées ailleurs dans les statistiques comptables.
     */
    public function montantTotalValide(?\DateTimeInterface $start = null, ?\DateTimeInterface $end = null): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COALESCE(SUM(r.montant), 0)')
            ->andWhere('r.statut = :statut')
            ->setParameter('statut', 'valide');

        if ($start && $end) {
            $qb->andWhere('r.traiteAt BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
