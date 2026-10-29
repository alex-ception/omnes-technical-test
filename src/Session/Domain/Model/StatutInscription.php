<?php

declare(strict_types=1);

namespace App\Session\Domain\Model;

enum StatutInscription: string
{
    case CONFIRMEE = 'confirmee';
    case ANNULEE = 'annulee';
    case LISTE_ATTENTE = 'liste_attente';

    public function libelle(): string
    {
        return match ($this) {
            self::CONFIRMEE => 'Confirmée',
            self::ANNULEE => 'Annulée',
            self::LISTE_ATTENTE => "Liste d'attente",
        };
    }
}
