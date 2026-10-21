<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Formation;
use App\Entity\Inscription;
use App\Entity\Session;
use App\Entity\Stagiaire;
use App\Entity\StatutInscription;
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

        self::assertTrue($session->getInscriptions()->contains($inscription));
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
