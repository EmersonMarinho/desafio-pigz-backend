<?php

namespace App\Context\Fipe\Infrastructure\Security;

use App\Context\User\Domain\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FipeVoter extends Voter
{
    public const MANAGE = 'FIPE_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::MANAGE;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === self::MANAGE) {
            return in_array('ROLE_ADMIN', $user->getRoles());
        }

        return false;
    }
}