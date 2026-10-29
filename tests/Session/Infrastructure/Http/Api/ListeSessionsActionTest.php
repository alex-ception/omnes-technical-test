<?php

declare(strict_types=1);

namespace App\Tests\Session\Infrastructure\Http\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ListeSessionsActionTest extends WebTestCase
{
    public function testLApiListeLesSessions(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/sessions');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $sessions = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertCount(5, $sessions);
        foreach ($sessions as $session) {
            self::assertArrayHasKey('code', $session);
            self::assertArrayHasKey('formation', $session);
            self::assertArrayHasKey('capacite', $session);
            self::assertArrayHasKey('places_restantes', $session);
        }
    }

    public function testLApiRenvoieLesPlacesRestantes(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/sessions');

        $sessions = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        $placesParCode = array_column($sessions, 'places_restantes', 'code');

        // SYMF-ADV-01 : capacité 5, 2 inscriptions confirmées et 2 annulées.
        self::assertSame(3, $placesParCode['SYMF-ADV-01']);
        // DOCK-DEV-01 : capacité 2, 1 confirmée, 1 annulée et 1 en liste d'attente.
        self::assertSame(1, $placesParCode['DOCK-DEV-01']);
    }
}
