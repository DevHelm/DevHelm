<?php

namespace DevHelm\Control\Factory;

use DevHelm\Control\Dto\App\Request\CreateAgentDto;

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
