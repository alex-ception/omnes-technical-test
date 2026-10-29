<?php

declare(strict_types=1);

namespace App\Tests\Session\Infrastructure\Http;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AccueilActionTest extends WebTestCase
{
    public function testLAccueilRedirigeVersLaListeDesSessions(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        self::assertResponseRedirects('/sessions');
    }
}
