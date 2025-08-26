<?php

namespace App\Security;

use App\Entity\Agent;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Represents an Agent as a Symfony User for authentication.
 */
class AgentUser implements UserInterface
{
    private Agent $agent;

    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }

    /**
     * Get the Agent entity this user represents.
     */
    public function getAgent(): Agent
    {
        return $this->agent;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        // Agents get a basic role for API access
        return ['ROLE_API_USER'];
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // API key authentication doesn't store credentials in the user
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdentifier(): string
    {
        // Use the agent ID as the identifier
        return $this->agent->getId();
    }
}