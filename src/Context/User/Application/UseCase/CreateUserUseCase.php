<?php

namespace App\Context\User\Application\UseCase;

use App\Context\User\Application\DTO\CreateUserDTO;
use App\Context\User\Application\DTO\UserResponseDTO;
use App\Context\User\Domain\Entity\User;
use App\Context\User\Infrastructure\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserUseCase
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function execute(CreateUserDTO $dto): UserResponseDTO
    {
        $user = new User();
        $user->setEmail($dto->email);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        $this->userRepository->add($user, true);

        return new UserResponseDTO($user);
    }
}
