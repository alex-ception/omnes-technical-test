<?php

declare(strict_types=1);

namespace App\Session\Domain\Model;

use App\Session\Domain\Exception\InscriptionImpossibleException;

final class Session
{
    /** Nombre maximal de personnes en liste d'attente quand la session est complète. */
    public const int LIMITE_LISTE_ATTENTE = 2;

    /** @var list<Inscription> */
    private array $inscriptions = [];

    public function __construct(
        private readonly Formation $formation,
        private readonly string $code,
        private readonly \DateTimeImmutable $dateDebut,
        private readonly \DateTimeImmutable $dateFin,
        private readonly int $capacite,
        private readonly ?int $id = null,
    ) {
        if ($capacite < 1) {
            throw new \InvalidArgumentException('La capacité d\'une session doit être d\'au moins 1.');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getFormation(): Formation
    {
        return $this->formation;
    }

    public function getDateDebut(): \DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function getDateFin(): \DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function getCapacite(): int
    {
        return $this->capacite;
    }

    /** @return list<Inscription> */
    public function getInscriptions(): array
    {
        return $this->inscriptions;
    }

    /**
     * Inscrit un stagiaire : confirmé s'il reste une place, en liste d'attente sinon.
     *
     * @throws InscriptionImpossibleException si le stagiaire est déjà inscrit ou si la liste d'attente est pleine
     */
    public function inscrire(Stagiaire $stagiaire): Inscription
    {
        if ($this->aUneInscriptionActive($stagiaire)) {
            throw new InscriptionImpossibleException(sprintf(
                '%s est déjà inscrit(e) à la session %s.',
                $stagiaire->getEmail(),
                $this->code,
            ));
        }

        if ($this->estComplete() && $this->estListeAttentePleine()) {
            throw new InscriptionImpossibleException(sprintf(
                'La session %s est complète et sa liste d\'attente est pleine.',
                $this->code,
            ));
        }

        $statut = $this->estComplete() ? StatutInscription::LISTE_ATTENTE : StatutInscription::CONFIRMEE;

        return new Inscription($this, $stagiaire, $statut);
    }

    /**
     * Seules les inscriptions confirmées consomment une place : une inscription annulée
     * libère la sienne et une inscription en liste d'attente n'en consomme pas.
     */
    public function getPlacesRestantes(): int
    {
        return $this->capacite - $this->compterInscriptions(StatutInscription::CONFIRMEE);
    }

    public function estComplete(): bool
    {
        return $this->getPlacesRestantes() <= 0;
    }

    public function estListeAttentePleine(): bool
    {
        return $this->compterInscriptions(StatutInscription::LISTE_ATTENTE) >= self::LIMITE_LISTE_ATTENTE;
    }

    /** @internal Appelée par le constructeur d'Inscription pour garder la session cohérente. */
    public function ajouterInscription(Inscription $inscription): void
    {
        if (!\in_array($inscription, $this->inscriptions, true)) {
            $this->inscriptions[] = $inscription;
        }
    }

    /**
     * Un stagiaire ne peut avoir qu'une seule inscription non annulée par session.
     */
    private function aUneInscriptionActive(Stagiaire $stagiaire): bool
    {
        foreach ($this->inscriptions as $inscription) {
            if (StatutInscription::ANNULEE !== $inscription->getStatut() && $inscription->getStagiaire()->estLeMemeQue($stagiaire)) {
                return true;
            }
        }

        return false;
    }

    private function compterInscriptions(StatutInscription $statut): int
    {
        return \count(array_filter(
            $this->inscriptions,
            static fn (Inscription $inscription): bool => $statut === $inscription->getStatut(),
        ));
    }
}
