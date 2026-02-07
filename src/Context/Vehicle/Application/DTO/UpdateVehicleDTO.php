<?php

namespace App\Context\Vehicle\Application\DTO;

use Symfony\Component\Validator\Defaults\Range;
use Symfony\Component\Validator\Constraint;

class UpdateVehicleDTO
{
    public function __construct(
        public ?string $make = null,
        public ?string $model = null,
        public ?int $yearFab = null,
        public ?int $yearModel = null,
        public ?string $color = null,
        public ?float $price = null,
        public ?int $km = null,
        public ?string $fuel = null,
        public ?string $fipeCode = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            make: $data['make'] ?? null,
            model: $data['model'] ?? null,
            yearFab: isset($data['yearFab']) ? (int) $data['yearFab'] : null,
            yearModel: isset($data['yearModel']) ? (int) $data['yearModel'] : null,
            color: $data['color'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            km: isset($data['km']) ? (int) $data['km'] : null,
            fuel: $data['fuel'] ?? null,
            fipeCode: $data['fipeCode'] ?? null
        );
    }
}
