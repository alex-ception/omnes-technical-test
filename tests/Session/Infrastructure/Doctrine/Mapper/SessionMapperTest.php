<?php

declare(strict_types=1);

namespace App\Tests\Session\Infrastructure\Doctrine\Mapper;

use App\Entity\Formation as FormationEntite;
use App\Entity\Inscription as InscriptionEntite;
use App\Entity\Session as SessionEntite;
use App\Entity\Stagiaire as StagiaireEntite;
use App\Entity\StatutInscription as StatutInscriptionEntite;
use App\Session\Domain\Model\Inscription;
use App\Session\Domain\Model\StatutInscription;
use App\Session\Infrastructure\Doctrine\Mapper\SessionMapper;
use App\Session\Infrastructure\Doctrine\Mapper\StagiaireMapper;
use PHPUnit\Framework\TestCase;

final class SessionMapperTest extends TestCase
{
    public function testUneSessionEstConvertieAvecSaFormation(): void
    {
        $entite = new SessionEntite(
            new FormationEntite('SYMF-ADV', 'Symfony avancé'),
            'SYMF-ADV-01',
            new \DateTimeImmutable('2026-11-03'),
            new \DateTimeImmutable('2026-11-05'),
            5,
        );

        $session = $this->mapper()->versDomaine($entite);

        self::assertSame('SYMF-ADV-01', $session->getCode());
        self::assertSame('SYMF-ADV', $session->getFormation()->getCode());
        self::assertSame('Symfony avancé', $session->getFormation()->getTitre());
        self::assertEquals(new \DateTimeImmutable('2026-11-03'), $session->getDateDebut());
        self::assertEquals(new \DateTimeImmutable('2026-11-05'), $session->getDateFin());
        self::assertSame(5, $session->getCapacite());
    }

    public function testLesInscriptionsSontConverties(): void
    {
        $entite = new SessionEntite(
            new FormationEntite('PHP-MOD', 'PHP moderne'),
            'PHP-MOD-01',
            new \DateTimeImmutable('2026-11-17'),
            new \DateTimeImmutable('2026-11-18'),
            3,
        );
        new InscriptionEntite($entite, new StagiaireEntite('Alice', 'Martin', 'alice.martin@exemple.fr'), StatutInscriptionEntite::CONFIRMEE, new \DateTimeImmutable('2026-09-01 09:12'));
        new InscriptionEntite($entite, new StagiaireEntite('Bruno', 'Petit', 'bruno.petit@exemple.fr'), StatutInscriptionEntite::ANNULEE);
        new InscriptionEntite($entite, new StagiaireEntite('Chloé', 'Durand', 'chloe.durand@exemple.fr'), StatutInscriptionEntite::LISTE_ATTENTE);

        $session = $this->mapper()->versDomaine($entite);

        self::assertSame(
            [
                ['alice.martin@exemple.fr', StatutInscription::CONFIRMEE],
                ['bruno.petit@exemple.fr', StatutInscription::ANNULEE],
                ['chloe.durand@exemple.fr', StatutInscription::LISTE_ATTENTE],
            ],
            array_map(
                static fn (Inscription $inscription): array => [$inscription->getStagiaire()->getEmail(), $inscription->getStatut()],
                $session->getInscriptions(),
            ),
        );
        self::assertEquals(new \DateTimeImmutable('2026-09-01 09:12'), $session->getInscriptions()[0]->getDateInscription());
        self::assertSame($session, $session->getInscriptions()[0]->getSession());
        // Les règles du domaine s'appliquent aux inscriptions converties.
        self::assertSame(2, $session->getPlacesRestantes());
    }

    private function mapper(): SessionMapper
    {
        return new SessionMapper(new StagiaireMapper());
    }
}
