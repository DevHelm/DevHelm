<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateAgentDto
{
    #[Assert\NotBlank(message: 'Name is required')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Name must be at least {{ limit }} characters long',
        maxMessage: 'Name cannot be longer than {{ limit }} characters'
    )]
    private readonly string $name;

    #[Assert\NotBlank(message: 'Project is required')]
    #[Assert\Length(
        min: 2,
        max: 10,
        minMessage: 'Project must be at least {{ limit }} characters long',
        maxMessage: 'Project cannot be longer than {{ limit }} characters'
    )]
    private readonly string $project;

    public function __construct(string $name, string $project)
    {
        $this->name = $name;
        $this->project = $project;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProject(): string
    {
        return $this->project;
    }
}