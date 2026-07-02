<?php

namespace App\Service;

use App\Entity\Entite;
use App\Entity\Etablissement;
use App\Entity\Professionnel;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionChecker
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Vérifie si l'abonnement de la personne a expiré.
     * Si c'est le cas, met à jour son statut et effectue un flush en base de données.
     *
     * @param Entite|null $personne
     * @return bool True si expiré, False sinon
     */
    public function checkExpiration(?Entite $personne): bool
    {
        if (!$personne) {
            return false;
        }

        $isExpired = false;
        $currentYear = (int) date('Y');

        if ($personne instanceof Professionnel) {
            $profession = $personne->getProfession();
            
            // Vérifier si la profession est payante
            $isPayante = false;
            if ($profession) {
                $montantNouveau = (int) $profession->getMontantNouvelleDemande();
                $montantRenouv = (int) $profession->getMontantRenouvellement();
                if ($montantNouveau > 0 || $montantRenouv > 0) {
                    $isPayante = true;
                }
            }

            if ($isPayante) {
                $code = $personne->getCode();
                if ($code && preg_match('/^[A-Za-z]{2}(\d{4})/', $code, $matches)) {
                    $yearCode = (int) $matches[1];
                    if ($yearCode < $currentYear) {
                        $isExpired = true;
                    }
                }
            }
        } elseif ($personne instanceof Etablissement) {
            $code = $personne->getCode();
            // On extrait les 4 premiers chiffres qui suivent les éventuelles lettres au début du code
            if ($code && preg_match('/^[A-Za-z]{2}(\d{4})/', $code, $matches)) {
                $yearCode = (int) $matches[1];
                if ($yearCode < $currentYear) {
                    $isExpired = true;
                }
            }
        }

        // Si le compte a expiré et que le statut n'est pas encore "renouvellement"
        // On met à jour pour forcer le front-end à l'afficher comme tel
        if ($isExpired && $personne->getStatus() !== 'renouvellement') {
            $personne->setStatus('renouvellement');
            $this->em->persist($personne);
            $this->em->flush();
        }

        return $isExpired;
    }
}
