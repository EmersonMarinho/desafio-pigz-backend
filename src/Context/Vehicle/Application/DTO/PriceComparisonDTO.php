<?php

namespace App\Context\Vehicle\Application\DTO;

class PriceComparisonDTO
{
    public function __construct(
        public readonly int $vehicleId,
        public readonly float $vehiclePrice,
        public readonly ?float $fipePrice,
        public readonly ?float $difference,
        public readonly ?float $percentageDifference,
        public readonly string $status,
        public readonly ?string $fipeCode = null,
        public readonly ?string $referenceMonth = null,
    ) {
    }

    public static function create(
        int $vehicleId,
        float $vehiclePrice,
        ?float $fipePrice,
        ?string $fipeCode = null,
        ?string $referenceMonth = null
    ): self {
        if ($fipePrice === null) {
            return new self(
                vehicleId: $vehicleId,
                vehiclePrice: $vehiclePrice,
                fipePrice: null,
                difference: null,
                percentageDifference: null,
                status: 'no_fipe_data',
                fipeCode: $fipeCode,
                referenceMonth: $referenceMonth
            );
        }

        $difference = $vehiclePrice - $fipePrice;
        $percentageDifference = ($difference / $fipePrice) * 100;

        $status = match (true) {
            abs($percentageDifference) < 1 => 'equal',
            $percentageDifference > 0 => 'above',
            default => 'below'
        };

        return new self(
            vehicleId: $vehicleId,
            vehiclePrice: $vehiclePrice,
            fipePrice: $fipePrice,
            difference: round($difference, 2),
            percentageDifference: round($percentageDifference, 2),
            status: $status,
            fipeCode: $fipeCode,
            referenceMonth: $referenceMonth
        );
    }

    public function toArray(): array
    {
        return [
            'vehicleId' => $this->vehicleId,
            'vehiclePrice' => $this->vehiclePrice,
            'fipePrice' => $this->fipePrice,
            'difference' => $this->difference,
            'percentageDifference' => $this->percentageDifference,
            'status' => $this->status,
            'fipeCode' => $this->fipeCode,
            'referenceMonth' => $this->referenceMonth,
        ];
    }
}
