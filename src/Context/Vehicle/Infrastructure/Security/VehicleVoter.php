<?php

namespace App\Context\Vehicle\Infrastructure\Security;

use App\Context\User\Domain\Entity\User;
use App\Context\Vehicle\Domain\Entity\Vehicle;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VehicleVoter extends Voter
{
    public const CREATE = 'VEHICLE_CREATE';
    public const EDIT = 'VEHICLE_EDIT';
    public const DELETE = 'VEHICLE_DELETE';
    public const VIEW = 'VEHICLE_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::CREATE, self::EDIT, self::DELETE, self::VIEW])) {
            return false;
        }

        if ($attribute === self::CREATE) {
            return true;
        }

        return $subject instanceof Vehicle;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // Admin tem acesso total
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return match ($attribute) {
            self::CREATE => true,
            self::VIEW => true,
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    private function canEdit(Vehicle $vehicle, User $user): bool
    {
        return $vehicle->getUser() !== null && $vehicle->getUser()->getId() === $user->getId();
    }

    private function canDelete(Vehicle $vehicle, User $user): bool
    {
        return $vehicle->getUser() !== null && $vehicle->getUser()->getId() === $user->getId();
    }
}
