<?php

declare(strict_types=1);

namespace App\Tests\Session\Application\Query;

use App\Session\Application\Query\ListerSessions;
use App\Session\Application\Query\ListerSessionsHandler;
use App\Session\Application\Query\ResumeSession;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ListerSessionsHandlerTest extends KernelTestCase
{
    public function testLesSessionsSontTrieesParDateDeDebut(): void
    {
        self::bootKernel();

        $sessions = $this->handler()(new ListerSessions());

        self::assertSame(
            ['SYMF-ADV-01', 'PHP-MOD-01', 'DOCK-DEV-01', 'GIT-QUO-01', 'PHP-MOD-02'],
            array_map(static fn (ResumeSession $session): string => $session->code, $sessions),
        );
    }

    public function testUnResumeDeSessionPorteSesInformationsEtSesPlacesRestantes(): void
    {
        self::bootKernel();

        $session = $this->resume('SYMF-ADV-01');

        self::assertSame('Symfony avancé', $session->formation);
        self::assertEquals(new \DateTimeImmutable('2026-11-03'), $session->dateDebut);
        self::assertEquals(new \DateTimeImmutable('2026-11-05'), $session->dateFin);
        self::assertSame(5, $session->capacite);
        // 2 inscriptions confirmées et 2 annulées.
        self::assertSame(3, $session->placesRestantes);
        self::assertFalse($session->complete);
    }

    public function testUnResumeDeSessionIndiqueSiLaSessionEstComplete(): void
    {
        self::bootKernel();

        // PHP-MOD-01 : capacité 3, 3 inscriptions confirmées.
        $session = $this->resume('PHP-MOD-01');

        self::assertSame(0, $session->placesRestantes);
        self::assertTrue($session->complete);
    }

    private function resume(string $code): ResumeSession
    {
        foreach ($this->handler()(new ListerSessions()) as $session) {
            if ($code === $session->code) {
                return $session;
            }
        }

        self::fail(sprintf('Session %s absente du résultat.', $code));
    }

    private function handler(): ListerSessionsHandler
    {
        return static::getContainer()->get(ListerSessionsHandler::class);
    }
}
