<?php

namespace App\Context\Vehicle\Domain\Service;

use App\Context\Fipe\Domain\Service\FipeApiClientInterface;
use App\Context\Fipe\Infrastructure\Repository\FipeRepository;
use App\Context\Vehicle\Application\DTO\PriceComparisonDTO;
use App\Context\Vehicle\Domain\Entity\Vehicle;
use Psr\Log\LoggerInterface;

class PriceComparisonService
{
    public function __construct(
        private readonly FipeRepository $fipeRepository,
        private readonly FipeApiClientInterface $fipeApiClient,
        private readonly LoggerInterface $logger
    ) {
    }

    public function compare(Vehicle $vehicle): PriceComparisonDTO
    {
        $fipeCode = $vehicle->getFipeCode();

        if (!$fipeCode) {
            return PriceComparisonDTO::create(
                vehicleId: $vehicle->getId(),
                vehiclePrice: $vehicle->getPrice(),
                fipePrice: null,
                fipeCode: null
            );
        }

        // HYBRID APPROACH: Try local DB first, then fallback to API

        // 1. Check local database (manual admin entries)
        $fipePrice = $this->fipeRepository->findByVehicleCode($fipeCode);

        if ($fipePrice) {
            $this->logger->info('FIPE price found in local database', [
                'vehicle_id' => $vehicle->getId(),
                'fipe_code' => $fipeCode,
                'source' => 'local_database'
            ]);

            return PriceComparisonDTO::create(
                vehicleId: $vehicle->getId(),
                vehiclePrice: $vehicle->getPrice(),
                fipePrice: (float) $fipePrice->getPrice(),
                fipeCode: $fipeCode,
                referenceMonth: $fipePrice->getReferenceMonth(),
                source: 'local_database'
            );
        }

        // 2. Fallback to Brasil API
        try {
            $this->logger->info('FIPE price not found locally, trying Brasil API', [
                'vehicle_id' => $vehicle->getId(),
                'fipe_code' => $fipeCode
            ]);

            $apiData = $this->fipeApiClient->lookupByCode($fipeCode);

            if ($apiData) {
                $this->logger->info('FIPE price found in Brasil API', [
                    'vehicle_id' => $vehicle->getId(),
                    'fipe_code' => $fipeCode,
                    'source' => 'brasil_api'
                ]);

                return PriceComparisonDTO::create(
                    vehicleId: $vehicle->getId(),
                    vehiclePrice: $vehicle->getPrice(),
                    fipePrice: $apiData->price,
                    fipeCode: $fipeCode,
                    referenceMonth: $apiData->referenceMonth,
                    source: 'brasil_api'
                );
            }
        } catch (\RuntimeException $e) {
            $this->logger->warning('Failed to fetch from Brasil API, continuing without FIPE price', [
                'vehicle_id' => $vehicle->getId(),
                'fipe_code' => $fipeCode,
                'error' => $e->getMessage()
            ]);
        }

        // 3. Not found in either source
        $this->logger->warning('FIPE code not found in local DB or Brasil API', [
            'vehicle_id' => $vehicle->getId(),
            'fipe_code' => $fipeCode
        ]);

        return PriceComparisonDTO::create(
            vehicleId: $vehicle->getId(),
            vehiclePrice: $vehicle->getPrice(),
            fipePrice: null,
            fipeCode: $fipeCode
        );
    }
}
