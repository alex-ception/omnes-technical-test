<?php

declare(strict_types=1);

namespace App\Session\Domain\Exception;

final class StagiaireIntrouvableException extends \DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Stagiaire %s inconnu.', $email));
    }
}
