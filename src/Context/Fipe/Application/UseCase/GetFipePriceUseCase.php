<?php

namespace App\Context\Fipe\Application\UseCase;

use App\Context\Fipe\Application\DTO\FipePriceResponseDTO;
use App\Context\Fipe\Infrastructure\Repository\FipeRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetFipePriceUseCase
{
    public function __construct(
        private readonly FipeRepository $fipeRepository
    ) {
    }

    public function execute(int $id): FipePriceResponseDTO
    {
        $fipePrice = $this->fipeRepository->find($id);
        if (!$fipePrice) {
            throw new NotFoundHttpException('FIPE price not found');
        }

        return FipePriceResponseDTO::fromEntity($fipePrice);
    }
}
