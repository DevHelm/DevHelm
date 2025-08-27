<?php

namespace Test\DevHelm\Control\Unit\Service;

use DevHelm\Control\Entity\Agent;
use DevHelm\Control\Entity\ApiKey;
use DevHelm\Control\Repository\ApiKeyRepositoryInterface;
use DevHelm\Control\Service\ApiKeyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApiKeyGeneratorTest extends TestCase
{
    private ApiKeyGenerator $apiKeyGenerator;
    private MockObject|ApiKeyRepositoryInterface $apiKeyRepository;

    protected function setUp(): void
    {
        // Create a mock for the API key repository
        $this->apiKeyRepository = $this->createMock(ApiKeyRepositoryInterface::class);

        // Create the service with the mocked repository
        $this->apiKeyGenerator = new ApiKeyGenerator($this->apiKeyRepository);
    }

    public function testGenerateForAgent(): void
    {
        // Create a mock Agent
        $agent = $this->createMock(Agent::class);

        // The agent should expect addApiKey to be called once with an ApiKey instance
        $agent->expects($this->once())
            ->method('addApiKey')
            ->with($this->isInstanceOf(ApiKey::class));

        // The repository should expect save to be called once with an ApiKey instance
        $this->apiKeyRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(ApiKey::class));

        // Call the method under test
        $apiKey = $this->apiKeyGenerator->generateForAgent($agent);

        // Assert that the returned object is an ApiKey
        $this->assertInstanceOf(ApiKey::class, $apiKey);

        // Assert that the API key is a 64-character hexadecimal string (32 bytes in hex)
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $apiKey->getKey());

        // Assert that the agent was set correctly on the API key
        $this->assertSame($agent, $apiKey->getAgent());
    }
}
