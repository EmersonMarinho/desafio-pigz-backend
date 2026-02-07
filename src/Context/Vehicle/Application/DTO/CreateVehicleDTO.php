<?php

namespace App\Context\Vehicle\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateVehicleDTO
{
    #[Assert\NotBlank]
    public string $make;

    #[Assert\NotBlank]
    public string $model;

    #[Assert\NotBlank]
    public string $version;

    public ?string $image = null;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public int $kms;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public float $price;

    #[Assert\NotBlank]
    public int $yearModel;

    #[Assert\NotBlank]
    public int $yearFab;

    #[Assert\NotBlank]
    public string $color;

    public ?string $fipeCode = null;

    public ?string $fuel = null;
}
