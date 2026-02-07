<?php

namespace App\Context\User\Domain\Repository;

use App\Context\User\Domain\Entity\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

interface UserRepositoryInterface
{
    public function add(User $entity, bool $flush = false): void;
    public function remove(User $entity, bool $flush = false): void;
    public function find($id, $lockMode = null, $lockVersion = null): ?User;
    public function findOneBy(array $criteria, array $orderBy = null): ?User;
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void;
}
