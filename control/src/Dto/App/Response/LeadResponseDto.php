<?php

namespace DevHelm\Control\Dto\App\Response;

readonly class LeadResponseDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $created_at,
    ) {
    }
}
