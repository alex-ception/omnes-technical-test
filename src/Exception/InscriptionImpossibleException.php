<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Levée quand une règle métier empêche d'inscrire un stagiaire à une session.
 */
final class InscriptionImpossibleException extends \DomainException
{
}
