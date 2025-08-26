<?php

namespace App\Security;

use App\Entity\Agent;
use Symfony\Component\Security\Core\User\UserInterface;

class AgentUser implements UserInterface
{
    private Agent $agent;

    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }

    public function getAgent(): Agent
    {
        return $this->agent;
    }

    public function getRoles(): array
    {
        return ['ROLE_API_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->agent->getId();
    }
}