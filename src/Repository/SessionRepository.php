<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /**
     * Toutes les sessions, triées par date de début, avec leur formation et leurs inscriptions.
     *
     * @return list<Session>
     */
    public function findAllAvecFormation(): array
    {
        return $this->createQueryBuilder('s')
            ->addSelect('f', 'i')
            ->join('s.formation', 'f')
            ->leftJoin('s.inscriptions', 'i')
            ->orderBy('s.dateDebut', 'ASC')
            ->addOrderBy('s.code', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByCode(string $code): ?Session
    {
        return $this->findOneBy(['code' => $code]);
    }
}
