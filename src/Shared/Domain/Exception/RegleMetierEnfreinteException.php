<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

/**
 * Exception métier signalant qu'une règle métier empêche l'opération demandée (409 côté API).
 */
interface RegleMetierEnfreinteException extends \Throwable
{
}
