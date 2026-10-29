<?php

declare(strict_types=1);

namespace App\Session\Domain\Exception;

use App\Shared\Domain\Exception\RessourceIntrouvableException;

final class StagiaireIntrouvableException extends \DomainException implements RessourceIntrouvableException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Stagiaire %s inconnu.', $email));
    }
}
