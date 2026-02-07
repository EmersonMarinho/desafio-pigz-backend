<?php

namespace App\Context\Vehicle\Application\DTO;

use App\Context\Vehicle\Domain\Entity\Vehicle;

class VehicleResponseDTO
{
    public function __construct(
        public int $id,
        public string $make,
        public string $model,
        public string $version,
        public ?string $image,
        public int $kms,
        public float $price,
        public int $yearModel,
        public int $yearFab,
        public string $color,
        public ?string $fipeCode,
        public ?string $fuel
    ) {
    }

    public static function fromEntity(Vehicle $vehicle): self
    {
        return new self(
            id: $vehicle->getId(),
            make: $vehicle->getMake(),
            model: $vehicle->getModel(),
            version: $vehicle->getVersion(),
            image: $vehicle->getImage(),
            kms: $vehicle->getKms(),
            price: $vehicle->getPrice(),
            yearModel: $vehicle->getYearModel(),
            yearFab: $vehicle->getYearFab(),
            color: $vehicle->getColor(),
            fipeCode: $vehicle->getFipeCode(),
            fuel: $vehicle->getFuel()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'make' => $this->make,
            'model' => $this->model,
            'version' => $this->version,
            'image' => $this->image,
            'kms' => $this->kms,
            'price' => $this->price,
            'yearModel' => $this->yearModel,
            'yearFab' => $this->yearFab,
            'color' => $this->color,
            'fipeCode' => $this->fipeCode,
            'fuel' => $this->fuel
        ];
    }
}
