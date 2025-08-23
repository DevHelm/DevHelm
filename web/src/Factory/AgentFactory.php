<?php

namespace App\Factory;

use App\Dto\CreateAgentDto;
use App\Entity\Agent;
use App\Entity\Team;

class AgentFactory
{
    public function createFromDto(CreateAgentDto $dto, Team $team): Agent
    {
        return Agent::createForTeam(
            $dto->getName(),
            $dto->getProject(),
            $team
        );
    }
}