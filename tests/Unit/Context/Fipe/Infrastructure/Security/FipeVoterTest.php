<?php

namespace App\Tests\Unit\Context\Fipe\Infrastructure\Security;

use App\Context\Fipe\Infrastructure\Security\FipeVoter;
use App\Context\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class FipeVoterTest extends TestCase
{
    private FipeVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new FipeVoter();
    }

    public function testAdminCanManageFipe(): void
    {
        $admin = $this->createUser(['ROLE_ADMIN']);
        $token = $this->createToken($admin);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, null, [FipeVoter::MANAGE])
        );
    }

    public function testRegularUserCannotManageFipe(): void
    {
        $user = $this->createUser(['ROLE_USER']);
        $token = $this->createToken($user);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, null, [FipeVoter::MANAGE])
        );
    }

    public function testAnonymousUserCannotManageFipe(): void
    {
        $token = $this->createToken(null);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, null, [FipeVoter::MANAGE])
        );
    }

    private function createUser(array $roles): User
    {
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setPassword('hashed');
        $user->setRoles($roles);

        return $user;
    }

    private function createToken(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }
}
