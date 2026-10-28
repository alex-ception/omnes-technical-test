<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
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

    public function testUnStagiaireEstInscritEnListeDAttenteSiLaSessionEstComplete(): void
    {
        $client = static::createClient();

        // PHP-MOD-01 : capacité 3, 3 inscriptions confirmées.
        $this->inscrire($client, 'PHP-MOD-01', 'alice.martin@exemple.fr');

        self::assertResponseStatusCodeSame(201);

        $inscription = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertSame('liste_attente', $inscription['statut']);
    }

    public function testLInscriptionEstRefuseeSiLaListeDAttenteEstPleine(): void
    {
        $client = static::createClient();

        $this->inscrire($client, 'PHP-MOD-01', 'alice.martin@exemple.fr');
        $this->inscrire($client, 'PHP-MOD-01', 'bruno.petit@exemple.fr');
        $this->inscrire($client, 'PHP-MOD-01', 'chloe.durand@exemple.fr');

        self::assertResponseStatusCodeSame(409);

        $reponse = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('erreur', $reponse);
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

    private function inscrire(KernelBrowser $client, string $codeSession, string $email): void
    {
        $client->request(
            'POST',
            sprintf('/api/sessions/%s/inscriptions', $codeSession),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => $email], \JSON_THROW_ON_ERROR),
        );
    }
}
