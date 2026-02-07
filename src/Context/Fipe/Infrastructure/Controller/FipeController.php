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
        try {
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
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/search/{fipeCode}', name: 'search', methods: ['GET'])]
    public function search(
        string $fipeCode,
        SearchFipeByCodeUseCase $searchFipeByCodeUseCase
    ): JsonResponse {
        try {
            $fipePrice = $searchFipeByCodeUseCase->execute($fipeCode, saveToCache: true);

            return $this->json([
                'success' => true,
                'data' => $fipePrice->toArray(),
                'source' => 'external_api'
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(
        int $id,
        GetFipePriceUseCase $getFipePriceUseCase
    ): JsonResponse {
        try {
            $fipePrice = $getFipePriceUseCase->execute($id);

            return $this->json([
                'success' => true,
                'data' => $fipePrice->toArray()
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted(FipeVoter::MANAGE)]
    public function create(
        Request $request,
        CreateFipePriceUseCase $createFipePriceUseCase
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            $dto = CreateFipePriceDTO::fromArray($data);

            $fipePrice = $createFipePriceUseCase->execute($dto);

            return $this->json([
                'success' => true,
                'message' => 'FIPE price created successfully',
                'data' => $fipePrice->toArray()
            ], Response::HTTP_CREATED);
        } catch (\Symfony\Component\HttpKernel\Exception\BadRequestHttpException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[IsGranted(FipeVoter::MANAGE)]
    public function update(
        int $id,
        Request $request,
        UpdateFipePriceUseCase $updateFipePriceUseCase
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            $dto = UpdateFipePriceDTO::fromArray($data);

            $fipePrice = $updateFipePriceUseCase->execute($id, $dto);

            return $this->json([
                'success' => true,
                'message' => 'FIPE price updated successfully',
                'data' => $fipePrice->toArray()
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Symfony\Component\HttpKernel\Exception\BadRequestHttpException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(FipeVoter::MANAGE)]
    public function delete(
        int $id,
        DeleteFipePriceUseCase $deleteFipePriceUseCase
    ): JsonResponse {
        try {
            $deleteFipePriceUseCase->execute($id);

            return $this->json([
                'success' => true,
                'message' => 'FIPE price deleted successfully'
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
