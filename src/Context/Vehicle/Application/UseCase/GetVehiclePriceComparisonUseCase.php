<?php

namespace App\Context\Vehicle\Application\UseCase;

use App\Context\Vehicle\Application\DTO\PriceComparisonDTO;
use App\Context\Vehicle\Domain\Service\PriceComparisonService;
use App\Context\Vehicle\Infrastructure\Repository\VehicleRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetVehiclePriceComparisonUseCase
{
    public function __construct(
        private readonly VehicleRepository $vehicleRepository,
        private readonly PriceComparisonService $priceComparisonService
    ) {
    }

    public function execute(int $vehicleId): PriceComparisonDTO
    {
        $vehicle = $this->vehicleRepository->find($vehicleId);

        if (!$vehicle) {
            throw new NotFoundHttpException('Vehicle not found');
        }

        return $this->priceComparisonService->compare($vehicle);
    }
}
