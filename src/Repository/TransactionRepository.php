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
            ->andWhere('t.state = :state')
            ->setParameter('state', 1)
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
            ->innerJoin('t.user', 'u')
                ->andWhere('t.user is not null')
                ->andWhere('t.state = :state')
                ->setParameter('state', 1)
                ->orderBy('t.id', 'ASC');
        } else {
            $query = $this->createQueryBuilder('t')
            ->innerJoin('t.user', 'u')
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
            ->innerJoin('t.user', 'u')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getComptableBilanData($startDate = null, $endDate = null, $professionId = null, $regionId = null): array
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();

        $transactionTable = $this->getTableName(Transaction::class, $em);
        $userTable = $this->getTableName(\App\Entity\User::class, $em);
        $personneTable = $this->getTableName(\App\Entity\Entite::class, $em);
        $professionnelTable = $this->getTableName(\App\Entity\Professionnel::class, $em);
        $professionTable = $this->getTableName(\App\Entity\Profession::class, $em);
        $regionTable = $this->getTableName(\App\Entity\Region::class, $em);

        $sql = "
            SELECT 
                t.id, 
                t.montant, 
                t.state, 
                t.channel, 
                t.type_user as typeUser, 
                t.created_at as createdAt,
                prof.libelle as professionLibelle,
                reg.libelle as regionLibelle
            FROM {$transactionTable} t
            LEFT JOIN {$userTable} u ON t.user_id = u.id
            LEFT JOIN {$personneTable} p ON u.personne_id = p.id
            LEFT JOIN {$professionnelTable} mp ON p.id = mp.id
            LEFT JOIN {$professionTable} prof ON mp.profession_id = prof.id
            LEFT JOIN {$regionTable} reg ON mp.region_id = reg.id
            WHERE t.state IN (0, 1)
        ";

        $params = [];

        if ($startDate) {
            $sql .= " AND t.created_at >= :startDate";
            $params['startDate'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $sql .= " AND t.created_at <= :endDate";
            $params['endDate'] = $endDate . ' 23:59:59';
        }

        if ($professionId) {
            $sql .= " AND mp.profession_id = :professionId";
            $params['professionId'] = $professionId;
        }

        if ($regionId) {
            $sql .= " AND mp.region_id = :regionId";
            $params['regionId'] = $regionId;
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $conn->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }

    public function getFilteredTransactions($type, $search = null, $montant = null, $startDate = null, $endDate = null, $professionId = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.user', 'u')
            ->leftJoin('u.personne', 'p')
            ->andWhere('t.user IS NOT NULL');

        if ($type !== 'admin') {
            $qb->andWhere('t.typeUser = :type')
               ->setParameter('type', $type);
        }

        if ($montant !== null && $montant !== '' && $montant !== 'all') {
            $qb->andWhere('t.montant = :montant')
               ->setParameter('montant', $montant);
        }

        if ($startDate) {
            $qb->andWhere('t.createdAt >= :startDate')
               ->setParameter('startDate', new \DateTime($startDate . ' 00:00:00'));
        }

        if ($endDate) {
            $qb->andWhere('t.createdAt <= :endDate')
               ->setParameter('endDate', new \DateTime($endDate . ' 23:59:59'));
        }

        if ($professionId) {
            $qb->innerJoin('App\Entity\Professionnel', 'mp', 'WITH', 'p.id = mp.id')
               ->andWhere('mp.profession = :professionId')
               ->setParameter('professionId', $professionId);
        }

        if ($search) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('t.reference', ':search'),
                $qb->expr()->like('t.type', ':search'),
                $qb->expr()->like('t.channel', ':search'),
                $qb->expr()->like('u.email', ':search'),
                $qb->expr()->like('p.nom', ':search'),
                $qb->expr()->like('p.prenoms', ':search')
            ))->setParameter('search', '%' . $search . '%');
        }

        $qb->andWhere('t.state = :state')
           ->setParameter('state', 1);

        $qb->orderBy('t.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getFilteredTransactionsKpis($type, $search = null, $montant = null, $startDate = null, $endDate = null, $professionId = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.user', 'u')
            ->leftJoin('u.personne', 'p')
            ->andWhere('t.user IS NOT NULL');

        if ($type !== 'admin') {
            $qb->andWhere('t.typeUser = :type')
               ->setParameter('type', $type);
        }

        if ($montant !== null && $montant !== '' && $montant !== 'all') {
            $qb->andWhere('t.montant = :montant')
               ->setParameter('montant', $montant);
        }

        if ($startDate) {
            $qb->andWhere('t.createdAt >= :startDate')
               ->setParameter('startDate', new \DateTime($startDate . ' 00:00:00'));
        }

        if ($endDate) {
            $qb->andWhere('t.createdAt <= :endDate')
               ->setParameter('endDate', new \DateTime($endDate . ' 23:59:59'));
        }

        if ($professionId) {
            $qb->innerJoin('App\Entity\Professionnel', 'mp', 'WITH', 'p.id = mp.id')
               ->andWhere('mp.profession = :professionId')
               ->setParameter('professionId', $professionId);
        }

        if ($search) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('t.reference', ':search'),
                $qb->expr()->like('t.type', ':search'),
                $qb->expr()->like('t.channel', ':search'),
                $qb->expr()->like('u.email', ':search'),
                $qb->expr()->like('p.nom', ':search'),
                $qb->expr()->like('p.prenoms', ':search')
            ))->setParameter('search', '%' . $search . '%');
        }

        $qb->select('t.montant, t.state');
        $results = $qb->getQuery()->getScalarResult();

        $montantTotal = 0;
        $nombreSuccess = 0;
        $nombreFail = 0;

        foreach ($results as $r) {
            $state = (int)$r['state'];
            $mont = (int)$r['montant'];
            if ($state === 1) {
                $montantTotal += $mont;
                $nombreSuccess++;
            } elseif ($state === 0) {
                $nombreFail++;
            }
        }

        return [
            'montantTotal' => $montantTotal,
            'nombreSuccess' => $nombreSuccess,
            'nombreFail' => $nombreFail,
        ];
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
