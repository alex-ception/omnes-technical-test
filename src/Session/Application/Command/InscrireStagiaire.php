<?php

declare(strict_types=1);

namespace App\Session\Application\Command;

/**
 * Inscrit un stagiaire à une session : confirmée s'il reste une place, en liste d'attente sinon.
 */
final readonly class InscrireStagiaire
{
    public function __construct(
        public string $codeSession,
        public string $emailStagiaire,
    ) {
    }
}
