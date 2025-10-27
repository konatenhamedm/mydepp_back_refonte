<?php

namespace App\Repository;

use App\Entity\Professionnel;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    use TableInfoTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }


    public function remove(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function add(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLastTransactionByUser($userId): ?Transaction
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :userId')
            ->andWhere('t.type = :state')
            ->setParameter('state', "NOUVELLE DEMANDE")
            ->setParameter('userId', $userId)
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function montantTotal()
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.montant) AS total')
            ->andWhere('t.state = :state')
            ->setParameter('state', 1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function transactionsEchoueesDuJour($tate)
    {
        $dateDebut = new \DateTimeImmutable('today'); // aujourd'hui à 00:00:00
        $dateFin = $dateDebut->modify('+1 day');      // demain à 00:00:00

        return $this->createQueryBuilder('t')
            ->where('t.type = :state')
            ->andWhere('t.createdAt >= :debut')
            ->andWhere('t.createdAt < :fin')
            ->setParameter('state', $tate)
            ->setParameter('debut', $dateDebut)
            ->setParameter('fin', $dateFin)
            ->getQuery()
            ->getResult();
    }

    public function nextNumero($annee)
    {
        $data = $this->lastNumero($annee);
        if ($data && $data['reference']) {
            $reference = $data['reference'];

            if (strpos($reference, '-') !== false) {
                [, $numero] = explode('-', $reference);
                $numero = ltrim($numero, '0');
            } else {
                $numero = 0;
            }
        } else {
            $numero = 0;
        }


        $code = "UP";
        $chrono = str_pad($numero + 1, 4, '0', STR_PAD_LEFT);
        $annee = substr($annee, -2);



        return "{$code}{$annee}-{$chrono}";
    }




    public function getHistorique()
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $transaction = $this->getTableName(Transaction::class, $em);
        $user = $this->getTableName(User::class, $em);
        $professionnel = $this->getTableName(Professionnel::class, $em);


        $sql = <<<SQL
        SELECT *
        FROM {$transaction} p
        JOIN {$user} u ON u.id = p.user_id
        /* JOIN {$professionnel} pr ON pr.user_id = u.id */
        SQL;

        $stmt = $connection->executeQuery($sql);

        return $stmt->fetchAllAssociative();
    }


    public function getAllTransaction($type): array
    {
        if ($type == 'admin') {
            $query = $this->createQueryBuilder('t')
                ->andWhere('t.user is not null')
                ->andWhere('t.state = :state')
                ->setParameter('state', 1)
                ->orderBy('t.id', 'ASC');
        } else {
            $query = $this->createQueryBuilder('t')
                ->andWhere('t.user is not null')
                ->andWhere('t.state = :state')
                ->andWhere('t.typeUser = :type')
                ->setParameter('state', 1)
                ->setParameter('type', $type)
                ->orderBy('t.id', 'ASC');

        }

        return $query->getQuery()->getResult();
    }
    public function getAllTransactionByUser($user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.state = :state')
            ->setParameter('state', 1)
            ->setParameter('user', $user)
            ->orderBy('t.id', 'ASC')

            ->getQuery()
            ->getResult()
        ;
    }


    

    //    /**
    //     * @return Transaction[] Returns an array of Transaction objects
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

    //    public function findOneBySomeField($value): ?Transaction
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
