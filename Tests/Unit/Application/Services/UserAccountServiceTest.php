<?php

namespace TwitchAnalytics\Application\Services;

use Mockery;
use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Controllers\GetUserPlatformAge\UserNameValidator;
use TwitchAnalytics\Domain\Exceptions\UserNotFoundException;
use TwitchAnalytics\Domain\Interfaces\UserRepositoryInterface;
use TwitchAnalytics\Domain\Models\User;
use TwitchAnalytics\Infrastructure\Repositories\ApiUserRepository;

class UserAccountServiceTest extends TestCase
{

    /**
     * @test
     **/
    public function testGetAccountAgeForNonExistingUser() : void
    {
        $apiRepositoryMock = Mockery::mock(ApiUserRepository::class);
        $apiRepositoryMock->allows()->findByDisplayName('ruben')->andReturns(null);
        $userAccountService = new UserAccountService($apiRepositoryMock);

        $this->expectException(UserNotFoundException::class);

        $userAccountService->getAccountAge('ruben');
    }

    /**
     * @test
     **/
    public function testGetAccountAgeForExistingUser(): void
    {
        $createdAt = '2011-11-20T00:00:00Z';

        // Mock del objeto User
        $userMock = Mockery::mock();
        $userMock->shouldReceive('getCreatedAt')->andReturn($createdAt);
        $userMock->shouldReceive('getDisplayName')->andReturn('Ninja');

        // Mock del repositorio
        $userRepositoryMock = Mockery::mock(ApiUserRepository::class);
        $userRepositoryMock->shouldReceive('findByDisplayName')->with('Ninja')->andReturn($userMock);

        // Instanciar el servicio
        $userAccountService = new UserAccountService($userRepositoryMock);

        // Ejecutar
        $result = $userAccountService->getAccountAge('Ninja');

        // Calcular valor esperado
        $expectedDays = (new \DateTime($createdAt))->diff(new \DateTime())->days;

        // Assertions
        $this->assertEquals('Ninja', $result['name']);
        $this->assertEquals($expectedDays, $result['days_since_creation']);
        $this->assertEquals($createdAt, $result['created_at']);
    }
}
