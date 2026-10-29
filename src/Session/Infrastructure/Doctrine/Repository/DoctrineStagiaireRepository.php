<?php

declare(strict_types=1);

namespace App\Session\Infrastructure\Doctrine\Repository;

use App\Session\Domain\Model\Stagiaire;
use App\Session\Domain\Repository\StagiaireRepository;
use App\Session\Infrastructure\Doctrine\Entity\Stagiaire as StagiaireEntite;
use App\Session\Infrastructure\Doctrine\Mapper\StagiaireMapper;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineStagiaireRepository implements StagiaireRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StagiaireMapper $mapper,
    ) {
    }

    public function trouverParEmail(string $email): ?Stagiaire
    {
        $entite = $this->entityManager->getRepository(StagiaireEntite::class)->findOneBy(['email' => $email]);

        return null === $entite ? null : $this->mapper->versDomaine($entite);
    }
}
