<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\InscriptionService;
use App\Session\Domain\Exception\InscriptionImpossibleException;
use App\Session\Domain\Model\StatutInscription;
use App\Session\Domain\Repository\SessionRepository;
use App\Session\Domain\Repository\StagiaireRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class InscriptionServiceTest extends KernelTestCase
{
    public function testUnStagiaireSInscritAUneSessionOuverte(): void
    {
        self::bootKernel();

        $inscription = $this->service()->inscrire(
            $this->stagiaire('alice.martin@exemple.fr'),
            $this->session('GIT-QUO-01'),
        );

        self::assertNotNull($inscription->getId());
        self::assertSame(StatutInscription::CONFIRMEE, $inscription->getStatut());
    }

    public function testUnStagiaireNePeutPasSInscrireDeuxFoisALaMemeSession(): void
    {
        self::bootKernel();

        $this->expectException(InscriptionImpossibleException::class);

        // Alice est déjà inscrite (confirmée) à SYMF-ADV-01 dans les fixtures.
        $this->service()->inscrire(
            $this->stagiaire('alice.martin@exemple.fr'),
            $this->session('SYMF-ADV-01'),
        );
    }

    public function testUnStagiaireEstInscritEnListeDAttenteSiLaSessionEstComplete(): void
    {
        self::bootKernel();

        // PHP-MOD-01 : capacité 3, 3 inscriptions confirmées, personne en liste d'attente.
        $inscription = $this->service()->inscrire(
            $this->stagiaire('alice.martin@exemple.fr'),
            $this->session('PHP-MOD-01'),
        );

        self::assertNotNull($inscription->getId());
        self::assertSame(StatutInscription::LISTE_ATTENTE, $inscription->getStatut());
    }

    public function testLInscriptionEstRefuseeSiLaListeDAttenteEstPleine(): void
    {
        self::bootKernel();

        // PHP-MOD-01 est complète : Alice et Bruno remplissent la liste d'attente.
        $session = $this->session('PHP-MOD-01');
        $this->service()->inscrire($this->stagiaire('alice.martin@exemple.fr'), $session);
        $this->service()->inscrire($this->stagiaire('bruno.petit@exemple.fr'), $session);

        $this->expectException(InscriptionImpossibleException::class);

        $this->service()->inscrire($this->stagiaire('chloe.durand@exemple.fr'), $session);
    }

    private function service(): InscriptionService
    {
        return static::getContainer()->get(InscriptionService::class);
    }

    private function session(string $code): \App\Session\Domain\Model\Session
    {
        $session = static::getContainer()->get(SessionRepository::class)->trouverParCode($code);
        self::assertNotNull($session, sprintf('Session %s absente des fixtures.', $code));

        return $session;
    }

    private function stagiaire(string $email): \App\Session\Domain\Model\Stagiaire
    {
        $stagiaire = static::getContainer()->get(StagiaireRepository::class)->trouverParEmail($email);
        self::assertNotNull($stagiaire, sprintf('Stagiaire %s absent des fixtures.', $email));

        return $stagiaire;
    }
}
