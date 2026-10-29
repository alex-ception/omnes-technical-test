<?php

declare(strict_types=1);

namespace App\Session\Domain\Repository;

use App\Session\Domain\Model\Session;

interface SessionRepository
{
    public function trouverParCode(string $code): ?Session;

    /**
     * Toutes les sessions avec leurs inscriptions, triées par date de début puis par code.
     *
     * @return list<Session>
     */
    public function listerParDateDeDebut(): array;

    /**
     * Persiste les nouvelles inscriptions de la session et leur attribue un identifiant.
     */
    public function enregistrer(Session $session): void;
}
