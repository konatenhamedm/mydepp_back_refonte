<?php

namespace App\Repository;

use App\Entity\LibelleGroupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LibelleGroupe>
 */
class LibelleGroupeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LibelleGroupe::class);
    }

    public function add(LibelleGroupe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LibelleGroupe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllByLibelleGroupe($id): array
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.typeDocuments', 't')
            ->innerJoin('t.typePersonne', 'p')
            ->andWhere('p.id = :val')
            ->andWhere('l.type = :type')
            ->setParameter('val', $id)
            ->setParameter('type', 'ACP')
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findAllByLibelleGroupeOep($id): array
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.typeDocuments', 't')
            ->innerJoin('t.typePersonne', 'p')
            ->andWhere('l.type = :type')
            ->andWhere('p.id = :val')
            ->setParameter('val', $id)
            ->setParameter('type', 'OEP')
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return LibelleGroupe[] Returns an array of LibelleGroupe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?LibelleGroupe
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
