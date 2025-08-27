<?php

namespace Test\DevHelm\Control\Unit\Security;

use DevHelm\Control\Entity\Agent;
use DevHelm\Control\Security\AgentUser;
use PHPUnit\Framework\TestCase;

class AgentUserTest extends TestCase
{
    private Agent $agent;
    private AgentUser $agentUser;

    protected function setUp(): void
    {
        // Create a mock Agent
        $this->agent = $this->createMock(Agent::class);

        // Mock getId method
        $this->agent->method('getId')
            ->willReturn('agent-uuid-123');

        // Create the AgentUser with the mocked agent
        $this->agentUser = new AgentUser($this->agent);
    }

    public function testGetAgent(): void
    {
        // Test that getAgent returns the agent passed in constructor
        $this->assertSame($this->agent, $this->agentUser->getAgent());
    }

    public function testGetRoles(): void
    {
        // Test that the agent has the API user role
        $roles = $this->agentUser->getRoles();
        $this->assertIsArray($roles);
        $this->assertContains('ROLE_API_USER', $roles);
    }

    public function testEraseCredentials(): void
    {
        // This method should not change any state
        $this->agentUser->eraseCredentials();

        // We can still get the agent
        $this->assertSame($this->agent, $this->agentUser->getAgent());
    }

    public function testGetUserIdentifier(): void
    {
        // The user identifier should be the agent ID
        $this->assertEquals('agent-uuid-123', $this->agentUser->getUserIdentifier());
    }
}
