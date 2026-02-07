<?php

namespace App\Context\Vehicle\Infrastructure\Controller;

use App\Context\Vehicle\Application\DTO\CreateVehicleDTO;
use App\Context\Vehicle\Application\UseCase\CreateVehicleUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class VehicleController extends AbstractController
{
    public function __construct(
        private CreateVehicleUseCase $createVehicleUseCase
    ) {
    }

    /*
     * Temporary security: Role Admin Only.
     * In future steps we will implement Voters for granular access.
     */
    #[Route('/api/vehicles', name: 'api_create_vehicle', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(#[MapRequestPayload] CreateVehicleDTO $dto): JsonResponse
    {
        $responseDTO = $this->createVehicleUseCase->execute($dto);

        return $this->json($responseDTO, 201);
    }
}
