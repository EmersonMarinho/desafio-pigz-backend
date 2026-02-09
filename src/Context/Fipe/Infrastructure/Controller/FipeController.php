<?php

namespace App\Context\Fipe\Infrastructure\Controller;

use App\Context\Fipe\Application\DTO\CreateFipePriceDTO;
use App\Context\Fipe\Application\DTO\UpdateFipePriceDTO;
use App\Context\Fipe\Application\UseCase\CreateFipePriceUseCase;
use App\Context\Fipe\Application\UseCase\DeleteFipePriceUseCase;
use App\Context\Fipe\Application\UseCase\GetFipePriceUseCase;
use App\Context\Fipe\Application\UseCase\ListFipePricesUseCase;
use App\Context\Fipe\Application\UseCase\LookupFipeCodeUseCase;
use App\Context\Fipe\Application\UseCase\UpdateFipePriceUseCase;
use App\Context\Fipe\Infrastructure\Security\FipeVoter;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/fipe', name: 'api_fipe_')]
#[OA\Tag(name: 'FIPE')]
class FipeController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Parameter(name: 'brand', in: 'query', description: 'Filter by brand')]
    #[OA\Parameter(name: 'year', in: 'query', description: 'Filter by year')]
    #[OA\Parameter(name: 'fuel', in: 'query', description: 'Filter by fuel type')]
    #[OA\Parameter(name: 'minPrice', in: 'query', description: 'Minimum price')]
    #[OA\Parameter(name: 'maxPrice', in: 'query', description: 'Maximum price')]
    #[OA\Response(response: 200, description: 'List of FIPE prices')]
    #[OA\Security(name: 'Bearer')]
    public function list(
        Request $request,
        ListFipePricesUseCase $listFipePricesUseCase
    ): JsonResponse {
        $brand = $request->query->get('brand');
        $year = $request->query->get('year') ? (int) $request->query->get('year') : null;
        $fuel = $request->query->get('fuel');
        $minPrice = $request->query->get('minPrice') ? (float) $request->query->get('minPrice') : null;
        $maxPrice = $request->query->get('maxPrice') ? (float) $request->query->get('maxPrice') : null;

        $fipePrices = $listFipePricesUseCase->execute(
            brand: $brand,
            year: $year,
            fuel: $fuel,
            minPrice: $minPrice,
            maxPrice: $maxPrice
        );

        return $this->json([
            'success' => true,
            'data' => array_map(fn($dto) => $dto->toArray(), $fipePrices),
            'count' => count($fipePrices)
        ]);
    }

    #[Route('/lookup/{fipeCode}', name: 'lookup', methods: ['GET'])]
    #[OA\Parameter(name: 'fipeCode', in: 'path', description: 'FIPE code (e.g. 001461-3)', required: true)]
    #[OA\Response(response: 200, description: 'Vehicle data from Brasil API')]
    #[OA\Response(response: 404, description: 'FIPE code not found')]
    #[OA\Security(name: 'Bearer')]
    public function lookup(
        string $fipeCode,
        LookupFipeCodeUseCase $lookupFipeCodeUseCase
    ): JsonResponse {
        $fipeData = $lookupFipeCodeUseCase->execute($fipeCode);

        if (!$fipeData) {
            return $this->json([
                'success' => false,
                'message' => 'FIPE code not found in external API'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $fipeData->toArray(),
            'source' => 'brasil_api'
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    #[OA\Parameter(name: 'id', in: 'path', description: 'FIPE price ID', required: true)]
    #[OA\Response(response: 200, description: 'FIPE price details')]
    #[OA\Security(name: 'Bearer')]
    public function get(
        int $id,
        GetFipePriceUseCase $getFipePriceUseCase
    ): JsonResponse {
        $fipePrice = $getFipePriceUseCase->execute($id);

        return $this->json([
            'success' => true,
            'data' => $fipePrice->toArray()
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted(FipeVoter::MANAGE)]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            required: ['vehicleCode', 'brand', 'model', 'year', 'fuel', 'price', 'referenceMonth'],
            properties: [
                new OA\Property(property: 'vehicleCode', type: 'string', example: '001461-3'),
                new OA\Property(property: 'brand', type: 'string', example: 'Fiat'),
                new OA\Property(property: 'model', type: 'string', example: 'Mobi'),
                new OA\Property(property: 'year', type: 'integer', example: 2022),
                new OA\Property(property: 'fuel', type: 'string', enum: ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'GNV', 'Híbrido', 'Elétrico']),
                new OA\Property(property: 'price', type: 'number', example: 58900),
                new OA\Property(property: 'referenceMonth', type: 'string', example: '02/2024'),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'FIPE price saved to local cache (avoids external API calls on price comparison)')]
    #[OA\Security(name: 'Bearer')]
    public function create(
        Request $request,
        CreateFipePriceUseCase $createFipePriceUseCase
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $dto = CreateFipePriceDTO::fromArray($data);

        $fipePrice = $createFipePriceUseCase->execute($dto);

        return $this->json([
            'success' => true,
            'message' => 'FIPE price created successfully',
            'data' => $fipePrice->toArray()
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[IsGranted(FipeVoter::MANAGE)]
    #[OA\Parameter(name: 'id', in: 'path', description: 'FIPE price ID', required: true)]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'brand', type: 'string'),
                new OA\Property(property: 'model', type: 'string'),
                new OA\Property(property: 'year', type: 'integer'),
                new OA\Property(property: 'fuel', type: 'string'),
                new OA\Property(property: 'price', type: 'number'),
                new OA\Property(property: 'referenceMonth', type: 'string', example: '02/2024'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'FIPE price updated successfully')]
    #[OA\Security(name: 'Bearer')]
    public function update(
        int $id,
        Request $request,
        UpdateFipePriceUseCase $updateFipePriceUseCase
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $dto = UpdateFipePriceDTO::fromArray($data);

        $fipePrice = $updateFipePriceUseCase->execute($id, $dto);

        return $this->json([
            'success' => true,
            'message' => 'FIPE price updated successfully',
            'data' => $fipePrice->toArray()
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(FipeVoter::MANAGE)]
    #[OA\Parameter(name: 'id', in: 'path', description: 'FIPE price ID', required: true)]
    #[OA\Response(response: 200, description: 'FIPE price deleted successfully')]
    #[OA\Security(name: 'Bearer')]
    public function delete(
        int $id,
        DeleteFipePriceUseCase $deleteFipePriceUseCase
    ): JsonResponse {
        $deleteFipePriceUseCase->execute($id);

        return $this->json([
            'success' => true,
            'message' => 'FIPE price deleted successfully'
        ]);
    }
}
