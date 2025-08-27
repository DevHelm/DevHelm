<?php

namespace App\Dto\Api\Response;

readonly class HelloWorldResponseDto
{
    public function __construct(
        public string $hello,
        public ?array $agent = null,
    ) {
    }
}
