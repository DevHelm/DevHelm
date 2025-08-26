<?php

namespace App\Dto\Generic;

readonly class ErrorResponseDto
{
    public function __construct(
        public string $error,
        public int $status_code = 400,
    ) { }
}