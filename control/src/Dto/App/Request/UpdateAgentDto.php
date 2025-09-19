<?php

namespace DevHelm\Control\Dto\App\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateAgentDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Name is required')]
        #[Assert\Length(
            min: 2,
            max: 255,
            minMessage: 'Name must be at least {{ limit }} characters long',
            maxMessage: 'Name cannot be longer than {{ limit }} characters'
        )]
        public string $name,

        #[Assert\NotBlank(message: 'Project is required')]
        #[Assert\Length(
            min: 2,
            max: 10,
            minMessage: 'Project must be at least {{ limit }} characters long',
            maxMessage: 'Project cannot be longer than {{ limit }} characters'
        )]
        public string $project,
    ) {
    }
}
