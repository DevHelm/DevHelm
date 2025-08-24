<?php

namespace App\Dto\App\Response;

readonly class AgentListResponseDto
{
    /**
     * @param AgentResponseDto[] $data
     */
    public function __construct(
        public array $data,
        public bool $has_more,
        public ?string $last_key,
    ) {
    }
}