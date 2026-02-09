<?php

namespace App\Tests\Unit\Context\Vehicle\Application\DTO;

use App\Context\Vehicle\Application\DTO\PriceComparisonDTO;
use PHPUnit\Framework\TestCase;

class PriceComparisonDTOTest extends TestCase
{
    public function testCreateWithNoFipeData(): void
    {
        $dto = PriceComparisonDTO::create(
            vehicleId: 1,
            vehiclePrice: 50000.0,
            fipePrice: null,
        );

        $this->assertSame(1, $dto->vehicleId);
        $this->assertSame(50000.0, $dto->vehiclePrice);
        $this->assertNull($dto->fipePrice);
        $this->assertNull($dto->difference);
        $this->assertNull($dto->percentageDifference);
        $this->assertSame('no_fipe_data', $dto->status);
    }

    public function testCreateWithVehicleAboveFipePrice(): void
    {
        $dto = PriceComparisonDTO::create(
            vehicleId: 1,
            vehiclePrice: 65000.0,
            fipePrice: 58900.0,
            fipeCode: '001461-3',
            referenceMonth: '02/2024',
            source: 'brasil_api'
        );

        $this->assertSame(1, $dto->vehicleId);
        $this->assertSame(65000.0, $dto->vehiclePrice);
        $this->assertSame(58900.0, $dto->fipePrice);
        $this->assertSame(6100.0, $dto->difference);
        $this->assertSame(10.36, $dto->percentageDifference);
        $this->assertSame('above', $dto->status);
        $this->assertSame('001461-3', $dto->fipeCode);
        $this->assertSame('brasil_api', $dto->source);
    }

    public function testCreateWithVehicleBelowFipePrice(): void
    {
        $dto = PriceComparisonDTO::create(
            vehicleId: 1,
            vehiclePrice: 50000.0,
            fipePrice: 58900.0
        );

        $this->assertSame(50000.0, $dto->vehiclePrice);
        $this->assertSame(58900.0, $dto->fipePrice);
        $this->assertSame(-8900.0, $dto->difference);
        $this->assertSame(-15.11, $dto->percentageDifference);
        $this->assertSame('below', $dto->status);
    }

    public function testCreateWithVehicleEqualPrice(): void
    {
        $dto = PriceComparisonDTO::create(
            vehicleId: 1,
            vehiclePrice: 58900.0,
            fipePrice: 58900.0
        );

        $this->assertSame(0.0, $dto->difference);
        $this->assertSame(0.0, $dto->percentageDifference);
        $this->assertSame('equal', $dto->status);
    }

    public function testCreateWithSmallDifferenceMarksAsEqual(): void
    {
        $dto = PriceComparisonDTO::create(
            vehicleId: 1,
            vehiclePrice: 58950.0,
            fipePrice: 58900.0
        );

        $this->assertSame('equal', $dto->status);
    }

    public function testToArray(): void
    {
        $dto = PriceComparisonDTO::create(
            vehicleId: 1,
            vehiclePrice: 50000.0,
            fipePrice: 58900.0,
            fipeCode: '001461-3',
            referenceMonth: '02/2024',
            source: 'local_database'
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertSame(1, $array['vehicleId']);
        $this->assertSame(50000.0, $array['vehiclePrice']);
        $this->assertSame(58900.0, $array['fipePrice']);
        $this->assertSame(-8900.0, $array['difference']);
        $this->assertSame('below', $array['status']);
        $this->assertSame('001461-3', $array['fipeCode']);
        $this->assertSame('local_database', $array['source']);
    }
}
