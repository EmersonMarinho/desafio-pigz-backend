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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

use App\Context\Fipe\Infrastructure\Security\FipeVoter;

#[Route('/api/fipe', name: 'api_fipe_')]
#[OA\Tag(name: 'FIPE', description: 'Endpoints para consulta e gestão de preços da tabela FIPE')]
class FipeController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Listar preços FIPE',
        description: 'Retorna uma lista de preços FIPE com filtros opcionais por marca, ano, combustível e faixa de preço.'
    )]
    #[OA\Parameter(name: 'brand', in: 'query', description: 'Filtrar por marca', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'year', in: 'query', description: 'Filtrar por ano', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'fuel', in: 'query', description: 'Filtrar por tipo de combustível', schema: new OA\Schema(type: 'string', enum: ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'GNV', 'Híbrido', 'Elétrico']))]
    #[OA\Parameter(name: 'minPrice', in: 'query', description: 'Preço mínimo', schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Parameter(name: 'maxPrice', in: 'query', description: 'Preço máximo', schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Response(
        response: 200,
        description: 'Lista de preços FIPE',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                new OA\Property(property: 'count', type: 'integer', example: 10)
            ]
        )
    )]
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
    #[OA\Get(
        summary: 'Consultar código FIPE',
        description: 'Busca informações de um veículo na API externa Brasil API usando o código FIPE.'
    )]
    #[OA\Parameter(name: 'fipeCode', in: 'path', required: true, description: 'Código FIPE do veículo', schema: new OA\Schema(type: 'string', example: '001004-9'))]
    #[OA\Response(
        response: 200,
        description: 'Dados do veículo encontrado',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'object'),
                new OA\Property(property: 'source', type: 'string', example: 'brasil_api')
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Código FIPE não encontrado')]
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
    #[OA\Get(
        summary: 'Obter preço FIPE por ID',
        description: 'Retorna os detalhes de um registro de preço FIPE específico.'
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID do registro FIPE', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Dados do preço FIPE',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'object')
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Registro não encontrado')]
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
    #[OA\Post(
        summary: 'Criar preço FIPE',
        description: 'Cria um novo registro de preço FIPE. Requer autenticação.'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['vehicleCode', 'brand', 'model', 'year', 'fuel', 'price', 'referenceMonth'],
            properties: [
                new OA\Property(property: 'vehicleCode', type: 'string', example: '001004-9', description: 'Código do veículo'),
                new OA\Property(property: 'brand', type: 'string', example: 'Fiat', description: 'Marca do veículo'),
                new OA\Property(property: 'model', type: 'string', example: 'Uno 1.0', description: 'Modelo do veículo'),
                new OA\Property(property: 'year', type: 'integer', example: 2023, description: 'Ano do modelo'),
                new OA\Property(property: 'fuel', type: 'string', example: 'Flex', enum: ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'GNV', 'Híbrido', 'Elétrico']),
                new OA\Property(property: 'price', type: 'number', format: 'float', example: 45000.00, description: 'Preço FIPE'),
                new OA\Property(property: 'referenceMonth', type: 'string', example: '01/2024', description: 'Mês de referência (MM/YYYY)')
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Preço FIPE criado com sucesso')]
    #[OA\Response(response: 401, description: 'Não autenticado')]
    #[OA\Response(response: 403, description: 'Não autorizado')]
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
    #[OA\Put(
        summary: 'Atualizar preço FIPE',
        description: 'Atualiza um registro de preço FIPE existente. Requer autenticação.'
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID do registro FIPE', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'vehicleCode', type: 'string', description: 'Código do veículo'),
                new OA\Property(property: 'brand', type: 'string', description: 'Marca do veículo'),
                new OA\Property(property: 'model', type: 'string', description: 'Modelo do veículo'),
                new OA\Property(property: 'year', type: 'integer', description: 'Ano do modelo'),
                new OA\Property(property: 'fuel', type: 'string', enum: ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'GNV', 'Híbrido', 'Elétrico']),
                new OA\Property(property: 'price', type: 'number', format: 'float', description: 'Preço FIPE'),
                new OA\Property(property: 'referenceMonth', type: 'string', description: 'Mês de referência (MM/YYYY)')
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Preço FIPE atualizado com sucesso')]
    #[OA\Response(response: 401, description: 'Não autenticado')]
    #[OA\Response(response: 403, description: 'Não autorizado')]
    #[OA\Response(response: 404, description: 'Registro não encontrado')]
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
    #[OA\Delete(
        summary: 'Deletar preço FIPE',
        description: 'Remove um registro de preço FIPE. Requer autenticação.'
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID do registro FIPE', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Preço FIPE deletado com sucesso')]
    #[OA\Response(response: 401, description: 'Não autenticado')]
    #[OA\Response(response: 403, description: 'Não autorizado')]
    #[OA\Response(response: 404, description: 'Registro não encontrado')]
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
