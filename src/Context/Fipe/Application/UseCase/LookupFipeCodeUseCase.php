<?php

namespace App\Context\Fipe\Application\UseCase;

use App\Context\Fipe\Application\DTO\FipeVehicleDTO;
use App\Context\Fipe\Domain\Service\FipeApiClientInterface;
use Psr\Log\LoggerInterface;

class LookupFipeCodeUseCase
{
    public function __construct(
        private readonly FipeApiClientInterface $fipeApiClient,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Lookup FIPE code from Brasil API
     * This is a helper for admins to auto-fill FIPE form data
     * Does NOT save to database automatically
     * 
     * @param string $fipeCode FIPE code to lookup
     * @return FipeVehicleDTO|null Vehicle data from API or null if not found
     */
    public function execute(string $fipeCode): ?FipeVehicleDTO
    {
        $this->logger->info('Looking up FIPE code', ['fipe_code' => $fipeCode]);

        try {
            $vehicleData = $this->fipeApiClient->lookupByCode($fipeCode);

            if (!$vehicleData) {
                $this->logger->warning('FIPE code not found in Brasil API', [
                    'fipe_code' => $fipeCode
                ]);
                return null;
            }

            $this->logger->info('FIPE code found in Brasil API', [
                'fipe_code' => $fipeCode,
                'brand' => $vehicleData->brand,
                'model' => $vehicleData->model
            ]);

            return $vehicleData;

        } catch (\RuntimeException $e) {
            $this->logger->error('Failed to lookup FIPE code', [
                'fipe_code' => $fipeCode,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
