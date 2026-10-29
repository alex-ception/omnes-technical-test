<?php

declare(strict_types=1);

namespace App\Session\Domain\Repository;

use App\Session\Domain\Model\Stagiaire;

interface StagiaireRepository
{
    public function trouverParEmail(string $email): ?Stagiaire;
}
