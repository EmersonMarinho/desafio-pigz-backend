<?php

namespace App\Context\Fipe\Application\UseCase;

use App\Context\Fipe\Application\DTO\FipePriceResponseDTO;
use App\Context\Fipe\Domain\Entity\FipePrice;
use App\Context\Fipe\Domain\Repository\FipeRepositoryInterface;
use App\Context\Fipe\Infrastructure\Service\FipeApiService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchFipeByCodeUseCase
{
    public function __construct(
        private readonly FipeApiService $fipeApiService,
        private readonly FipeRepositoryInterface $fipeRepository
    ) {
    }

    public function execute(string $fipeCode, bool $saveToCache = true): FipePriceResponseDTO
    {
        $cachedPrice = $this->fipeRepository->findByVehicleCode($fipeCode);
        if ($cachedPrice) {
            return FipePriceResponseDTO::fromEntity($cachedPrice);
        }

        $apiData = $this->fipeApiService->searchByFipeCode($fipeCode);

        if (!$apiData) {
            throw new NotFoundHttpException("FIPE code {$fipeCode} not found");
        }

        $fipePrice = new FipePrice();
        $fipePrice->setVehicleCode($apiData['CodigoFipe'] ?? $fipeCode);
        $fipePrice->setBrand($apiData['Marca'] ?? 'Unknown');
        $fipePrice->setModel($apiData['Modelo'] ?? 'Unknown');
        $fipePrice->setYear((int) ($apiData['AnoModelo'] ?? 0));
        $fipePrice->setFuel($apiData['Combustivel'] ?? 'Unknown');

        $priceFloat = $this->fipeApiService->parsePriceToFloat($apiData['Valor'] ?? 'R$ 0,00');
        $fipePrice->setPrice((string) $priceFloat);

        $fipePrice->setReferenceMonth($apiData['MesReferencia'] ?? $this->fipeApiService->getReferenceMonth());

        if ($saveToCache) {
            $this->fipeRepository->save($fipePrice, true);
        }

        return FipePriceResponseDTO::fromEntity($fipePrice);
    }
}
