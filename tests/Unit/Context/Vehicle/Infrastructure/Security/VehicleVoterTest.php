<?php

namespace App\Tests\Unit\Context\Vehicle\Infrastructure\Security;

use App\Context\User\Domain\Entity\User;
use App\Context\Vehicle\Domain\Entity\Vehicle;
use App\Context\Vehicle\Infrastructure\Security\VehicleVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class VehicleVoterTest extends TestCase
{
    private VehicleVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new VehicleVoter();
    }

    public function testAdminCanCreateVehicle(): void
    {
        $admin = $this->createUser(1, ['ROLE_ADMIN']);
        $token = $this->createToken($admin);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, null, [VehicleVoter::CREATE])
        );
    }

    public function testRegularUserCanCreateVehicle(): void
    {
        $user = $this->createUser(1, ['ROLE_USER']);
        $token = $this->createToken($user);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, null, [VehicleVoter::CREATE])
        );
    }

    public function testAdminCanEditAnyVehicle(): void
    {
        $admin = $this->createUser(1, ['ROLE_ADMIN']);
        $vehicle = $this->createVehicle(1, $this->createUser(999, ['ROLE_USER']));
        $token = $this->createToken($admin);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $vehicle, [VehicleVoter::EDIT])
        );
    }

    public function testOwnerCanEditOwnVehicle(): void
    {
        $owner = $this->createUser(1, ['ROLE_USER']);
        $vehicle = $this->createVehicle(1, $owner);
        $token = $this->createToken($owner);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $vehicle, [VehicleVoter::EDIT])
        );
    }

    public function testUserCannotEditOtherUserVehicle(): void
    {
        $user = $this->createUser(1, ['ROLE_USER']);
        $otherUser = $this->createUser(2, ['ROLE_USER']);
        $vehicle = $this->createVehicle(1, $otherUser);
        $token = $this->createToken($user);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $vehicle, [VehicleVoter::EDIT])
        );
    }

    public function testOwnerCanDeleteOwnVehicle(): void
    {
        $owner = $this->createUser(1, ['ROLE_USER']);
        $vehicle = $this->createVehicle(1, $owner);
        $token = $this->createToken($owner);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $vehicle, [VehicleVoter::DELETE])
        );
    }

    public function testUserCannotDeleteOtherUserVehicle(): void
    {
        $user = $this->createUser(1, ['ROLE_USER']);
        $otherUser = $this->createUser(2, ['ROLE_USER']);
        $vehicle = $this->createVehicle(1, $otherUser);
        $token = $this->createToken($user);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $vehicle, [VehicleVoter::DELETE])
        );
    }

    public function testUserCanViewVehicle(): void
    {
        $user = $this->createUser(1, ['ROLE_USER']);
        $vehicle = $this->createVehicle(1, $this->createUser(999, ['ROLE_USER']));
        $token = $this->createToken($user);

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $vehicle, [VehicleVoter::VIEW])
        );
    }

    public function testAnonymousUserCannotCreate(): void
    {
        $token = $this->createToken(null);

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, null, [VehicleVoter::CREATE])
        );
    }

    private function createUser(int $id, array $roles): User
    {
        $user = new User();
        $user->setEmail("user{$id}@test.com");
        $user->setPassword('hashed');
        $user->setRoles($roles);

        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, $id);

        return $user;
    }

    private function createVehicle(int $id, User $owner): Vehicle
    {
        $vehicle = new Vehicle();
        $vehicle->setMake('Fiat');
        $vehicle->setModel('Mobi');
        $vehicle->setVersion('Like');
        $vehicle->setKms(0);
        $vehicle->setPrice(50000.0);
        $vehicle->setYearModel(2022);
        $vehicle->setYearFab(2022);
        $vehicle->setColor('Branco');
        $vehicle->setUser($owner);

        $reflection = new \ReflectionClass($vehicle);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($vehicle, $id);

        return $vehicle;
    }

    private function createToken(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }
}
