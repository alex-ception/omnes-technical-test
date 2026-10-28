<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Formation;
use App\Entity\Inscription;
use App\Entity\Session;
use App\Entity\Stagiaire;
use App\Entity\StatutInscription;
use PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
    public function testUneSessionSansInscriptionATouteSaCapaciteDisponible(): void
    {
        $session = $this->uneSession(capacite: 4);

        self::assertSame(4, $session->getPlacesRestantes());
    }

    public function testUneInscriptionConfirmeeConsommeUnePlace(): void
    {
        $session = $this->uneSession(capacite: 4);

        new Inscription($session, $this->unStagiaire('a'), StatutInscription::CONFIRMEE);
        new Inscription($session, $this->unStagiaire('b'), StatutInscription::CONFIRMEE);

        self::assertSame(2, $session->getPlacesRestantes());
    }

    public function testUneInscriptionAnnuleeNeConsommePasDePlace(): void
    {
        $session = $this->uneSession(capacite: 4);

        new Inscription($session, $this->unStagiaire('a'), StatutInscription::CONFIRMEE);
        new Inscription($session, $this->unStagiaire('b'), StatutInscription::ANNULEE);

        self::assertSame(3, $session->getPlacesRestantes());
    }

    public function testUneInscriptionEnListeDAttenteNeConsommePasDePlace(): void
    {
        $session = $this->uneSession(capacite: 4);

        new Inscription($session, $this->unStagiaire('a'), StatutInscription::CONFIRMEE);
        new Inscription($session, $this->unStagiaire('b'), StatutInscription::LISTE_ATTENTE);

        self::assertSame(3, $session->getPlacesRestantes());
    }

    private function uneSession(int $capacite): Session
    {
        return new Session(
            new Formation('TEST', 'Formation de test'),
            'TEST-01',
            new \DateTimeImmutable('2027-03-01'),
            new \DateTimeImmutable('2027-03-02'),
            $capacite,
        );
    }

    private function unStagiaire(string $prenom): Stagiaire
    {
        return new Stagiaire($prenom, 'Stagiaire', $prenom.'.stagiaire@exemple.fr');
    }
}
