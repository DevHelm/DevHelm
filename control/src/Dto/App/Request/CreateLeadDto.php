<?php

namespace DevHelm\Control\Dto\App\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateLeadDto
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

        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Email must be a valid email address')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Email cannot be longer than {{ limit }} characters'
        )]
        public string $email,
    ) {
    }
}
