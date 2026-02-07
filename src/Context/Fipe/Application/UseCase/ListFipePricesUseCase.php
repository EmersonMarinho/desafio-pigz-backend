<?php

namespace App\Context\Fipe\Application\UseCase;

use App\Context\Fipe\Application\DTO\FipePriceResponseDTO;
use App\Context\Fipe\Infrastructure\Repository\FipeRepository;

class ListFipePricesUseCase
{
    public function __construct(
        private readonly FipeRepository $fipeRepository
    ) {
    }

    /**
     * @return FipePriceResponseDTO[]
     */
    public function execute(
        ?string $brand = null,
        ?int $year = null,
        ?string $fuel = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array {
        $fipePrices = $this->fipeRepository->findWithFilters(
            brand: $brand,
            year: $year,
            fuel: $fuel,
            minPrice: $minPrice,
            maxPrice: $maxPrice
        );

        return array_map(
            fn($fipePrice) => FipePriceResponseDTO::fromEntity($fipePrice),
            $fipePrices
        );
    }
}
