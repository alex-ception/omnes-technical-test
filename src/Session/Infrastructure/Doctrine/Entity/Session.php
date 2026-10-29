<?php

declare(strict_types=1);

namespace App\Session\Infrastructure\Doctrine\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Modèle de persistance Doctrine. Les règles métier vivent dans App\Session\Domain\Model\Session,
 * les mappers font la conversion.
 */
#[ORM\Entity]
#[ORM\Table(name: 'session_formation')]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, unique: true)]
    private string $code;

    #[ORM\ManyToOne(targetEntity: Formation::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private Formation $formation;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $dateDebut;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $dateFin;

    #[ORM\Column]
    private int $capacite;

    /** @var Collection<int, Inscription> */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'session')]
    private Collection $inscriptions;

    public function __construct(
        Formation $formation,
        string $code,
        \DateTimeImmutable $dateDebut,
        \DateTimeImmutable $dateFin,
        int $capacite,
    ) {
        if ($capacite < 1) {
            throw new \InvalidArgumentException('La capacité d\'une session doit être d\'au moins 1.');
        }

        $this->formation = $formation;
        $this->code = $code;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->capacite = $capacite;
        $this->inscriptions = new ArrayCollection();
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

    /** @return Collection<int, Inscription> */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    /** @internal Appelée par le constructeur d'Inscription pour garder la collection cohérente. */
    public function addInscription(Inscription $inscription): void
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
        }
    }
}
