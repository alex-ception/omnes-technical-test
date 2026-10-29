<?php

declare(strict_types=1);

namespace App\Session\Application\Command;

use App\Session\Domain\Exception\InscriptionImpossibleException;
use App\Session\Domain\Exception\SessionIntrouvableException;
use App\Session\Domain\Exception\StagiaireIntrouvableException;
use App\Session\Domain\Model\Inscription;
use App\Session\Domain\Repository\SessionRepository;
use App\Session\Domain\Repository\StagiaireRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class InscrireStagiaireHandler
{
    public function __construct(
        private SessionRepository $sessions,
        private StagiaireRepository $stagiaires,
    ) {
    }

    /**
     * @throws SessionIntrouvableException    si aucune session ne porte ce code
     * @throws StagiaireIntrouvableException  si aucun stagiaire n'a cet email
     * @throws InscriptionImpossibleException si une règle métier empêche l'inscription
     */
    public function __invoke(InscrireStagiaire $commande): Inscription
    {
        $session = $this->sessions->trouverParCode($commande->codeSession)
            ?? throw new SessionIntrouvableException($commande->codeSession);
        $stagiaire = $this->stagiaires->trouverParEmail($commande->emailStagiaire)
            ?? throw new StagiaireIntrouvableException($commande->emailStagiaire);

        $inscription = $session->inscrire($stagiaire);

        $this->sessions->enregistrer($session);

        return $inscription;
    }
}
