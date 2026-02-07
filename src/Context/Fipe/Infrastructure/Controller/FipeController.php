<?php

namespace App\Context\Fipe\Infrastructure\Controller;

use App\Context\Fipe\Application\DTO\CreateFipePriceDTO;
use App\Context\Fipe\Application\DTO\UpdateFipePriceDTO;
use App\Context\Fipe\Application\UseCase\CreateFipePriceUseCase;
use App\Context\Fipe\Application\UseCase\DeleteFipePriceUseCase;
use App\Context\Fipe\Application\UseCase\GetFipePriceUseCase;
use App\Context\Fipe\Application\UseCase\ListFipePricesUseCase;
use App\Context\Fipe\Application\UseCase\SearchFipeByCodeUseCase;
use App\Context\Fipe\Application\UseCase\UpdateFipePriceUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Context\Fipe\Infrastructure\Security\FipeVoter;

#[Route('/api/fipe', name: 'api_fipe_')]
class FipeController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
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

    #[Route('/search/{fipeCode}', name: 'search', methods: ['GET'])]
    public function search(
        string $fipeCode,
        SearchFipeByCodeUseCase $searchFipeByCodeUseCase
    ): JsonResponse {
        $fipePrice = $searchFipeByCodeUseCase->execute($fipeCode, saveToCache: true);

        return $this->json([
            'success' => true,
            'data' => $fipePrice->toArray(),
            'source' => 'external_api'
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
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
