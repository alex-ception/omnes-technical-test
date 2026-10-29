<?php

declare(strict_types=1);

namespace App\Session\Domain\Model;

final readonly class Stagiaire
{
    public function __construct(
        private string $prenom,
        private string $nom,
        private string $email,
        private ?int $id = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getNomComplet(): string
    {
        return $this->prenom.' '.$this->nom;
    }

    /**
     * Deux stagiaires sont la même personne s'ils ont le même email (unique).
     */
    public function estLeMemeQue(self $autre): bool
    {
        return $this->email === $autre->email;
    }
}
