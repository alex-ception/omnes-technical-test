<?php

declare(strict_types=1);

namespace App\Session\Domain\Model;

final readonly class Formation
{
    public function __construct(
        private string $code,
        private string $titre,
        private ?int $id = null,
    ) {
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
}
