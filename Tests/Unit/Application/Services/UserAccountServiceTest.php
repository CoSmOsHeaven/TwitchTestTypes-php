<?php

namespace TwitchAnalytics\Application\Services;

use Mockery;
use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Domain\Exceptions\UserNotFoundException;
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

        $userMock = Mockery::mock(User::class);
        $userMock->shouldReceive('getCreatedAt')->andReturn($createdAt);
        $userMock->shouldReceive('getDisplayName')->andReturn('Ninja');

        $userRepositoryMock = Mockery::mock(ApiUserRepository::class);
        $userRepositoryMock->shouldReceive('findByDisplayName')->with('Ninja')->andReturn($userMock);

        $userAccountService = new UserAccountService($userRepositoryMock);

        $result = $userAccountService->getAccountAge('Ninja');

        $expectedDays = (new \DateTime($createdAt))->diff(new \DateTime())->days;

        $this->assertEquals('Ninja', $result['name']);
        $this->assertEquals($expectedDays, $result['days_since_creation']);
        $this->assertEquals($createdAt, $result['created_at']);
    }
}
