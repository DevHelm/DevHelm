<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity]
#[ORM\Table('agent')]
class Agent
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private $id;

    #[ORM\ManyToOne(targetEntity: Lead::class, inversedBy: 'agents')]
    #[ORM\JoinColumn(name: 'lead_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Lead $lead = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(name: 'github_profile', type: 'string', length: 255, nullable: true)]
    private ?string $githubProfile = null;

    #[ORM\Column(name: 'jira_profile', type: 'string', length: 255, nullable: true)]
    private ?string $jiraProfile = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'inactive';

    #[ORM\Column(name: 'last_seen', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastSeen = null;

    #[ORM\Column(name: 'server_address', type: 'string', length: 255, nullable: true)]
    private ?string $serverAddress = null;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: ApiKey::class, cascade: ['persist'], orphanRemoval: false)]
    private Collection $apiKeys;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: Task::class)]
    private Collection $tasks;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct()
    {
        $this->apiKeys = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }

    public function getId(): string
    {
        return (string) $this->id;
    }

    public function getLead(): ?Lead
    {
        return $this->lead;
    }

    public function setLead(?Lead $lead): self
    {
        $this->lead = $lead;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getGithubProfile(): ?string
    {
        return $this->githubProfile;
    }

    public function setGithubProfile(?string $githubProfile): self
    {
        $this->githubProfile = $githubProfile;

        return $this;
    }

    public function getJiraProfile(): ?string
    {
        return $this->jiraProfile;
    }

    public function setJiraProfile(?string $jiraProfile): self
    {
        $this->jiraProfile = $jiraProfile;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLastSeen(): ?\DateTimeImmutable
    {
        return $this->lastSeen;
    }

    public function setLastSeen(?\DateTimeImmutable $lastSeen): self
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    public function getServerAddress(): ?string
    {
        return $this->serverAddress;
    }

    public function setServerAddress(?string $serverAddress): self
    {
        $this->serverAddress = $serverAddress;

        return $this;
    }

    /** @return ApiKey[] */
    public function getApiKeys(): array
    {
        return $this->apiKeys->toArray();
    }

    public function addApiKey(ApiKey $apiKey): self
    {
        if (!$this->apiKeys->contains($apiKey)) {
            $this->apiKeys->add($apiKey);
            $apiKey->setAgent($this);
        }

        return $this;
    }

    /** @return Task[] */
    public function getTasks(): array
    {
        return $this->tasks->toArray();
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setAgent($this);
        }

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable('now');
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable('now');
    }
}