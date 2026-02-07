<?php

namespace App\Context\Fipe\Domain\Repository;

use App\Context\Fipe\Domain\Entity\FipePrice;

interface FipeRepositoryInterface
{
    public function save(FipePrice $fipePrice, bool $flush = false): void;
    public function remove(FipePrice $fipePrice, bool $flush = false): void;
    public function find($id, $lockMode = null, $lockVersion = null): ?FipePrice;
    public function findByVehicleCode(string $vehicleCode): ?FipePrice;
    public function findWithFilters(
        ?string $brand = null,
        ?int $year = null,
        ?string $fuel = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array;
}
