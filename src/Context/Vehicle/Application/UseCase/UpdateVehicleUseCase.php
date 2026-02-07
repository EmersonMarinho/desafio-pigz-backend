<?php

namespace App\Context\Vehicle\Application\UseCase;

use App\Context\Vehicle\Application\DTO\UpdateVehicleDTO;
use App\Context\Vehicle\Application\DTO\VehicleResponseDTO;
use App\Context\Vehicle\Domain\Repository\VehicleRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateVehicleUseCase
{
    public function __construct(
        private readonly VehicleRepositoryInterface $vehicleRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function execute(int $id, UpdateVehicleDTO $dto): VehicleResponseDTO
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new BadRequestHttpException(implode(', ', $errorMessages));
        }

        $vehicle = $this->vehicleRepository->find($id);

        if (!$vehicle) {
            throw new NotFoundHttpException('Vehicle not found');
        }

        if ($dto->make !== null)
            $vehicle->setMake($dto->make);
        if ($dto->model !== null)
            $vehicle->setModel($dto->model);
        if ($dto->yearFab !== null)
            $vehicle->setYearFab($dto->yearFab);
        if ($dto->yearModel !== null)
            $vehicle->setYearModel($dto->yearModel);
        if ($dto->color !== null)
            $vehicle->setColor($dto->color);
        if ($dto->price !== null)
            $vehicle->setPrice($dto->price);
        if ($dto->km !== null)
            $vehicle->setKms($dto->km);
        if ($dto->fuel !== null)
            $vehicle->setFuel($dto->fuel);
        if ($dto->fipeCode !== null)
            $vehicle->setFipeCode($dto->fipeCode);

        $this->vehicleRepository->add($vehicle, true);

        return VehicleResponseDTO::fromEntity($vehicle);
    }
}
