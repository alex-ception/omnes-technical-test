<?php

declare(strict_types=1);

namespace App\Tests\Session\Application\Command;

use App\Session\Application\Command\InscrireStagiaire;
use App\Session\Application\Command\InscrireStagiaireHandler;
use App\Session\Domain\Exception\InscriptionImpossibleException;
use App\Session\Domain\Model\StatutInscription;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class InscrireStagiaireHandlerTest extends KernelTestCase
{
    public function testUnStagiaireEstConfirmeSurUneSessionOuverte(): void
    {
        self::bootKernel();

        $inscription = $this->handler()(new InscrireStagiaire('GIT-QUO-01', 'alice.martin@exemple.fr'));

        self::assertNotNull($inscription->getId());
        self::assertSame(StatutInscription::CONFIRMEE, $inscription->getStatut());
        self::assertSame('GIT-QUO-01', $inscription->getSession()->getCode());
        self::assertSame('alice.martin@exemple.fr', $inscription->getStagiaire()->getEmail());
    }

    public function testUnStagiaireEstInscritEnListeDAttenteSiLaSessionEstComplete(): void
    {
        self::bootKernel();

        // PHP-MOD-01 : capacité 3, 3 inscriptions confirmées, personne en liste d'attente.
        $inscription = $this->handler()(new InscrireStagiaire('PHP-MOD-01', 'alice.martin@exemple.fr'));

        self::assertSame(StatutInscription::LISTE_ATTENTE, $inscription->getStatut());
    }

    public function testLInscriptionEstRefuseeSiLaListeDAttenteEstPleine(): void
    {
        self::bootKernel();

        $this->handler()(new InscrireStagiaire('PHP-MOD-01', 'alice.martin@exemple.fr'));
        $this->handler()(new InscrireStagiaire('PHP-MOD-01', 'bruno.petit@exemple.fr'));

        $this->expectException(InscriptionImpossibleException::class);

        $this->handler()(new InscrireStagiaire('PHP-MOD-01', 'chloe.durand@exemple.fr'));
    }

    public function testUnStagiaireNePeutPasSInscrireDeuxFoisALaMemeSession(): void
    {
        self::bootKernel();

        $this->expectException(InscriptionImpossibleException::class);

        // Alice est déjà inscrite (confirmée) à SYMF-ADV-01 dans les fixtures.
        $this->handler()(new InscrireStagiaire('SYMF-ADV-01', 'alice.martin@exemple.fr'));
    }

    private function handler(): InscrireStagiaireHandler
    {
        return static::getContainer()->get(InscrireStagiaireHandler::class);
    }
}
