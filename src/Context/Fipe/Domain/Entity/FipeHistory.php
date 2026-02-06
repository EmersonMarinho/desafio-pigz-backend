<?php

namespace App\Context\Fipe\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FipeHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 50)]
    private ?string $monthRef = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
