<?php

declare(strict_types=1);

namespace App\Session\Infrastructure\Doctrine\Mapper;

use App\Entity\Session as SessionEntite;
use App\Session\Domain\Model\Formation;
use App\Session\Domain\Model\Inscription;
use App\Session\Domain\Model\Session;
use App\Session\Domain\Model\StatutInscription;

final readonly class SessionMapper
{
    public function __construct(
        private StagiaireMapper $stagiaireMapper,
    ) {
    }

    /**
     * Reconstitue la session du domaine avec sa formation et toutes ses inscriptions.
     */
    public function versDomaine(SessionEntite $entite): Session
    {
        $formation = $entite->getFormation();

        $session = new Session(
            new Formation($formation->getCode(), $formation->getTitre(), $formation->getId()),
            $entite->getCode(),
            $entite->getDateDebut(),
            $entite->getDateFin(),
            $entite->getCapacite(),
            $entite->getId(),
        );

        foreach ($entite->getInscriptions() as $inscription) {
            // Le constructeur d'Inscription l'ajoute à la session.
            new Inscription(
                $session,
                $this->stagiaireMapper->versDomaine($inscription->getStagiaire()),
                StatutInscription::from($inscription->getStatut()->value),
                $inscription->getDateInscription(),
                $inscription->getId(),
            );
        }

        return $session;
    }
}
