<?php

namespace App\Repository;

use App\Entity\PlaqueParametre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlaqueParametre>
 */
class PlaqueParametreRepository extends ServiceEntityRepository
{
    public const CODE_ACCES_DEFAUT = '12345678';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaqueParametre::class);
    }

    /**
     * Retourne l'unique ligne de paramètres, créée avec le code par défaut si absente.
     */
    public function getParametres(): PlaqueParametre
    {
        $parametre = $this->findOneBy([], ['id' => 'ASC']);

        if (!$parametre) {
            $parametre = new PlaqueParametre();
            $parametre->setCodeAccesHash(password_hash(self::CODE_ACCES_DEFAUT, PASSWORD_DEFAULT));
            $this->getEntityManager()->persist($parametre);
            $this->getEntityManager()->flush();
        }

        return $parametre;
    }
}
