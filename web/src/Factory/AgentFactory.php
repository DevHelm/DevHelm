<?php

namespace App\Factory;

use App\Dto\App\Request\CreateAgentDto;
use App\Entity\Agent;
use App\Entity\Team;
use App\Enum\AgentStatus;

class AgentFactory
{
    public function createFromDto(CreateAgentDto $dto, Team $team): Agent
    {
        $agent = new Agent();
        $agent->setName($dto->name);
        $agent->setProject($dto->project);
        $agent->setTeam($team);
        $agent->setStatus(AgentStatus::Enabled);
        $agent->setCreatedAt(new \DateTimeImmutable());
        $agent->setUpdatedAt(new \DateTimeImmutable());

        return $agent;
    }
}
