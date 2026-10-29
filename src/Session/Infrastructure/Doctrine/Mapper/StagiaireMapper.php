<?php

declare(strict_types=1);

namespace App\Session\Infrastructure\Doctrine\Mapper;

use App\Entity\Stagiaire as StagiaireEntite;
use App\Session\Domain\Model\Stagiaire;

final class StagiaireMapper
{
    public function versDomaine(StagiaireEntite $entite): Stagiaire
    {
        return new Stagiaire($entite->getPrenom(), $entite->getNom(), $entite->getEmail(), $entite->getId());
    }
}
