<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private Session $session;

    #[ORM\ManyToOne(targetEntity: Stagiaire::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Stagiaire $stagiaire;

    #[ORM\Column(enumType: StatutInscription::class)]
    private StatutInscription $statut;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $dateInscription;

    public function __construct(
        Session $session,
        Stagiaire $stagiaire,
        StatutInscription $statut = StatutInscription::CONFIRMEE,
        ?\DateTimeImmutable $dateInscription = null,
    ) {
        $this->session = $session;
        $this->stagiaire = $stagiaire;
        $this->statut = $statut;
        $this->dateInscription = $dateInscription ?? new \DateTimeImmutable();

        $session->addInscription($this);
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
