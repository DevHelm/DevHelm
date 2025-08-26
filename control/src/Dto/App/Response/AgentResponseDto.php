<?php

namespace DevHelm\Control\Dto\App\Response;

readonly class AgentResponseDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $project,
        public string $team_id,
        public string $created_at,
    )
    {}
}