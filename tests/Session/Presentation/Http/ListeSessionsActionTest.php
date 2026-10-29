<?php

declare(strict_types=1);

namespace App\Tests\Session\Presentation\Http;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ListeSessionsActionTest extends WebTestCase
{
    public function testLaListeDesSessionsSAffiche(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/sessions');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Sessions de formation');
        self::assertCount(5, $crawler->filter('tbody tr[data-session-code]'));
    }

    public function testLaListeAfficheLesPlacesRestantes(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/sessions');

        // SYMF-ADV-01 : capacité 5, voir les fixtures pour le détail des inscriptions.
        $places = $crawler->filter('tr[data-session-code="SYMF-ADV-01"] [data-test="places-restantes"]')->text();

        self::assertSame('3', trim($places));
    }

    public function testLaListeSignaleLesSessionsCompletes(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/sessions');

        // PHP-MOD-01 : capacité 3, 3 inscriptions confirmées.
        self::assertSame('complet', $crawler->filter('tr[data-session-code="PHP-MOD-01"]')->attr('class'));
        // DOCK-DEV-01 : capacité 2, une seule inscription confirmée (les autres sont annulée ou en liste d'attente).
        self::assertSame('', $crawler->filter('tr[data-session-code="DOCK-DEV-01"]')->attr('class'));
    }
}
