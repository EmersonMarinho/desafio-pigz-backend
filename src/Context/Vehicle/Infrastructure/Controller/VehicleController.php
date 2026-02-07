<?php

namespace App\Context\Vehicle\Infrastructure\Controller;

use App\Context\Vehicle\Application\DTO\CreateVehicleDTO;
use App\Context\Vehicle\Application\UseCase\CreateVehicleUseCase;
use App\Context\Vehicle\Application\UseCase\GetVehiclePriceComparisonUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use App\Context\Vehicle\Infrastructure\Security\VehicleVoter;

class VehicleController extends AbstractController
{
    public function __construct(
        private CreateVehicleUseCase $createVehicleUseCase,
        private GetVehiclePriceComparisonUseCase $priceComparisonUseCase
    ) {
    }

    #[Route('/api/vehicles', name: 'api_create_vehicle', methods: ['POST'])]
    #[IsGranted(VehicleVoter::CREATE)]
    public function create(#[MapRequestPayload] CreateVehicleDTO $dto): JsonResponse
    {
        $responseDTO = $this->createVehicleUseCase->execute($dto);

        return $this->json($responseDTO, 201);
    }

    #[Route('/api/vehicles/{id}/price-comparison', name: 'api_vehicle_price_comparison', methods: ['GET'])]
    public function priceComparison(int $id): JsonResponse
    {
        $comparison = $this->priceComparisonUseCase->execute($id);

        return $this->json([
            'success' => true,
            'data' => $comparison->toArray()
        ]);
    }
}
