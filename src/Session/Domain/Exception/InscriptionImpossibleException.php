<?php

declare(strict_types=1);

namespace App\Session\Domain\Exception;

use App\Shared\Domain\Exception\RegleMetierEnfreinteException;

/**
 * Levée quand une règle métier empêche d'inscrire un stagiaire à une session.
 */
final class InscriptionImpossibleException extends \DomainException implements RegleMetierEnfreinteException
{
}
