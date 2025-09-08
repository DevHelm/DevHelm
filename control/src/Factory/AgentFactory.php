<?php

namespace DevHelm\Control\Factory;

use DevHelm\Control\Dto\App\Request\CreateAgentDto;
use DevHelm\Control\Dto\App\Response\AgentListResponseDto;
use DevHelm\Control\Dto\App\Response\AgentResponseDto;
use DevHelm\Control\Entity\Agent;
use DevHelm\Control\Entity\Team;
use DevHelm\Control\Enum\AgentStatus;

class AgentFactory
{
    public function createEntity(CreateAgentDto $dto, Team $team): Agent
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

    public function createAppDto(array $data): CreateAgentDto
    {
        return new CreateAgentDto(
            $data['name'] ?? '',
            $data['project'] ?? ''
        );
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
