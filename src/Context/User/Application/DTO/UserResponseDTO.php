<?php

namespace App\Context\User\Application\DTO;

use App\Context\User\Domain\Entity\User;

class UserResponseDTO
{
    public readonly int $id;
    public readonly string $email;

    public function __construct(User $user)
    {
        $this->id = $user->getId();
        $this->email = $user->getEmail();
    }
}
