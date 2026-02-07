<?php

namespace App\Context\Vehicle\Application\UseCase;

use App\Context\Vehicle\Domain\Repository\VehicleRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteVehicleUseCase
{
    public function __construct(
        private readonly VehicleRepositoryInterface $vehicleRepository
    ) {
    }

    public function execute(int $id): void
    {
        $vehicle = $this->vehicleRepository->find($id);

        if (!$vehicle) {
            throw new NotFoundHttpException('Vehicle not found');
        }

        $this->vehicleRepository->remove($vehicle, true);
    }
}
