<?php

declare(strict_types=1);

namespace App\Session\Infrastructure\Doctrine\Mapper;

use App\Session\Domain\Model\Stagiaire;
use App\Session\Infrastructure\Doctrine\Entity\Stagiaire as StagiaireEntite;

final class StagiaireMapper
{
    public function versDomaine(StagiaireEntite $entite): Stagiaire
    {
        return new Stagiaire($entite->getPrenom(), $entite->getNom(), $entite->getEmail(), $entite->getId());
    }
}
