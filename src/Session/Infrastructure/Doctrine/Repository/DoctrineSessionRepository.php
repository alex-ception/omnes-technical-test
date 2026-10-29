<?php

declare(strict_types=1);

namespace App\Session\Infrastructure\Doctrine\Repository;

use App\Session\Domain\Model\Inscription;
use App\Session\Domain\Model\Session;
use App\Session\Domain\Repository\SessionRepository;
use App\Session\Infrastructure\Doctrine\Entity\Inscription as InscriptionEntite;
use App\Session\Infrastructure\Doctrine\Entity\Session as SessionEntite;
use App\Session\Infrastructure\Doctrine\Entity\Stagiaire as StagiaireEntite;
use App\Session\Infrastructure\Doctrine\Mapper\SessionMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final readonly class DoctrineSessionRepository implements SessionRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SessionMapper $mapper,
    ) {
    }

    public function trouverParCode(string $code): ?Session
    {
        /** @var SessionEntite|null $entite */
        $entite = $this->requeteAvecInscriptions()
            ->andWhere('s.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();

        return null === $entite ? null : $this->mapper->versDomaine($entite);
    }

    public function listerParDateDeDebut(): array
    {
        /** @var list<SessionEntite> $entites */
        $entites = $this->requeteAvecInscriptions()
            ->orderBy('s.dateDebut', 'ASC')
            ->addOrderBy('s.code', 'ASC')
            ->addOrderBy('i.id', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map($this->mapper->versDomaine(...), $entites);
    }

    public function enregistrer(Session $session): void
    {
        $sessionId = $session->getId() ?? throw new \LogicException(sprintf('La session %s n\'a jamais été enregistrée.', $session->getCode()));

        /** @var list<array{Inscription, InscriptionEntite}> $nouvelles */
        $nouvelles = [];

        foreach ($session->getInscriptions() as $inscription) {
            if (null !== $inscription->getId()) {
                continue;
            }

            $stagiaireId = $inscription->getStagiaire()->getId()
                ?? throw new \LogicException(sprintf('Le stagiaire %s n\'a jamais été enregistré.', $inscription->getStagiaire()->getEmail()));

            $entite = new InscriptionEntite(
                $this->entityManager->getReference(SessionEntite::class, $sessionId),
                $this->entityManager->getReference(StagiaireEntite::class, $stagiaireId),
                $inscription->getStatut(),
                $inscription->getDateInscription(),
            );
            $this->entityManager->persist($entite);
            $nouvelles[] = [$inscription, $entite];
        }

        $this->entityManager->flush();

        foreach ($nouvelles as [$inscription, $entite]) {
            // Le domaine n'expose pas de mutateur technique : l'identifiant est renseigné par réflexion.
            (new \ReflectionProperty(Inscription::class, 'id'))->setValue($inscription, $entite->getId());
        }
    }

    private function requeteAvecInscriptions(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s', 'f', 'i', 'st')
            ->from(SessionEntite::class, 's')
            ->join('s.formation', 'f')
            ->leftJoin('s.inscriptions', 'i')
            ->leftJoin('i.stagiaire', 'st');
    }
}
