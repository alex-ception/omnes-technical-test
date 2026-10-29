<?php

declare(strict_types=1);

namespace App\Tests\Session\Presentation\Http\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class InscrireStagiaireActionTest extends WebTestCase
{
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
