<?php

namespace App\Factory;

use App\Dto\App\Request\CreateAgentDto;

class CreateAgentDtoFactory
{
    public function createFromArray(array $data): CreateAgentDto
    {
        return new CreateAgentDto(
            $data['name'] ?? '',
            $data['project'] ?? ''
        );
    }
}