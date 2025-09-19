<?php

namespace DevHelm\Control\Dto\Response;

readonly class ErrorResponseDto
{
    public function __construct(
        public string $error,
        public int $status_code,
    ) {
    }
}
