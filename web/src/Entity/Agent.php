<?php

namespace App\Entity;

use App\Enum\AgentStatus;
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

    /**
     * The team this agent belongs to.
     */
    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'agents')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: false)]
    private Team $team;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    /**
     * The project identifier this agent is associated with.
     */
    #[ORM\Column(type: 'string', length: 10)]
    private string $project;

    #[ORM\Column(name: 'github_profile', type: 'string', length: 255, nullable: true)]
    private ?string $githubProfile = null;

    #[ORM\Column(name: 'jira_profile', type: 'string', length: 255, nullable: true)]
    private ?string $jiraProfile = null;

    #[ORM\Column(type: 'string', enumType: AgentStatus::class)]
    private AgentStatus $status = AgentStatus::Enabled;

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

    public function getStatus(): AgentStatus
    {
        return $this->status;
    }

    public function setStatus(AgentStatus $status): self
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

    /**
     * Get the project identifier.
     *
     * @return string The project identifier
     */
    public function getProject(): string
    {
        return $this->project;
    }

    /**
     * Set the project identifier.
     *
     * @param string $project The project identifier
     */
    public function setProject(string $project): self
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get the team this agent belongs to.
     *
     * @return Team The team entity
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * Set the team this agent belongs to.
     *
     * @param Team $team The team entity
     */
    public function setTeam(Team $team): self
    {
        $this->team = $team;

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

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setTasks(Collection $tasks): void
    {
        $this->tasks = $tasks;
    }

    public function setApiKeys(Collection $apiKeys): void
    {
        $this->apiKeys = $apiKeys;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
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

}
