<?php

namespace App\Repository;

use App\Entity\TypeDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeDocument>
 */
class TypeDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeDocument::class);
    }

    public function add(TypeDocument $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }



    public function remove(TypeDocument $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllByLibelleGroupe(): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.libelleGroupe', 'lg')
            ->groupBy('lg.libelle')
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByTypePersonneGrouped(int $typePersonneId): array
    {
        $qb = $this->createQueryBuilder('td')
            ->join('td.libelleGroupe', 'lg')
            ->join('td.typePersonne', 'tp')
            ->andWhere('tp.id = :typePersonneId')
            ->setParameter('typePersonneId', $typePersonneId)
            ->orderBy('lg.libelle', 'ASC');

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return TypeDocument[] Returns an array of TypeDocument objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TypeDocument
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
