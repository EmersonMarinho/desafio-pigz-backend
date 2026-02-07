<?php

namespace App\Context\Fipe\Infrastructure\Repository;

use App\Context\Fipe\Domain\Entity\FipePrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FipePrice::class);
    }

    public function save(FipePrice $fipePrice): void
    {
        $this->getEntityManager()->persist($fipePrice);
        $this->getEntityManager()->flush();
    }

    public function remove(FipePrice $fipePrice): void
    {
        $this->getEntityManager()->remove($fipePrice);
        $this->getEntityManager()->flush();
    }

    public function findByVehicleCode(string $vehicleCode): ?FipePrice
    {
        return $this->findOneBy(['vehicleCode' => $vehicleCode]);
    }

    public function findWithFilters(
        ?string $brand = null,
        ?int $year = null,
        ?string $fuel = null,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): array {
        $qb = $this->createQueryBuilder('f');

        if ($brand) {
            $qb->andWhere('f.brand = :brand')
                ->setParameter('brand', $brand);
        }

        if ($year) {
            $qb->andWhere('f.year = :year')
                ->setParameter('year', $year);
        }

        if ($fuel) {
            $qb->andWhere('f.fuel = :fuel')
                ->setParameter('fuel', $fuel);
        }

        if ($minPrice !== null) {
            $qb->andWhere('f.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice !== null) {
            $qb->andWhere('f.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        $qb->orderBy('f.brand', 'ASC')
            ->addOrderBy('f.model', 'ASC')
            ->addOrderBy('f.year', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
