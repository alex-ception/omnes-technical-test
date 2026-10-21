<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Inscription;
use App\Entity\Session;
use App\Entity\Stagiaire;
use App\Entity\StatutInscription;
use App\Exception\InscriptionImpossibleException;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;

final class InscriptionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly InscriptionRepository $inscriptions,
    ) {
    }

    /**
     * Inscrit un stagiaire à une session.
     *
     * @throws InscriptionImpossibleException si une règle métier empêche l'inscription
     */
    public function inscrire(Stagiaire $stagiaire, Session $session): Inscription
    {
        if ($this->inscriptions->existeInscriptionActive($stagiaire, $session)) {
            throw new InscriptionImpossibleException(sprintf(
                '%s est déjà inscrit(e) à la session %s.',
                $stagiaire->getEmail(),
                $session->getCode(),
            ));
        }

        $inscription = new Inscription($session, $stagiaire, StatutInscription::CONFIRMEE);

        $this->entityManager->persist($inscription);
        $this->entityManager->flush();

        return $inscription;
    }
}
