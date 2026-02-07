<?php

namespace App\Context\Fipe\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateFipePriceDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Vehicle code is required')]
        #[Assert\Length(max: 50)]
        public readonly string $vehicleCode,

        #[Assert\NotBlank(message: 'Brand is required')]
        #[Assert\Length(max: 100)]
        public readonly string $brand,

        #[Assert\NotBlank(message: 'Model is required')]
        #[Assert\Length(max: 255)]
        public readonly string $model,

        #[Assert\NotBlank(message: 'Year is required')]
        #[Assert\Positive]
        #[Assert\Range(min: 1900, max: 2100)]
        public readonly int $year,

        #[Assert\NotBlank(message: 'Fuel type is required')]
        #[Assert\Choice(choices: ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'GNV', 'Híbrido', 'Elétrico'])]
        public readonly string $fuel,

        #[Assert\NotBlank(message: 'Price is required')]
        #[Assert\Positive]
        public readonly float $price,

        #[Assert\NotBlank(message: 'Reference month is required')]
        #[Assert\Regex(pattern: '/^\d{2}\/\d{4}$/', message: 'Reference month must be in format MM/YYYY')]
        public readonly string $referenceMonth,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            vehicleCode: $data['vehicleCode'] ?? '',
            brand: $data['brand'] ?? '',
            model: $data['model'] ?? '',
            year: (int) ($data['year'] ?? 0),
            fuel: $data['fuel'] ?? '',
            price: (float) ($data['price'] ?? 0),
            referenceMonth: $data['referenceMonth'] ?? '',
        );
    }
}
