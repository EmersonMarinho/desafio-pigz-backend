<?php

namespace App\Context\Vehicle\Application\UseCase;

use App\Context\User\Domain\Entity\User;
use App\Context\Vehicle\Application\DTO\CreateVehicleDTO;
use App\Context\Vehicle\Application\DTO\VehicleResponseDTO;
use App\Context\Vehicle\Domain\Entity\Vehicle;
use App\Context\Vehicle\Domain\Repository\VehicleRepositoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateVehicleUseCase
{
    public function __construct(
        private readonly VehicleRepositoryInterface $vehicleRepository,
        private readonly Security $security,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function execute(CreateVehicleDTO $dto): VehicleResponseDTO
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new BadRequestHttpException(implode(', ', $errorMessages));
        }

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

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
        $vehicle->setUser($currentUser);

        $this->vehicleRepository->add($vehicle, true);

        return VehicleResponseDTO::fromEntity($vehicle);
    }
}
