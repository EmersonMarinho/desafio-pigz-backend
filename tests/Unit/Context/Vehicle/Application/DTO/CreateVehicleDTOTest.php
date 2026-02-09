<?php

namespace App\Tests\Unit\Context\Vehicle\Application\DTO;

use App\Context\Vehicle\Application\DTO\CreateVehicleDTO;
use PHPUnit\Framework\TestCase;

class CreateVehicleDTOTest extends TestCase
{
    public function testFromArrayWithCompleteData(): void
    {
        $data = [
            'make' => 'Fiat',
            'model' => 'Mobi',
            'version' => 'Like 1.0',
            'image' => 'http://example.com/image.jpg',
            'kms' => 15000,
            'price' => 55000.50,
            'yearModel' => 2023,
            'yearFab' => 2022,
            'color' => 'Branco',
            'fipeCode' => '001461-3',
            'fuel' => 'Flex',
        ];

        $dto = CreateVehicleDTO::fromArray($data);

        $this->assertSame('Fiat', $dto->make);
        $this->assertSame('Mobi', $dto->model);
        $this->assertSame('Like 1.0', $dto->version);
        $this->assertSame('http://example.com/image.jpg', $dto->image);
        $this->assertSame(15000, $dto->kms);
        $this->assertSame(55000.50, $dto->price);
        $this->assertSame(2023, $dto->yearModel);
        $this->assertSame(2022, $dto->yearFab);
        $this->assertSame('Branco', $dto->color);
        $this->assertSame('001461-3', $dto->fipeCode);
        $this->assertSame('Flex', $dto->fuel);
    }

    public function testFromArrayWithPartialDataUsesDefaults(): void
    {
        $data = [
            'make' => 'Honda',
            'model' => 'Civic',
            'version' => 'EX',
            'color' => 'Preto',
        ];

        $dto = CreateVehicleDTO::fromArray($data);

        $this->assertSame('Honda', $dto->make);
        $this->assertSame('Civic', $dto->model);
        $this->assertSame('EX', $dto->version);
        $this->assertNull($dto->image);
        $this->assertSame(0, $dto->kms);
        $this->assertSame(0.0, $dto->price);
        $this->assertSame(0, $dto->yearModel);
        $this->assertSame(0, $dto->yearFab);
        $this->assertSame('Preto', $dto->color);
        $this->assertNull($dto->fipeCode);
        $this->assertNull($dto->fuel);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $dto = CreateVehicleDTO::fromArray([]);

        $this->assertSame('', $dto->make);
        $this->assertSame('', $dto->model);
        $this->assertSame('', $dto->version);
        $this->assertSame(0, $dto->kms);
        $this->assertSame(0.0, $dto->price);
        $this->assertSame('', $dto->color);
    }
}
