<?php

namespace App\Context\Fipe\Application\DTO;

use App\Context\Fipe\Domain\Entity\FipePrice;

class FipePriceResponseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $vehicleCode,
        public readonly string $brand,
        public readonly string $model,
        public readonly int $year,
        public readonly string $fuel,
        public readonly float $price,
        public readonly string $referenceMonth,
        public readonly string $createdAt,
        public readonly ?string $updatedAt = null,
    ) {
    }

    public static function fromEntity(FipePrice $fipePrice): self
    {
        return new self(
            id: $fipePrice->getId(),
            vehicleCode: $fipePrice->getVehicleCode(),
            brand: $fipePrice->getBrand(),
            model: $fipePrice->getModel(),
            year: $fipePrice->getYear(),
            fuel: $fipePrice->getFuel(),
            price: (float) $fipePrice->getPrice(),
            referenceMonth: $fipePrice->getReferenceMonth(),
            createdAt: $fipePrice->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $fipePrice->getUpdatedAt()?->format('Y-m-d H:i:s'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'vehicleCode' => $this->vehicleCode,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'fuel' => $this->fuel,
            'price' => $this->price,
            'referenceMonth' => $this->referenceMonth,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
