<?php

namespace App\Context\Fipe\Domain\Service;

use App\Context\Fipe\Application\DTO\FipeVehicleDTO;

interface FipeApiClientInterface
{
    /**
     * Lookup vehicle by FIPE code from external API
     * 
     * @param string $fipeCode FIPE vehicle code (e.g., "001004-1")
     * @return FipeVehicleDTO|null Vehicle data or null if not found
     * @throws \RuntimeException If API is unavailable
     */
    public function lookupByCode(string $fipeCode): ?FipeVehicleDTO;

    /**
     * Search vehicles by brand, model and year
     * 
     * @param string|null $brand Brand name
     * @param string|null $model Model name
     * @param int|null $year Model year
     * @return FipeVehicleDTO[] List of matching vehicles
     * @throws \RuntimeException If API is unavailable
     */
    public function search(?string $brand = null, ?string $model = null, ?int $year = null): array;
}
