<?php

declare(strict_types=1);

namespace App\Tests\Session\Infrastructure\Doctrine\Repository;

use App\Session\Domain\Model\Session;
use App\Session\Domain\Model\StatutInscription;
use App\Session\Infrastructure\Doctrine\Mapper\SessionMapper;
use App\Session\Infrastructure\Doctrine\Mapper\StagiaireMapper;
use App\Session\Infrastructure\Doctrine\Repository\DoctrineSessionRepository;
use App\Session\Infrastructure\Doctrine\Repository\DoctrineStagiaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineSessionRepositoryTest extends KernelTestCase
{
    public function testTrouverParCodeRenvoieLaSessionDuDomaineAvecSesInscriptions(): void
    {
        self::bootKernel();

        $session = $this->sessions()->trouverParCode('SYMF-ADV-01');

        self::assertInstanceOf(Session::class, $session);
        self::assertNotNull($session->getId());
        self::assertSame('Symfony avancé', $session->getFormation()->getTitre());
        // 2 inscriptions confirmées et 2 annulées.
        self::assertCount(4, $session->getInscriptions());
        self::assertSame(3, $session->getPlacesRestantes());
    }

    public function testTrouverParCodeRenvoieNullPourUneSessionInconnue(): void
    {
        self::bootKernel();

        self::assertNull($this->sessions()->trouverParCode('INCONNUE-01'));
    }

    public function testListerParDateDeDebutRenvoieToutesLesSessionsTriees(): void
    {
        self::bootKernel();

        $sessions = $this->sessions()->listerParDateDeDebut();

        self::assertSame(
            ['SYMF-ADV-01', 'PHP-MOD-01', 'DOCK-DEV-01', 'GIT-QUO-01', 'PHP-MOD-02'],
            array_map(static fn (Session $session): string => $session->getCode(), $sessions),
        );
    }

    public function testEnregistrerPersisteLesNouvellesInscriptionsEtLeurAttribueUnIdentifiant(): void
    {
        self::bootKernel();

        // PHP-MOD-01 : capacité 3, 3 inscriptions confirmées.
        $session = $this->sessions()->trouverParCode('PHP-MOD-01');
        $alice = $this->stagiaires()->trouverParEmail('alice.martin@exemple.fr');
        self::assertNotNull($session);
        self::assertNotNull($alice);

        $inscription = $session->inscrire($alice);
        $this->sessions()->enregistrer($session);

        self::assertNotNull($inscription->getId());

        $this->entityManager()->clear();
        $relue = $this->sessions()->trouverParCode('PHP-MOD-01');
        self::assertNotNull($relue);
        self::assertCount(4, $relue->getInscriptions());
        self::assertSame(StatutInscription::LISTE_ATTENTE, $relue->getInscriptions()[3]->getStatut());
        self::assertSame('alice.martin@exemple.fr', $relue->getInscriptions()[3]->getStagiaire()->getEmail());
    }

    private function sessions(): DoctrineSessionRepository
    {
        return new DoctrineSessionRepository($this->entityManager(), new SessionMapper(new StagiaireMapper()));
    }

    private function stagiaires(): DoctrineStagiaireRepository
    {
        return new DoctrineStagiaireRepository($this->entityManager(), new StagiaireMapper());
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine.orm.entity_manager');
    }
}
