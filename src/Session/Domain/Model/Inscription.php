<?php

declare(strict_types=1);

namespace App\Session\Domain\Model;

final class Inscription
{
    private readonly \DateTimeImmutable $dateInscription;

    public function __construct(
        private readonly Session $session,
        private readonly Stagiaire $stagiaire,
        private StatutInscription $statut = StatutInscription::CONFIRMEE,
        ?\DateTimeImmutable $dateInscription = null,
        private readonly ?int $id = null,
    ) {
        $this->dateInscription = $dateInscription ?? new \DateTimeImmutable();

        $session->ajouterInscription($this);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getStagiaire(): Stagiaire
    {
        return $this->stagiaire;
    }

    public function getStatut(): StatutInscription
    {
        return $this->statut;
    }

    public function getDateInscription(): \DateTimeImmutable
    {
        return $this->dateInscription;
    }

    public function annuler(): void
    {
        $this->statut = StatutInscription::ANNULEE;
    }
}
