<?php

declare(strict_types=1);

namespace App\Session\Application\Command;

use App\Entity\Inscription;
use App\Entity\StatutInscription;
use App\Exception\InscriptionImpossibleException;
use App\Repository\InscriptionRepository;
use App\Repository\SessionRepository;
use App\Repository\StagiaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class InscrireStagiaireHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SessionRepository $sessions,
        private StagiaireRepository $stagiaires,
        private InscriptionRepository $inscriptions,
    ) {
    }

    /**
     * @throws InscriptionImpossibleException si une règle métier empêche l'inscription
     */
    public function __invoke(InscrireStagiaire $commande): Inscription
    {
        $session = $this->sessions->findOneByCode($commande->codeSession)
            ?? throw new \InvalidArgumentException(sprintf('Session %s inconnue.', $commande->codeSession));
        $stagiaire = $this->stagiaires->findOneByEmail($commande->emailStagiaire)
            ?? throw new \InvalidArgumentException(sprintf('Stagiaire %s inconnu.', $commande->emailStagiaire));

        if ($this->inscriptions->existeInscriptionActive($stagiaire, $session)) {
            throw new InscriptionImpossibleException(sprintf(
                '%s est déjà inscrit(e) à la session %s.',
                $stagiaire->getEmail(),
                $session->getCode(),
            ));
        }

        if ($session->estComplete() && $session->estListeAttentePleine()) {
            throw new InscriptionImpossibleException(sprintf(
                'La session %s est complète et sa liste d\'attente est pleine.',
                $session->getCode(),
            ));
        }

        $statut = $session->estComplete() ? StatutInscription::LISTE_ATTENTE : StatutInscription::CONFIRMEE;

        $inscription = new Inscription($session, $stagiaire, $statut);

        $this->entityManager->persist($inscription);
        $this->entityManager->flush();

        return $inscription;
    }
}
