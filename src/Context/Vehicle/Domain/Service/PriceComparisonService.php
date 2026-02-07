<?php

namespace App\Context\Vehicle\Domain\Service;

use App\Context\Fipe\Infrastructure\Repository\FipeRepository;
use App\Context\Vehicle\Application\DTO\PriceComparisonDTO;
use App\Context\Vehicle\Domain\Entity\Vehicle;

class PriceComparisonService
{
    public function __construct(
        private readonly FipeRepository $fipeRepository
    ) {
    }

    public function compare(Vehicle $vehicle): PriceComparisonDTO
    {
        $fipeCode = $vehicle->getFipeCode();

        if (!$fipeCode) {
            return PriceComparisonDTO::create(
                vehicleId: $vehicle->getId(),
                vehiclePrice: $vehicle->getPrice(),
                fipePrice: null,
                fipeCode: null
            );
        }

        $fipePrice = $this->fipeRepository->findByVehicleCode($fipeCode);

        if (!$fipePrice) {
            return PriceComparisonDTO::create(
                vehicleId: $vehicle->getId(),
                vehiclePrice: $vehicle->getPrice(),
                fipePrice: null,
                fipeCode: $fipeCode
            );
        }

        return PriceComparisonDTO::create(
            vehicleId: $vehicle->getId(),
            vehiclePrice: $vehicle->getPrice(),
            fipePrice: (float) $fipePrice->getPrice(),
            fipeCode: $fipeCode,
            referenceMonth: $fipePrice->getReferenceMonth()
        );
    }
}
