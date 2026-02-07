<?php

namespace App\Context\Vehicle\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateVehicleDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public string $make,

        #[Assert\NotBlank]
        public string $model,

        #[Assert\NotBlank]
        public string $version,

        public ?string $image,

        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public int $kms,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public float $price,

        #[Assert\NotBlank]
        public int $yearModel,

        #[Assert\NotBlank]
        public int $yearFab,

        #[Assert\NotBlank]
        public string $color,

        public ?string $fipeCode,

        public ?string $fuel
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            make: $data['make'] ?? '',
            model: $data['model'] ?? '',
            version: $data['version'] ?? '',
            image: $data['image'] ?? null,
            kms: isset($data['kms']) ? (int) $data['kms'] : 0,
            price: isset($data['price']) ? (float) $data['price'] : 0.0,
            yearModel: isset($data['yearModel']) ? (int) $data['yearModel'] : 0,
            yearFab: isset($data['yearFab']) ? (int) $data['yearFab'] : 0,
            color: $data['color'] ?? '',
            fipeCode: $data['fipeCode'] ?? null,
            fuel: $data['fuel'] ?? null
        );
    }
}
