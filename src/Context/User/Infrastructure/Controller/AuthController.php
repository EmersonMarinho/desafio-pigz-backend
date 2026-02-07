<?php

namespace App\Context\User\Infrastructure\Controller;

use App\Context\User\Application\DTO\CreateUserDTO;
use App\Context\User\Application\UseCase\RegisterUserUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private RegisterUserUseCase $registerUserUseCase
    ) {
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(#[MapRequestPayload] CreateUserDTO $dto): JsonResponse
    {
        $responseDTO = $this->registerUserUseCase->execute($dto);

        return $this->json($responseDTO, 201);
    }
}
