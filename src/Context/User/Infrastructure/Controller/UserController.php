<?php

namespace App\Context\User\Infrastructure\Controller;

use App\Context\User\Application\DTO\CreateUserDTO;
use App\Context\User\Application\UseCase\RegisterUserUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_user_')]
class UserController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        RegisterUserUseCase $registerUserUseCase
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $dto = CreateUserDTO::fromArray($data);

        $userResponse = $registerUserUseCase->execute($dto);

        return $this->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $userResponse->toArray()
        ], Response::HTTP_CREATED);
    }
}
