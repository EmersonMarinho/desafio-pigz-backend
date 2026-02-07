<?php

namespace App\Context\Fipe\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateFipePriceDTO
{
    public function __construct(
        #[Assert\Length(max: 100)]
        public readonly ?string $brand = null,

        #[Assert\Length(max: 255)]
        public readonly ?string $model = null,

        #[Assert\Positive]
        #[Assert\Range(min: 1900, max: 2100)]
        public readonly ?int $year = null,

        #[Assert\Choice(choices: ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'GNV', 'Híbrido', 'Elétrico'])]
        public readonly ?string $fuel = null,

        #[Assert\Positive]
        public readonly ?float $price = null,

        #[Assert\Regex(pattern: '/^\d{2}\/\d{4}$/', message: 'Reference month must be in format MM/YYYY')]
        public readonly ?string $referenceMonth = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            brand: $data['brand'] ?? null,
            model: $data['model'] ?? null,
            year: isset($data['year']) ? (int) $data['year'] : null,
            fuel: $data['fuel'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            referenceMonth: $data['referenceMonth'] ?? null,
        );
    }
}
