<?php

namespace App\Context\Vehicle\Application\UseCase;

use App\Context\Vehicle\Application\DTO\VehicleResponseDTO;
use App\Context\Vehicle\Domain\Repository\VehicleRepositoryInterface;

class ListVehiclesUseCase
{
    public function __construct(
        private readonly VehicleRepositoryInterface $vehicleRepository
    ) {
    }

    public function execute(
        ?string $make = null,
        ?string $model = null,
        ?int $year = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array {
        $vehicles = $this->vehicleRepository->findWithFilters(
            make: $make,
            model: $model,
            year: $year,
            minPrice: $minPrice,
            maxPrice: $maxPrice
        );

        return array_map(fn($vehicle) => VehicleResponseDTO::fromEntity($vehicle), $vehicles);
    }
}
