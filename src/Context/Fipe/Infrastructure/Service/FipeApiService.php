<?php

namespace App\Context\Fipe\Infrastructure\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FipeApiService
{
    private const BASE_URL = 'https://parallelum.com.br/fipe/api/v1';

    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
    }

    /**
     * Get all brands for a given vehicle type
     * @param string $vehicleType 'carros', 'motos', or 'caminhoes'
     */
    public function getBrands(string $vehicleType = 'carros'): array
    {
        $response = $this->httpClient->request(
            'GET',
            self::BASE_URL . "/{$vehicleType}/marcas"
        );

        return $response->toArray();
    }

    public function getModels(string $vehicleType, int $brandCode): array
    {
        $response = $this->httpClient->request(
            'GET',
            self::BASE_URL . "/{$vehicleType}/marcas/{$brandCode}/modelos"
        );

        return $response->toArray();
    }

    public function getYears(string $vehicleType, int $brandCode, int $modelCode): array
    {
        $response = $this->httpClient->request(
            'GET',
            self::BASE_URL . "/{$vehicleType}/marcas/{$brandCode}/modelos/{$modelCode}/anos"
        );

        return $response->toArray();
    }

    public function getPrice(string $vehicleType, int $brandCode, int $modelCode, string $yearCode): array
    {
        $response = $this->httpClient->request(
            'GET',
            self::BASE_URL . "/{$vehicleType}/marcas/{$brandCode}/modelos/{$modelCode}/anos/{$yearCode}"
        );

        return $response->toArray();
    }

    public function searchByFipeCode(string $fipeCode, string $vehicleType = 'carros'): ?array
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                self::BASE_URL . "/{$vehicleType}/veiculo/{$fipeCode}"
            );

            return $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function parsePriceToFloat(string $priceString): float
    {
        $cleaned = str_replace(['R$', ' '], '', $priceString);
        $cleaned = str_replace('.', '', $cleaned);
        $cleaned = str_replace(',', '.', $cleaned);

        return (float) $cleaned;
    }

    public function getReferenceMonth(): string
    {
        return date('m/Y');
    }
}
