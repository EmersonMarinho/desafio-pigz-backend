<?php

namespace App\Context\Vehicle\Application\UseCase;

use App\Context\Vehicle\Application\DTO\VehicleResponseDTO;
use App\Context\Vehicle\Domain\Repository\VehicleRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetVehicleUseCase
{
    public function __construct(
        private readonly VehicleRepositoryInterface $vehicleRepository
    ) {
    }

    public function execute(int $id): VehicleResponseDTO
    {
        $vehicle = $this->vehicleRepository->find($id);

        if (!$vehicle) {
            throw new NotFoundHttpException('Vehicle not found');
        }

        return VehicleResponseDTO::fromEntity($vehicle);
    }
}
