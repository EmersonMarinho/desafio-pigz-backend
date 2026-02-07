<?php

namespace App\Context\Vehicle\Domain\Repository;

use App\Context\Vehicle\Domain\Entity\Vehicle;
use Doctrine\DBAL\LockMode;

interface VehicleRepositoryInterface
{
    public function add(Vehicle $vehicle, bool $flush = false): void;
    public function remove(Vehicle $vehicle, bool $flush = false): void;

    /**
     * @return Vehicle|null
     */
    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?object;

    /**
     * @return Vehicle[]
     */
    public function findAll(): array;
    public function findWithFilters(
        ?string $make = null,
        ?string $model = null,
        ?int $year = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array;
}
