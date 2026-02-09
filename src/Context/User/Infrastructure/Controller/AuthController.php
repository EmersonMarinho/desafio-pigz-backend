<?php

namespace App\Context\User\Infrastructure\Controller;

use App\Context\User\Application\DTO\CreateUserDTO;
use App\Context\User\Application\UseCase\RegisterUserUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Authentication')]
class AuthController extends AbstractController
{
    public function __construct(
        private RegisterUserUseCase $registerUserUseCase
    ) {
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'password123', minLength: 6),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'User registered successfully')]
    #[OA\Response(response: 400, description: 'Validation error')]
    public function register(#[MapRequestPayload] CreateUserDTO $dto): JsonResponse
    {
        $responseDTO = $this->registerUserUseCase->execute($dto);

        return $this->json($responseDTO, 201);
    }
}
