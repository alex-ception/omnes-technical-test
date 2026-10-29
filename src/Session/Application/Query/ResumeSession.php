<?php

declare(strict_types=1);

namespace App\Session\Application\Query;

use App\Session\Domain\Model\Session;

/**
 * Vue en lecture seule d'une session, pour la liste des sessions (page et API).
 */
final readonly class ResumeSession
{
    public function __construct(
        public string $code,
        public string $formation,
        public \DateTimeImmutable $dateDebut,
        public \DateTimeImmutable $dateFin,
        public int $capacite,
        public int $placesRestantes,
        public bool $complete,
    ) {
    }

    public static function depuisSession(Session $session): self
    {
        return new self(
            $session->getCode(),
            $session->getFormation()->getTitre(),
            $session->getDateDebut(),
            $session->getDateFin(),
            $session->getCapacite(),
            $session->getPlacesRestantes(),
            $session->estComplete(),
        );
    }
}
