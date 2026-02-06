<?php

namespace App\Context\Vehicle\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $make = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\Column(length: 255)]
    private ?string $version = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $kms = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private ?int $yearModel = null;

    #[ORM\Column]
    private ?int $yearFab = null;

    #[ORM\Column(length: 255)]
    private ?string $color = null;

    // Getters and Setters would go here
    public function getId(): ?int
    {
        return $this->id;
    }
}
