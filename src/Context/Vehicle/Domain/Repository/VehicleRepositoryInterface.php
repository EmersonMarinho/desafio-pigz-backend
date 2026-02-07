<?php

namespace App\Context\Vehicle\Domain\Repository;

use App\Context\Vehicle\Domain\Entity\Vehicle;

interface VehicleRepositoryInterface
{
    public function add(Vehicle $vehicle, bool $flush = false): void;
    public function remove(Vehicle $vehicle, bool $flush = false): void;
    public function find($id, $lockMode = null, $lockVersion = null): ?Vehicle;
    public function findAll(): array;
    public function findWithFilters(
        ?string $make = null,
        ?string $model = null,
        ?int $year = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array;
}
