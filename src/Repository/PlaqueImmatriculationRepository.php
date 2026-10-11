<?php

namespace App\Repository;

use App\Entity\PlaqueImmatriculation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlaqueImmatriculation>
 */
class PlaqueImmatriculationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaqueImmatriculation::class);
    }

    public function add(PlaqueImmatriculation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PlaqueImmatriculation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retrouve une plaque déjà importée : par immatriculation si elle existe,
     * sinon par dénomination. Permet de réimporter une fiche sans changer
     * son token (les QR codes déjà imprimés restent valides).
     */
    public function findExisting(?string $immatriculation, string $denomination): ?PlaqueImmatriculation
    {
        if ($immatriculation) {
            $found = $this->findOneBy(['immatriculation' => $immatriculation]);
            if ($found) {
                return $found;
            }
        }

        return $this->findOneBy(['denomination' => $denomination]);
    }

    /**
     * @return PlaqueImmatriculation[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.denomination', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
