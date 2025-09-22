<?php

namespace DevHelm\Control\Factory;

use DevHelm\Control\Dto\App\Request\CreateLeadDto;
use DevHelm\Control\Dto\App\Response\LeadResponseDto;
use DevHelm\Control\Entity\Lead;

class LeadFactory
{
    public function createEntity(CreateLeadDto $dto): Lead
    {
        $lead = new Lead();
        $lead->setName($dto->name);
        $lead->setEmail($dto->email);

        return $lead;
    }

    public function createLeadResponseDto(Lead $lead): LeadResponseDto
    {
        return new LeadResponseDto(
            id: (string) $lead->getId(),
            name: $lead->getName(),
            email: $lead->getEmail(),
            created_at: $lead->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
