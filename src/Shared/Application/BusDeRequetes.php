<?php

declare(strict_types=1);

namespace App\Shared\Application;

use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Envoie une requête sur query.bus et renvoie directement le résultat de son handler.
 */
final class BusDeRequetes
{
    use HandleTrait;

    public function __construct(MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    public function demander(object $requete): mixed
    {
        return $this->handle($requete);
    }
}
