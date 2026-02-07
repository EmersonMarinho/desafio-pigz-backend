<?php

namespace App\Context\Vehicle\Infrastructure\Repository;

use App\Context\Vehicle\Domain\Entity\Vehicle;
use App\Context\Vehicle\Domain\Repository\VehicleRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository implements VehicleRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    public function add(Vehicle $vehicle, bool $flush = false): void
    {
        $this->getEntityManager()->persist($vehicle);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Vehicle $vehicle, bool $flush = false): void
    {
        $this->getEntityManager()->remove($vehicle);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWithFilters(
        ?string $make = null,
        ?string $model = null,
        ?int $year = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array {
        $qb = $this->createQueryBuilder('v');

        if ($make) {
            $qb->andWhere('v.make LIKE :make')
                ->setParameter('make', '%' . $make . '%');
        }

        if ($model) {
            $qb->andWhere('v.model LIKE :model')
                ->setParameter('model', '%' . $model . '%');
        }

        if ($year) {
            $qb->andWhere('v.yearModel = :year OR v.yearFab = :year')
                ->setParameter('year', $year);
        }

        if ($minPrice) {
            $qb->andWhere('v.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $qb->andWhere('v.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        return $qb->getQuery()->getResult();
    }
}
