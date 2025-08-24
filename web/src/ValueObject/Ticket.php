<?php

namespace App\ValueObject;

class Ticket
{
    public function __construct(
        private string $key,
        private string $summary,
        private ?string $description = null,
        private ?string $status = null,
        private ?string $priority = null,
        private ?string $assignee = null,
        private ?string $reporter = null,
        private array $labels = [],
        private ?\DateTimeInterface $created = null,
        private ?\DateTimeInterface $updated = null,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function getAssignee(): ?string
    {
        return $this->assignee;
    }

    public function getReporter(): ?string
    {
        return $this->reporter;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }
}
