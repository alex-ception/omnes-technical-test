<?php

declare(strict_types=1);

namespace App\Session\Infrastructure\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Modèle de persistance Doctrine. Les règles métier vivent dans App\Session\Domain\Model\Stagiaire,
 * les mappers font la conversion.
 */
#[ORM\Entity]
class Stagiaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private string $prenom;

    #[ORM\Column(length: 80)]
    private string $nom;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    public function __construct(string $prenom, string $nom, string $email)
    {
        $this->prenom = $prenom;
        $this->nom = $nom;
        $this->email = $email;
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
}
