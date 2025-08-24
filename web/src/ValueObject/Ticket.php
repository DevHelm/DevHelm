<?php

namespace App\ValueObject;

readonly class Ticket
{
    public function __construct(
        public string $key,
        public string $summary,
        public ?string $description = null,
        public ?string $status = null,
        public ?string $priority = null,
        public ?string $assignee = null,
        public ?string $reporter = null,
        public array $labels = [],
        public ?\DateTimeInterface $created = null,
        public ?\DateTimeInterface $updated = null,
    ) {
    }
}
