<?php

namespace App\Context\User\Domain\Repository;

use App\Context\User\Domain\Entity\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\DBAL\LockMode;

interface UserRepositoryInterface
{
    public function add(User $entity, bool $flush = false): void;
    public function remove(User $entity, bool $flush = false): void;

    /**
     * @return User|null
     */
    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?object;

    /**
     * @return User|null
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?object;
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void;
}
