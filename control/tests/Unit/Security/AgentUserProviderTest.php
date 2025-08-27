<?php

namespace Test\DevHelm\Control\Unit\Security;

use DevHelm\Control\Entity\Agent;
use DevHelm\Control\Entity\ApiKey;
use DevHelm\Control\Enum\AgentStatus;
use DevHelm\Control\Repository\AgentRepositoryInterface;
use DevHelm\Control\Repository\ApiKeyRepositoryInterface;
use DevHelm\Control\Security\AgentUser;
use DevHelm\Control\Security\AgentUserProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class AgentUserProviderTest extends TestCase
{
    private AgentUserProvider $agentUserProvider;
    private MockObject|AgentRepositoryInterface $agentRepository;
    private MockObject|ApiKeyRepositoryInterface $apiKeyRepository;

    protected function setUp(): void
    {
        $this->agentRepository = $this->createMock(AgentRepositoryInterface::class);
        $this->apiKeyRepository = $this->createMock(ApiKeyRepositoryInterface::class);

        $this->agentUserProvider = new AgentUserProvider(
            $this->agentRepository,
            $this->apiKeyRepository
        );
    }

    public function testLoadUserByApiKeyWithValidKey(): void
    {
        // Create mock objects
        $apiKey = $this->createMock(ApiKey::class);
        $agent = $this->createMock(Agent::class);
        $agent->method('getStatus')
            ->willReturn(AgentStatus::Enabled);

        $apiKey->method('getAgent')
            ->willReturn($agent);

        $this->apiKeyRepository->method('findEnabledByKey')
            ->with('valid-api-key')
            ->willReturn($apiKey);

        // Call the method under test
        $result = $this->agentUserProvider->loadUserByApiKey('valid-api-key');

        // Assertions
        $this->assertInstanceOf(AgentUser::class, $result);
        $this->assertSame($agent, $result->getAgent());
    }

    public function testLoadUserByApiKeyWithInvalidKey(): void
    {
        // Configure mock to return null (no API key found)
        $this->apiKeyRepository->method('findEnabledByKey')
            ->with('invalid-api-key')
            ->willReturn(null);

        // Expect exception
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('API Key not found or expired');

        // Call the method under test
        $this->agentUserProvider->loadUserByApiKey('invalid-api-key');
    }

    public function testLoadUserByIdentifier(): void
    {
        // Create mock objects
        $apiKey = $this->createMock(ApiKey::class);
        $agent = new Agent();
        $agent->setStatus(AgentStatus::Enabled);
        $this->apiKeyRepository->method('findEnabledByKey')
            ->with('valid-api-key')
            ->willReturn($apiKey);

        // Call the method under test - should delegate to loadUserByApiKey
        $result = $this->agentUserProvider->loadUserByIdentifier('valid-api-key');

        // Assertions
        $this->assertInstanceOf(AgentUser::class, $result);
    }

    public function testRefreshUserWithValidUser(): void
    {
        // Create mock objects
        $agent = $this->createMock(Agent::class);
        $agentUser = $this->createMock(AgentUser::class);

        // Configure mocks
        $agentUser->method('getAgent')
            ->willReturn($agent);

        $agent->method('getId')
            ->willReturn('agent-id-123');

        $this->agentRepository->method('findById')
            ->with('agent-id-123')
            ->willReturn($agent);

        // Call the method under test
        $result = $this->agentUserProvider->refreshUser($agentUser);

        // Assertions
        $this->assertInstanceOf(AgentUser::class, $result);
    }

    public function testRefreshUserWithInvalidUserClass(): void
    {
        // Create a mock of a different user interface
        $invalidUser = $this->createMock(UserInterface::class);

        // Expect exception
        $this->expectException(UnsupportedUserException::class);

        // Call the method under test
        $this->agentUserProvider->refreshUser($invalidUser);
    }

    public function testRefreshUserWithNonExistentAgent(): void
    {
        // Create mock objects
        $agent = $this->createMock(Agent::class);
        $agentUser = $this->createMock(AgentUser::class);

        // Configure mocks
        $agentUser->method('getAgent')
            ->willReturn($agent);

        $agent->method('getId')
            ->willReturn('non-existent-id');

        $this->agentRepository->method('findById')
            ->with('non-existent-id')
            ->willReturn(null);

        // Expect exception
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Agent no longer exists');

        // Call the method under test
        $this->agentUserProvider->refreshUser($agentUser);
    }

    public function testSupportsClass(): void
    {
        // Should support the AgentUser class
        $this->assertTrue($this->agentUserProvider->supportsClass(AgentUser::class));

        // Should not support other classes
        $this->assertFalse($this->agentUserProvider->supportsClass(UserInterface::class));
        $this->assertFalse($this->agentUserProvider->supportsClass(Agent::class));
    }
}
