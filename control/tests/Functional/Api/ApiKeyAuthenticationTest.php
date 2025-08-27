<?php

namespace Test\DevHelm\Control\Functional\Api;

use DevHelm\Control\Entity\Agent;
use DevHelm\Control\Entity\ApiKey;
use DevHelm\Control\Entity\Team;
use DevHelm\Control\Enum\AgentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuthenticationTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private string $apiKey;
    private Agent $agent;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        // Create test data
        $this->createTestData();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->cleanupTestData();

        $this->entityManager->close();
        parent::tearDown();
    }

    /**
     * Test accessing the API with a valid API key.
     */
    public function testApiAccessWithValidKey(): void
    {
        // Call the API with a valid key in the header
        $this->client->request(
            'GET',
            '/api/v1/hello-world',
            [],
            [],
            ['HTTP_X-API-KEY' => $this->apiKey]
        );

        // Check response is successful
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Check response content
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('hello', $responseData);
        $this->assertEquals($this->agent->getName(), $responseData['hello'], 'The API should say hello to the agent by name');
        $this->assertArrayHasKey('agent', $responseData);
        $this->assertEquals($this->agent->getId(), $responseData['agent']['id']);
        $this->assertEquals($this->agent->getName(), $responseData['agent']['name']);
    }

    /**
     * Test accessing the API with an invalid API key.
     */
    public function testApiAccessWithInvalidKey(): void
    {
        // Call the API with an invalid key
        $this->client->request(
            'GET',
            '/api/v1/hello-world',
            [],
            [],
            ['HTTP_X-API-KEY' => 'invalid-key-123']
        );

        // Check response is unauthorized
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());

        // Check error message
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertArrayHasKey('message', $responseData);
    }

    /**
     * Test accessing the API without any key.
     */
    public function testApiAccessWithNoKey(): void
    {
        // Call the API without a key
        $this->client->request('GET', '/api/v1/hello-world');

        // Check response is unauthorized
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());

        // Check error message
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertStringContainsString('API key is missing', $responseData['message']);
    }

    /**
     * Create test data needed for the tests.
     */
    private function createTestData(): void
    {
        // Create a team
        $team = new Team();
        $team->setName('Test Team');
        $team->setBillingEmail('test@example.com');

        $this->entityManager->persist($team);

        // Create an agent
        $this->agent = new Agent();
        $this->agent->setName('Test Agent');
        $this->agent->setProject('TEST');
        $this->agent->setTeam($team);
        $this->agent->setStatus(AgentStatus::Enabled);
        $this->agent->setCreatedAt(new \DateTimeImmutable());
        $this->agent->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($this->agent);

        // Create an API key
        $apiKey = new ApiKey();
        $apiKey->setKey('test-api-key-'.uniqid());
        $apiKey->setStatus('active');
        $apiKey->setAgent($this->agent);

        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();

        // Store the API key for use in tests
        $this->apiKey = $apiKey->getKey();
    }

    /**
     * Clean up test data after tests.
     */
    private function cleanupTestData(): void
    {
        // Find and remove the agent (cascade will remove API key)
        $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(['name' => 'Test Agent']);
        if ($agent) {
            $this->entityManager->remove($agent);
        }

        // Find and remove the team
        $team = $this->entityManager->getRepository(Team::class)->findOneBy(['name' => 'Test Team']);
        if ($team) {
            $this->entityManager->remove($team);
        }

        $this->entityManager->flush();
    }
}
