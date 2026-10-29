<?php

declare(strict_types=1);

namespace App\Tests\Shared\Application;

use App\Shared\Application\BusDeRequetes;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

final class BusDeRequetesTest extends TestCase
{
    public function testLeBusRenvoieLeResultatDuHandler(): void
    {
        $requete = new \stdClass();
        $bus = $this->unBus(static fn (\stdClass $recue): array => ['réponse', $recue]);

        self::assertSame(['réponse', $requete], $bus->demander($requete));
    }

    public function testUneRequeteSansHandlerEstRefusee(): void
    {
        $bus = new BusDeRequetes(new MessageBus([new HandleMessageMiddleware(new HandlersLocator([]))]));

        $this->expectException(NoHandlerForMessageException::class);

        $bus->demander(new \stdClass());
    }

    private function unBus(callable $handler): BusDeRequetes
    {
        return new BusDeRequetes(new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([\stdClass::class => [$handler]])),
        ]));
    }
}
