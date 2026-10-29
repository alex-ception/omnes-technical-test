<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Inscription;
use App\Entity\Session;
use App\Entity\Stagiaire;
use App\Exception\InscriptionImpossibleException;
use App\Session\Application\Command\InscrireStagiaire;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * Façade conservée pour le portail stagiaire : délègue l'inscription à la commande InscrireStagiaire.
 */
final class InscriptionService
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
    ) {
    }

    /**
     * Inscrit un stagiaire à une session.
     *
     * @throws InscriptionImpossibleException si une règle métier empêche l'inscription
     */
    public function inscrire(Stagiaire $stagiaire, Session $session): Inscription
    {
        try {
            $enveloppe = $this->commandBus->dispatch(new InscrireStagiaire($session->getCode(), $stagiaire->getEmail()));
        } catch (HandlerFailedException $e) {
            // Messenger enveloppe les exceptions du handler : on remonte l'exception métier telle quelle.
            foreach ($e->getWrappedExceptions(InscriptionImpossibleException::class) as $exception) {
                throw $exception;
            }

            throw $e;
        }

        /** @var HandledStamp $resultat */
        $resultat = $enveloppe->last(HandledStamp::class);

        return $resultat->getResult();
    }
}
