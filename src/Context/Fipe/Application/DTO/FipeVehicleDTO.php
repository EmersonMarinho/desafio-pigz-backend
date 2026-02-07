<?php

namespace App\Context\Fipe\Application\DTO;

class FipeVehicleDTO
{
    public function __construct(
        public readonly string $vehicleCode,
        public readonly string $brand,
        public readonly string $model,
        public readonly int $year,
        public readonly string $fuel,
        public readonly float $price,
        public readonly string $referenceMonth,
        public readonly string $source = 'brasil_api'
    ) {
    }

    public static function fromBrasilApiResponse(array $data): self
    {
        return new self(
            vehicleCode: $data['codigoFipe'] ?? '',
            brand: $data['marca'] ?? '',
            model: $data['modelo'] ?? '',
            year: (int) ($data['anoModelo'] ?? 0),
            fuel: $data['combustivel'] ?? '',
            price: self::parsePrice($data['valor'] ?? ''),
            referenceMonth: $data['mesReferencia'] ?? '',
            source: 'brasil_api'
        );
    }

    private static function parsePrice(string $priceString): float
    {
        // Remove "R$ " and convert "1.234,56" to 1234.56
        $clean = str_replace(['R$ ', '.', ','], ['', '', '.'], $priceString);
        return (float) $clean;
    }

    public function toArray(): array
    {
        return [
            'vehicleCode' => $this->vehicleCode,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'fuel' => $this->fuel,
            'price' => $this->price,
            'referenceMonth' => $this->referenceMonth,
            'source' => $this->source,
        ];
    }
}
