<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

/**
 * Exception métier signalant qu'une ressource demandée n'existe pas (404 côté API).
 */
interface RessourceIntrouvableException extends \Throwable
{
}
