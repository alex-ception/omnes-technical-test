<?php

declare(strict_types=1);

namespace App\Session\Domain\Exception;

final class SessionIntrouvableException extends \DomainException
{
    public function __construct(string $code)
    {
        parent::__construct(sprintf('Session %s inconnue.', $code));
    }
}
