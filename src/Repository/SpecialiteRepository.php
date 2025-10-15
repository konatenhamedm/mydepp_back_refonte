<?php

namespace App\Repository;

use App\Entity\Professionnel;
use App\Entity\Specialite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Specialite>
 */
class SpecialiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Specialite::class);
    }

    public function add(Specialite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countSpecialiteProfByGenre($genre)
    {
   

        if($genre == "tout"){
            return $this->getEntityManager()->createQueryBuilder()
            ->select('c.libelle AS civilite, g.libelle AS genre, COUNT(e.id) AS nombre')
            ->from(Specialite::class, 'c')
            ->leftJoin(Professionnel::class, 'e', 'WITH', 'e.civilite = c.id')
            ->leftJoin('e.genre', 'g')
            ->groupBy('c.id, g.id')
            ->getQuery()
            ->getResult();
        }else{
            return $this->getEntityManager()->createQueryBuilder()
            ->select('c.libelle AS civilite, g.libelle AS genre, COUNT(e.id) AS nombre')
            ->from(Specialite::class, 'c')
            ->leftJoin(Professionnel::class, 'e', 'WITH', 'e.civilite = c.id')
            ->leftJoin('e.genre', 'g')
            ->andWhere("g.libelle = :val")
            ->setParameter('val', $genre)
            ->groupBy('c.id, g.id')
            ->getQuery()
            ->getResult();
        }
        

    }


    //    /**
    //     * @return Specialite[] Returns an array of Specialite objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Specialite
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
