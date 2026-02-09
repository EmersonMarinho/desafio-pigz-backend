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
use OpenApi\Attributes as OA;

#[Route('/api/vehicles', name: 'api_vehicles_')]
#[OA\Tag(name: 'Veículos', description: 'Endpoints para gestão de veículos')]
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
    #[OA\Post(
        summary: 'Criar veículo',
        description: 'Cadastra um novo veículo no sistema. Requer autenticação.'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['make', 'model', 'version', 'kms', 'price', 'yearModel', 'yearFab', 'color'],
            properties: [
                new OA\Property(property: 'make', type: 'string', example: 'Fiat', description: 'Marca do veículo'),
                new OA\Property(property: 'model', type: 'string', example: 'Uno', description: 'Modelo do veículo'),
                new OA\Property(property: 'version', type: 'string', example: '1.0 Fire Flex', description: 'Versão do veículo'),
                new OA\Property(property: 'image', type: 'string', nullable: true, example: 'https://example.com/image.jpg', description: 'URL da imagem'),
                new OA\Property(property: 'kms', type: 'integer', example: 50000, description: 'Quilometragem'),
                new OA\Property(property: 'price', type: 'number', format: 'float', example: 35000.00, description: 'Preço de venda'),
                new OA\Property(property: 'yearModel', type: 'integer', example: 2023, description: 'Ano do modelo'),
                new OA\Property(property: 'yearFab', type: 'integer', example: 2022, description: 'Ano de fabricação'),
                new OA\Property(property: 'color', type: 'string', example: 'Branco', description: 'Cor do veículo'),
                new OA\Property(property: 'fipeCode', type: 'string', nullable: true, example: '001004-9', description: 'Código FIPE'),
                new OA\Property(property: 'fuel', type: 'string', nullable: true, example: 'Flex', description: 'Tipo de combustível')
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Veículo criado com sucesso')]
    #[OA\Response(response: 401, description: 'Não autenticado')]
    #[OA\Response(response: 403, description: 'Não autorizado')]
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
    #[OA\Get(
        summary: 'Listar veículos',
        description: 'Retorna uma lista de veículos com filtros opcionais.'
    )]
    #[OA\Parameter(name: 'make', in: 'query', description: 'Filtrar por marca', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'model', in: 'query', description: 'Filtrar por modelo', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'year', in: 'query', description: 'Filtrar por ano', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'minPrice', in: 'query', description: 'Preço mínimo', schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Parameter(name: 'maxPrice', in: 'query', description: 'Preço máximo', schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de veículos',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                new OA\Property(property: 'count', type: 'integer', example: 10)
            ]
        )
    )]
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
    #[OA\Get(
        summary: 'Obter veículo',
        description: 'Retorna os detalhes de um veículo específico. Requer autenticação.'
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID do veículo', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Dados do veículo',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'object')
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Não autenticado')]
    #[OA\Response(response: 404, description: 'Veículo não encontrado')]
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
    #[OA\Put(
        summary: 'Atualizar veículo',
        description: 'Atualiza os dados de um veículo existente. Requer autenticação.'
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID do veículo', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'make', type: 'string', description: 'Marca do veículo'),
                new OA\Property(property: 'model', type: 'string', description: 'Modelo do veículo'),
                new OA\Property(property: 'version', type: 'string', description: 'Versão do veículo'),
                new OA\Property(property: 'image', type: 'string', nullable: true, description: 'URL da imagem'),
                new OA\Property(property: 'kms', type: 'integer', description: 'Quilometragem'),
                new OA\Property(property: 'price', type: 'number', format: 'float', description: 'Preço de venda'),
                new OA\Property(property: 'yearModel', type: 'integer', description: 'Ano do modelo'),
                new OA\Property(property: 'yearFab', type: 'integer', description: 'Ano de fabricação'),
                new OA\Property(property: 'color', type: 'string', description: 'Cor do veículo'),
                new OA\Property(property: 'fipeCode', type: 'string', nullable: true, description: 'Código FIPE'),
                new OA\Property(property: 'fuel', type: 'string', nullable: true, description: 'Tipo de combustível')
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Veículo atualizado com sucesso')]
    #[OA\Response(response: 401, description: 'Não autenticado')]
    #[OA\Response(response: 403, description: 'Não autorizado')]
    #[OA\Response(response: 404, description: 'Veículo não encontrado')]
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
    #[OA\Delete(
        summary: 'Deletar veículo',
        description: 'Remove um veículo do sistema. Requer autenticação.'
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID do veículo', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Veículo deletado com sucesso')]
    #[OA\Response(response: 401, description: 'Não autenticado')]
    #[OA\Response(response: 403, description: 'Não autorizado')]
    #[OA\Response(response: 404, description: 'Veículo não encontrado')]
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
    #[OA\Get(
        summary: 'Comparação de preço FIPE',
        description: 'Compara o preço do veículo com o valor da tabela FIPE.'
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID do veículo', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Dados de comparação de preço',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'vehiclePrice', type: 'number', format: 'float'),
                    new OA\Property(property: 'fipePrice', type: 'number', format: 'float'),
                    new OA\Property(property: 'difference', type: 'number', format: 'float'),
                    new OA\Property(property: 'percentageDifference', type: 'number', format: 'float')
                ])
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Veículo não encontrado')]
    public function priceComparison(int $id): JsonResponse
    {
        $comparison = $this->priceComparisonUseCase->execute($id);

        return $this->json([
            'success' => true,
            'data' => $comparison->toArray()
        ]);
    }
}
