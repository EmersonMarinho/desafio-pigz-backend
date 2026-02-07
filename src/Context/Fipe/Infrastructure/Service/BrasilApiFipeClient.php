<?php

namespace App\Context\Fipe\Infrastructure\Service;

use App\Context\Fipe\Application\DTO\FipeVehicleDTO;
use App\Context\Fipe\Domain\Service\FipeApiClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class BrasilApiFipeClient implements FipeApiClientInterface
{
    private const BASE_URL = 'https://brasilapi.com.br/api/fipe';
    private const TIMEOUT = 10; // seconds

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger
    ) {
    }

    public function lookupByCode(string $fipeCode): ?FipeVehicleDTO
    {
        try {
            // Brasil API endpoint for FIPE lookup
            // Example: /api/fipe/preco/v1/001004-1
            $url = self::BASE_URL . '/preco/v1/' . $fipeCode;

            $this->logger->info('Fetching FIPE data from Brasil API', [
                'fipe_code' => $fipeCode,
                'url' => $url
            ]);

            $response = $this->httpClient->request('GET', $url, [
                'timeout' => self::TIMEOUT,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 404) {
                $this->logger->warning('FIPE code not found', ['fipe_code' => $fipeCode]);
                return null;
            }

            if ($statusCode !== 200) {
                $this->logger->error('Brasil API returned non-200 status', [
                    'status_code' => $statusCode,
                    'fipe_code' => $fipeCode
                ]);
                throw new \RuntimeException('Brasil API returned status: ' . $statusCode);
            }

            $data = $response->toArray();

            $item = isset($data[0]) ? $data[0] : $data;

            return FipeVehicleDTO::fromBrasilApiResponse($item);

        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to connect to Brasil API', [
                'error' => $e->getMessage(),
                'fipe_code' => $fipeCode
            ]);
            throw new \RuntimeException('Failed to connect to Brasil API: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching FIPE data', [
                'error' => $e->getMessage(),
                'fipe_code' => $fipeCode
            ]);
            throw new \RuntimeException('Error fetching FIPE data: ' . $e->getMessage(), 0, $e);
        }
    }

    public function search(?string $brand = null, ?string $model = null, ?int $year = null): array
    {
        $this->logger->info('FIPE search called', [
            'brand' => $brand,
            'model' => $model,
            'year' => $year
        ]);
        return [];
    }
}
