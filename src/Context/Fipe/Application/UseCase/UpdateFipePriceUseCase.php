<?php

namespace App\Context\Fipe\Application\UseCase;

use App\Context\Fipe\Application\DTO\UpdateFipePriceDTO;
use App\Context\Fipe\Application\DTO\FipePriceResponseDTO;
use App\Context\Fipe\Domain\Repository\FipeRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateFipePriceUseCase
{
    public function __construct(
        private readonly FipeRepositoryInterface $fipeRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function execute(int $id, UpdateFipePriceDTO $dto): FipePriceResponseDTO
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new BadRequestHttpException(implode(', ', $errorMessages));
        }

        $fipePrice = $this->fipeRepository->find($id);
        if (!$fipePrice) {
            throw new NotFoundHttpException('FIPE price not found');
        }

        if ($dto->brand !== null) {
            $fipePrice->setBrand($dto->brand);
        }
        if ($dto->model !== null) {
            $fipePrice->setModel($dto->model);
        }
        if ($dto->year !== null) {
            $fipePrice->setYear($dto->year);
        }
        if ($dto->fuel !== null) {
            $fipePrice->setFuel($dto->fuel);
        }
        if ($dto->price !== null) {
            $fipePrice->setPrice((string) $dto->price);
        }
        if ($dto->referenceMonth !== null) {
            $fipePrice->setReferenceMonth($dto->referenceMonth);
        }

        $fipePrice->setUpdatedAt(new \DateTime());

        $this->fipeRepository->save($fipePrice, true);

        return FipePriceResponseDTO::fromEntity($fipePrice);
    }
}
