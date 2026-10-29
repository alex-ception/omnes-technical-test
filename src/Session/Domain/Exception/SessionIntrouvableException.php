<?php

declare(strict_types=1);

namespace App\Session\Domain\Exception;

use App\Shared\Domain\Exception\RessourceIntrouvableException;

final class SessionIntrouvableException extends \DomainException implements RessourceIntrouvableException
{
    public function __construct(string $code)
    {
        parent::__construct(sprintf('Session %s inconnue.', $code));
    }
}
