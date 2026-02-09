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
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/vehicles', name: 'api_vehicles_')]
#[OA\Tag(name: 'Vehicles')]
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
    #[OA\RequestBody(
        content: new OA\JsonContent(
            required: ['make', 'model', 'version', 'kms', 'price', 'yearModel', 'yearFab', 'color'],
            properties: [
                new OA\Property(property: 'make', type: 'string', example: 'Fiat'),
                new OA\Property(property: 'model', type: 'string', example: 'Mobi'),
                new OA\Property(property: 'version', type: 'string', example: 'Like 1.0'),
                new OA\Property(property: 'kms', type: 'integer', example: 0),
                new OA\Property(property: 'price', type: 'number', example: 50000),
                new OA\Property(property: 'yearModel', type: 'integer', example: 2022),
                new OA\Property(property: 'yearFab', type: 'integer', example: 2022),
                new OA\Property(property: 'color', type: 'string', example: 'Branco'),
                new OA\Property(property: 'fipeCode', type: 'string', example: '001461-3'),
                new OA\Property(property: 'fuel', type: 'string', example: 'Flex'),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Vehicle created successfully')]
    #[OA\Security(name: 'Bearer')]
    public function create(
        Request $request
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!\is_array($data)) {
            throw new BadRequestHttpException('Invalid JSON');
        }
        $dto = CreateVehicleDTO::fromArray($data);

        $vehicleResponse = $this->createVehicleUseCase->execute($dto);

        return $this->json([
            'success' => true,
            'message' => 'Vehicle created successfully',
            'data' => $vehicleResponse->toArray()
        ], Response::HTTP_CREATED);
    }

    #[Route('', name: 'api_list_vehicles', methods: ['GET'])]
    #[OA\Parameter(name: 'make', in: 'query', description: 'Filter by brand')]
    #[OA\Parameter(name: 'model', in: 'query', description: 'Filter by model')]
    #[OA\Parameter(name: 'year', in: 'query', description: 'Filter by year')]
    #[OA\Parameter(name: 'minPrice', in: 'query', description: 'Minimum price')]
    #[OA\Parameter(name: 'maxPrice', in: 'query', description: 'Maximum price')]
    #[OA\Response(response: 200, description: 'List of vehicles')]
    #[OA\Security(name: 'Bearer')]
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
    #[OA\Parameter(name: 'id', in: 'path', description: 'Vehicle ID', required: true)]
    #[OA\Response(response: 200, description: 'Vehicle details')]
    #[OA\Response(response: 404, description: 'Vehicle not found')]
    #[OA\Security(name: 'Bearer')]
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
    #[OA\Parameter(name: 'id', in: 'path', description: 'Vehicle ID', required: true)]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'make', type: 'string'),
                new OA\Property(property: 'model', type: 'string'),
                new OA\Property(property: 'price', type: 'number'),
                new OA\Property(property: 'color', type: 'string'),
                new OA\Property(property: 'kms', type: 'integer'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Vehicle updated successfully')]
    #[OA\Security(name: 'Bearer')]
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
        if (!\is_array($data)) {
            throw new BadRequestHttpException('Invalid JSON');
        }
        $dto = UpdateVehicleDTO::fromArray($data);

        $vehicleDTO = $this->updateVehicleUseCase->execute($id, $dto);

        return $this->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'data' => $vehicleDTO->toArray()
        ]);
    }

    #[Route('/{id}', name: 'api_delete_vehicle', methods: ['DELETE'])]
    #[OA\Parameter(name: 'id', in: 'path', description: 'Vehicle ID', required: true)]
    #[OA\Response(response: 200, description: 'Vehicle deleted successfully')]
    #[OA\Security(name: 'Bearer')]
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
    #[OA\Parameter(name: 'id', in: 'path', description: 'Vehicle ID', required: true)]
    #[OA\Response(response: 200, description: 'Price comparison with FIPE table')]
    #[OA\Security(name: 'Bearer')]
    public function priceComparison(int $id): JsonResponse
    {
        $comparison = $this->priceComparisonUseCase->execute($id);

        return $this->json([
            'success' => true,
            'data' => $comparison->toArray()
        ]);
    }
}
