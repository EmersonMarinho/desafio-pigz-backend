<?php

namespace App\Context\Vehicle\Infrastructure\Controller;

use App\Context\Vehicle\Application\DTO\CreateVehicleDTO;
use App\Context\Vehicle\Application\DTO\UpdateVehicleDTO;
use App\Context\Vehicle\Application\UseCase\CreateVehicleUseCase;
use App\Context\Vehicle\Application\UseCase\GetVehiclePriceComparisonUseCase;
use App\Context\Vehicle\Application\UseCase\ListVehiclesUseCase;
use App\Context\Vehicle\Application\UseCase\GetVehicleUseCase;
use App\Context\Vehicle\Application\UseCase\UpdateVehicleUseCase;
use App\Context\Vehicle\Application\UseCase\DeleteVehicleUseCase;
use App\Context\Vehicle\Domain\Repository\VehicleRepositoryInterface;
use App\Context\Vehicle\Infrastructure\Security\VehicleVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/vehicles', name: 'api_vehicles_')]
class VehicleController extends AbstractController
{
    public function __construct(
        private CreateVehicleUseCase $createVehicleUseCase,
        private GetVehiclePriceComparisonUseCase $priceComparisonUseCase,
        private ListVehiclesUseCase $listVehiclesUseCase,
        private GetVehicleUseCase $getVehicleUseCase,
        private UpdateVehicleUseCase $updateVehicleUseCase,
        private DeleteVehicleUseCase $deleteVehicleUseCase,
        private VehicleRepositoryInterface $vehicleRepository
    ) {
    }

    #[Route('', name: 'api_create_vehicle', methods: ['POST'])]
    #[IsGranted(VehicleVoter::CREATE)]
    public function create(
        Request $request
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $dto = CreateVehicleDTO::fromArray($data);

        $vehicleResponse = $this->createVehicleUseCase->execute($dto);

        return $this->json([
            'success' => true,
            'message' => 'Vehicle created successfully',
            'data' => $vehicleResponse->toArray()
        ], Response::HTTP_CREATED);
    }

    #[Route('', name: 'api_list_vehicles', methods: ['GET'])]
    public function list(
        Request $request
    ): JsonResponse {
        $make = $request->query->get('make');
        $model = $request->query->get('model');
        $year = $request->query->get('year') ? (int) $request->query->get('year') : null;
        $minPrice = $request->query->get('minPrice') ? (float) $request->query->get('minPrice') : null;
        $maxPrice = $request->query->get('maxPrice') ? (float) $request->query->get('maxPrice') : null;

        $vehicles = $this->listVehiclesUseCase->execute(
            make: $make,
            model: $model,
            year: $year,
            minPrice: $minPrice,
            maxPrice: $maxPrice
        );

        return $this->json([
            'success' => true,
            'data' => array_map(fn($dto) => $dto->toArray(), $vehicles),
            'count' => count($vehicles)
        ]);
    }

    #[Route('/{id}', name: 'api_get_vehicle', methods: ['GET'])]
    public function get(
        int $id
    ): JsonResponse {
        $vehicle = $this->vehicleRepository->find($id);
        if (!$vehicle) {
            throw $this->createNotFoundException('Vehicle not found');
        }

        $this->denyAccessUnlessGranted(VehicleVoter::VIEW, $vehicle);

        $vehicleDTO = $this->getVehicleUseCase->execute($id);

        return $this->json([
            'success' => true,
            'data' => $vehicleDTO->toArray()
        ]);
    }

    #[Route('/{id}', name: 'api_update_vehicle', methods: ['PUT', 'PATCH'])]
    public function update(
        int $id,
        Request $request
    ): JsonResponse {
        // Get the vehicle entity first for authorization check
        $vehicle = $this->vehicleRepository->find($id);
        if (!$vehicle) {
            throw $this->createNotFoundException('Vehicle not found');
        }

        $this->denyAccessUnlessGranted(VehicleVoter::EDIT, $vehicle);

        $data = json_decode($request->getContent(), true);
        $dto = UpdateVehicleDTO::fromArray($data);

        $vehicleDTO = $this->updateVehicleUseCase->execute($id, $dto);

        return $this->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'data' => $vehicleDTO->toArray()
        ]);
    }

    #[Route('/{id}', name: 'api_delete_vehicle', methods: ['DELETE'])]
    public function delete(
        int $id
    ): JsonResponse {
        // Get the vehicle entity first for authorization check
        $vehicle = $this->vehicleRepository->find($id);
        if (!$vehicle) {
            throw $this->createNotFoundException('Vehicle not found');
        }

        $this->denyAccessUnlessGranted(VehicleVoter::DELETE, $vehicle);

        $this->deleteVehicleUseCase->execute($id);

        return $this->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully'
        ]);
    }

    #[Route('/{id}/price-comparison', name: 'api_vehicle_price_comparison', methods: ['GET'])]
    public function priceComparison(int $id): JsonResponse
    {
        $comparison = $this->priceComparisonUseCase->execute($id);

        return $this->json([
            'success' => true,
            'data' => $comparison->toArray()
        ]);
    }
}
