<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\StatutInscription;
use App\Exception\InscriptionImpossibleException;
use App\Repository\SessionRepository;
use App\Repository\StagiaireRepository;
use App\Service\InscriptionService;
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

    private function service(): InscriptionService
    {
        return static::getContainer()->get(InscriptionService::class);
    }

    private function session(string $code): \App\Entity\Session
    {
        $session = static::getContainer()->get(SessionRepository::class)->findOneByCode($code);
        self::assertNotNull($session, sprintf('Session %s absente des fixtures.', $code));

        return $session;
    }

    private function stagiaire(string $email): \App\Entity\Stagiaire
    {
        $stagiaire = static::getContainer()->get(StagiaireRepository::class)->findOneByEmail($email);
        self::assertNotNull($stagiaire, sprintf('Stagiaire %s absent des fixtures.', $email));

        return $stagiaire;
    }
}
