<?php

namespace App\Context\Vehicle\Application\DTO;

use App\Context\Vehicle\Domain\Entity\Vehicle;

class VehicleResponseDTO
{
    public int $id;
    public string $make;
    public string $model;
    public string $version;
    public ?string $image;
    public int $kms;
    public float $price;
    public int $yearModel;
    public int $yearFab;
    public string $color;
    public ?string $fipeCode;
    public ?string $fuel;

    public function __construct(Vehicle $vehicle)
    {
        $this->id = $vehicle->getId();
        $this->make = $vehicle->getMake();
        $this->model = $vehicle->getModel();
        $this->version = $vehicle->getVersion();
        $this->image = $vehicle->getImage();
        $this->kms = $vehicle->getKms();
        $this->price = $vehicle->getPrice();
        $this->yearModel = $vehicle->getYearModel();
        $this->yearFab = $vehicle->getYearFab();
        $this->color = $vehicle->getColor();
        $this->fipeCode = $vehicle->getFipeCode();
        $this->fuel = $vehicle->getFuel();
    }
}
