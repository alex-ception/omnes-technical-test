<?php

declare(strict_types=1);

namespace App\Tests\Session\Domain\Model;

use App\Session\Domain\Exception\InscriptionImpossibleException;
use App\Session\Domain\Model\Formation;
use App\Session\Domain\Model\Inscription;
use App\Session\Domain\Model\Session;
use App\Session\Domain\Model\Stagiaire;
use App\Session\Domain\Model\StatutInscription;
use PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
    public function testUneSessionDoitAvoirUneCapaciteDAuMoinsUn(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->uneSession(capacite: 0);
    }

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

    public function testUneSessionAvecDesPlacesRestantesNEstPasComplete(): void
    {
        $session = $this->uneSession(capacite: 2);

        new Inscription($session, $this->unStagiaire('a'), StatutInscription::CONFIRMEE);
        new Inscription($session, $this->unStagiaire('b'), StatutInscription::ANNULEE);

        self::assertFalse($session->estComplete());
    }

    public function testUneSessionSansPlaceRestanteEstComplete(): void
    {
        $session = $this->uneSession(capacite: 2);

        new Inscription($session, $this->unStagiaire('a'), StatutInscription::CONFIRMEE);
        new Inscription($session, $this->unStagiaire('b'), StatutInscription::CONFIRMEE);

        self::assertTrue($session->estComplete());
    }

    public function testLaListeDAttenteNEstPasPleineEnDessousDeDeuxPersonnes(): void
    {
        $session = $this->uneSession(capacite: 1);

        new Inscription($session, $this->unStagiaire('a'), StatutInscription::CONFIRMEE);
        new Inscription($session, $this->unStagiaire('b'), StatutInscription::LISTE_ATTENTE);

        self::assertFalse($session->estListeAttentePleine());
    }

    public function testLaListeDAttenteEstPleineADeuxPersonnes(): void
    {
        $session = $this->uneSession(capacite: 1);

        new Inscription($session, $this->unStagiaire('a'), StatutInscription::CONFIRMEE);
        new Inscription($session, $this->unStagiaire('b'), StatutInscription::LISTE_ATTENTE);
        new Inscription($session, $this->unStagiaire('c'), StatutInscription::LISTE_ATTENTE);

        self::assertTrue($session->estListeAttentePleine());
    }

    public function testUneInscriptionAnnuleeNeComptePasDansLaListeDAttente(): void
    {
        $session = $this->uneSession(capacite: 1);

        new Inscription($session, $this->unStagiaire('a'), StatutInscription::CONFIRMEE);
        new Inscription($session, $this->unStagiaire('b'), StatutInscription::LISTE_ATTENTE);
        new Inscription($session, $this->unStagiaire('c'), StatutInscription::ANNULEE);

        self::assertFalse($session->estListeAttentePleine());
    }

    public function testInscrireConfirmeLeStagiaireSIlResteUnePlace(): void
    {
        $session = $this->uneSession(capacite: 1);

        $inscription = $session->inscrire($this->unStagiaire('a'));

        self::assertSame(StatutInscription::CONFIRMEE, $inscription->getStatut());
        self::assertContains($inscription, $session->getInscriptions());
    }

    public function testInscrireMetLeStagiaireEnListeDAttenteSiLaSessionEstComplete(): void
    {
        $session = $this->uneSession(capacite: 1);
        $session->inscrire($this->unStagiaire('a'));

        $inscription = $session->inscrire($this->unStagiaire('b'));

        self::assertSame(StatutInscription::LISTE_ATTENTE, $inscription->getStatut());
    }

    public function testInscrireRefuseLeStagiaireSiLaListeDAttenteEstPleine(): void
    {
        $session = $this->uneSession(capacite: 1);
        $session->inscrire($this->unStagiaire('a'));
        $session->inscrire($this->unStagiaire('b'));
        $session->inscrire($this->unStagiaire('c'));

        $this->expectException(InscriptionImpossibleException::class);

        $session->inscrire($this->unStagiaire('d'));
    }

    public function testInscrireRefuseUnStagiaireDejaInscrit(): void
    {
        $session = $this->uneSession(capacite: 4);
        $session->inscrire($this->unStagiaire('a'));

        $this->expectException(InscriptionImpossibleException::class);

        $session->inscrire($this->unStagiaire('a'));
    }

    public function testInscrireRefuseUnStagiaireDejaEnListeDAttente(): void
    {
        $session = $this->uneSession(capacite: 1);
        $session->inscrire($this->unStagiaire('a'));
        $session->inscrire($this->unStagiaire('b'));

        $this->expectException(InscriptionImpossibleException::class);

        $session->inscrire($this->unStagiaire('b'));
    }

    public function testInscrireAccepteUnStagiaireDontLInscriptionAEteAnnulee(): void
    {
        $session = $this->uneSession(capacite: 4);
        $session->inscrire($this->unStagiaire('a'))->annuler();

        $inscription = $session->inscrire($this->unStagiaire('a'));

        self::assertSame(StatutInscription::CONFIRMEE, $inscription->getStatut());
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
