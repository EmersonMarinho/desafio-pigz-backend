<?php

namespace App\Context\Fipe\Application\UseCase;

use App\Context\Fipe\Infrastructure\Repository\FipeRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteFipePriceUseCase
{
    public function __construct(
        private readonly FipeRepository $fipeRepository
    ) {
    }

    public function execute(int $id): void
    {
        $fipePrice = $this->fipeRepository->find($id);
        if (!$fipePrice) {
            throw new NotFoundHttpException('FIPE price not found');
        }

        $this->fipeRepository->remove($fipePrice);
    }
}
