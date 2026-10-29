<?php

declare(strict_types=1);

namespace App\Tests\Session\Infrastructure\Http\Api;

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

    public function testUneSessionInconnueEstRefusee(): void
    {
        $client = static::createClient();

        $this->inscrire($client, 'INCONNUE-01', 'alice.martin@exemple.fr');

        self::assertResponseStatusCodeSame(404);
        self::assertSame(['erreur' => 'Session INCONNUE-01 inconnue.'], $this->reponseJson($client));
    }

    public function testLEmailEstObligatoire(): void
    {
        $client = static::createClient();

        $this->inscrire($client, 'GIT-QUO-01', '');

        self::assertResponseStatusCodeSame(400);
        self::assertSame(['erreur' => 'Le champ "email" est obligatoire.'], $this->reponseJson($client));
    }

    public function testLeMessageDuRefusEstRenvoye(): void
    {
        $client = static::createClient();

        // Alice est déjà inscrite (confirmée) à SYMF-ADV-01 dans les fixtures.
        $this->inscrire($client, 'SYMF-ADV-01', 'alice.martin@exemple.fr');

        self::assertResponseStatusCodeSame(409);
        self::assertSame(['erreur' => 'alice.martin@exemple.fr est déjà inscrit(e) à la session SYMF-ADV-01.'], $this->reponseJson($client));
    }

    /**
     * @return array<string, mixed>
     */
    private function reponseJson(KernelBrowser $client): array
    {
        return json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
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
