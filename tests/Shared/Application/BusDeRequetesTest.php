<?php

declare(strict_types=1);

namespace App\Tests\Shared\Application;

use App\Session\Application\Query\ListerSessions;
use App\Session\Application\Query\ResumeSession;
use App\Shared\Application\BusDeRequetes;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class BusDeRequetesTest extends KernelTestCase
{
    public function testLeBusRenvoieLeResultatDuHandler(): void
    {
        self::bootKernel();

        $bus = new BusDeRequetes(static::getContainer()->get('query.bus'));

        $sessions = $bus->demander(new ListerSessions());

        self::assertIsArray($sessions);
        self::assertCount(5, $sessions);
        self::assertContainsOnlyInstancesOf(ResumeSession::class, $sessions);
    }
}
