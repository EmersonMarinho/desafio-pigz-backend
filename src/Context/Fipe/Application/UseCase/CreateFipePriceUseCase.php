<?php

namespace App\Context\Fipe\Application\UseCase;

use App\Context\Fipe\Application\DTO\CreateFipePriceDTO;
use App\Context\Fipe\Application\DTO\FipePriceResponseDTO;
use App\Context\Fipe\Domain\Entity\FipePrice;
use App\Context\Fipe\Domain\Repository\FipeRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateFipePriceUseCase
{
    public function __construct(
        private readonly FipeRepositoryInterface $fipeRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function execute(CreateFipePriceDTO $dto): FipePriceResponseDTO
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new BadRequestHttpException(implode(', ', $errorMessages));
        }

        $existing = $this->fipeRepository->findByVehicleCode($dto->vehicleCode);
        if ($existing) {
            throw new BadRequestHttpException('Vehicle code already exists in FIPE database');
        }

        $fipePrice = new FipePrice();
        $fipePrice->setVehicleCode($dto->vehicleCode);
        $fipePrice->setBrand($dto->brand);
        $fipePrice->setModel($dto->model);
        $fipePrice->setYear($dto->year);
        $fipePrice->setFuel($dto->fuel);
        $fipePrice->setPrice((string) $dto->price);
        $fipePrice->setReferenceMonth($dto->referenceMonth);

        $this->fipeRepository->save($fipePrice, true);

        return FipePriceResponseDTO::fromEntity($fipePrice);
    }
}
