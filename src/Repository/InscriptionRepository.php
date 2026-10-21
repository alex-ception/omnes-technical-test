<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Inscription;
use App\Entity\Session;
use App\Entity\Stagiaire;
use App\Entity\StatutInscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inscription>
 */
class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    /**
     * Vrai si le stagiaire a déjà une inscription non annulée sur cette session.
     */
    public function existeInscriptionActive(Stagiaire $stagiaire, Session $session): bool
    {
        $nombre = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.stagiaire = :stagiaire')
            ->andWhere('i.session = :session')
            ->andWhere('i.statut != :annulee')
            ->setParameter('stagiaire', $stagiaire)
            ->setParameter('session', $session)
            ->setParameter('annulee', StatutInscription::ANNULEE->value)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $nombre > 0;
    }
}
