<?php

namespace App\Factory;

use App\Dto\App\Request\CreateAgentDto;
use App\Dto\App\Response\AgentListResponseDto;
use App\Dto\App\Response\AgentResponseDto;
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

    public function createAgentResponseDto(Agent $agent): AgentResponseDto
    {
        return new AgentResponseDto(
            id: (string) $agent->getId(),
            name: $agent->getName(),
            project: $agent->getProject(),
            team_id: (string) $agent->getTeam()->getId(),
            created_at: $agent->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }

    /**
     * Create a list response DTO from an array of AgentResponseDto objects.
     *
     * @param AgentResponseDto[] $agentResponseDtos
     */
    public function createAgentListResponseDto(array $agentResponseDtos, bool $hasMore = false, ?string $lastKey = null): AgentListResponseDto
    {
        return new AgentListResponseDto(
            data: $agentResponseDtos,
            has_more: $hasMore,
            last_key: $lastKey,
        );
    }
}
