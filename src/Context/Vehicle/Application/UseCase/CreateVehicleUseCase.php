<?php

namespace App\Context\Vehicle\Application\UseCase;

use App\Context\Vehicle\Application\DTO\CreateVehicleDTO;
use App\Context\Vehicle\Application\DTO\VehicleResponseDTO;
use App\Context\Vehicle\Domain\Entity\Vehicle;
use App\Context\Vehicle\Domain\Repository\VehicleRepositoryInterface;

class CreateVehicleUseCase
{
    public function __construct(
        private VehicleRepositoryInterface $vehicleRepository
    ) {
    }

    public function execute(CreateVehicleDTO $dto): VehicleResponseDTO
    {
        $vehicle = new Vehicle();
        $vehicle->setMake($dto->make);
        $vehicle->setModel($dto->model);
        $vehicle->setVersion($dto->version);
        $vehicle->setImage($dto->image);
        $vehicle->setKms($dto->kms);
        $vehicle->setPrice($dto->price);
        $vehicle->setYearModel($dto->yearModel);
        $vehicle->setYearFab($dto->yearFab);
        $vehicle->setColor($dto->color);
        $vehicle->setFipeCode($dto->fipeCode);
        $vehicle->setFuel($dto->fuel);

        $this->vehicleRepository->add($vehicle, true);

        return VehicleResponseDTO::fromEntity($vehicle);
    }
}
