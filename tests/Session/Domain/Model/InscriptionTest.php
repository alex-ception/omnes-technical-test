<?php

declare(strict_types=1);

namespace App\Tests\Session\Domain\Model;

use App\Session\Domain\Model\Formation;
use App\Session\Domain\Model\Inscription;
use App\Session\Domain\Model\Session;
use App\Session\Domain\Model\Stagiaire;
use App\Session\Domain\Model\StatutInscription;
use PHPUnit\Framework\TestCase;

final class InscriptionTest extends TestCase
{
    public function testUneInscriptionEstConfirmeeParDefaut(): void
    {
        $inscription = new Inscription($this->uneSession(), $this->unStagiaire());

        self::assertSame(StatutInscription::CONFIRMEE, $inscription->getStatut());
    }

    public function testAnnulerPasseLeStatutAAnnulee(): void
    {
        $inscription = new Inscription($this->uneSession(), $this->unStagiaire());

        $inscription->annuler();

        self::assertSame(StatutInscription::ANNULEE, $inscription->getStatut());
    }

    public function testUneInscriptionEstAjouteeALaSession(): void
    {
        $session = $this->uneSession();

        $inscription = new Inscription($session, $this->unStagiaire());

        self::assertContains($inscription, $session->getInscriptions());
    }

    public function testUneInscriptionNouvelleNAPasDIdentifiant(): void
    {
        $inscription = new Inscription($this->uneSession(), $this->unStagiaire());

        self::assertNull($inscription->getId());
    }

    private function uneSession(): Session
    {
        return new Session(
            new Formation('TEST', 'Formation de test'),
            'TEST-01',
            new \DateTimeImmutable('2027-03-01'),
            new \DateTimeImmutable('2027-03-02'),
            4,
        );
    }

    private function unStagiaire(): Stagiaire
    {
        return new Stagiaire('Test', 'Stagiaire', 'test.stagiaire@exemple.fr');
    }
}
