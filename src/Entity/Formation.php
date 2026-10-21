<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private string $code;

    #[ORM\Column(length: 150)]
    private string $titre;

    /** @var Collection<int, Session> */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'formation')]
    private Collection $sessions;

    public function __construct(string $code, string $titre)
    {
        $this->code = $code;
        $this->titre = $titre;
        $this->sessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    /** @return Collection<int, Session> */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }
}
