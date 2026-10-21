<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SessionApiControllerTest extends WebTestCase
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

    public function testUnStagiaireSInscritViaLApi(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/sessions/GIT-QUO-01/inscriptions',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'alice.martin@exemple.fr'], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);

        $inscription = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertSame('GIT-QUO-01', $inscription['session']);
        self::assertSame('confirmee', $inscription['statut']);
    }

    public function testUnStagiaireInconnuEstRefuse(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/sessions/GIT-QUO-01/inscriptions',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'inconnu@exemple.fr'], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(404);
    }
}
