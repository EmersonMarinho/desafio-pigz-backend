<?php

namespace App\Context\User\Infrastructure\Controller;

use App\Context\User\Application\DTO\CreateUserDTO;
use App\Context\User\Application\UseCase\RegisterUserUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Autenticação', description: 'Endpoints de autenticação e registro de usuários')]
class AuthController extends AbstractController
{
    public function __construct(
        private RegisterUserUseCase $registerUserUseCase
    ) {
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    #[OA\Post(
        summary: 'Registrar novo usuário',
        description: 'Cria uma nova conta de usuário no sistema.'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'usuario@exemplo.com', description: 'Email do usuário'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 6, example: 'senha123', description: 'Senha (mínimo 6 caracteres)')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Usuário registrado com sucesso',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'email', type: 'string', example: 'usuario@exemplo.com')
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Dados inválidos')]
    #[OA\Response(response: 409, description: 'Email já cadastrado')]
    public function register(#[MapRequestPayload] CreateUserDTO $dto): JsonResponse
    {
        $responseDTO = $this->registerUserUseCase->execute($dto);

        return $this->json($responseDTO, 201);
    }
}
