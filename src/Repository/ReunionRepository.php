<?php

namespace App\Repository;

use App\Entity\Reunion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reunion>
 */
class ReunionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reunion::class);
    }

    public function add(Reunion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Reunion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Recherche avancée des réunions avec filtres optionnels et filtre par utilisateur
     */
    public function findByAdvancedFilter(?string $startDate, ?string $endDate, ?string $type, ?\App\Entity\User $user = null)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->andWhere('r.deletedAt IS NULL');

        if ($startDate) {
            $qb->andWhere('r.jour >= :startDate')
               ->setParameter('startDate', $startDate . ' 00:00:00');
        }

        if ($endDate) {
            $qb->andWhere('r.jour <= :endDate')
               ->setParameter('endDate', $endDate . ' 23:59:59');
        }

        if ($type) {
            $qb->andWhere('r.type = :type')
               ->setParameter('type', $type);
        }

        if ($user) {
            $qb->andWhere('r.createdBy = :user')
               ->setParameter('user', $user);
        }

        $qb->orderBy('r.id', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
