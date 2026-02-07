<?php

namespace App\Context\User\Application\DTO;

use App\Context\User\Domain\Entity\User;

class UserResponseDTO
{
    public function __construct(
        public int $id,
        public string $email,
        public array $roles
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            email: $user->getEmail(),
            roles: $user->getRoles()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'roles' => $this->roles
        ];
    }
}
