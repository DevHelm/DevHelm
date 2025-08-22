<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity]
#[ORM\Table('task')]
#[ORM\HasLifecycleCallbacks]
class Task
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private $id;

    #[ORM\ManyToOne(targetEntity: Agent::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Agent $agent;

    #[ORM\Column(name: 'jira_ticket_id', type: 'string', length: 255)]
    private string $jiraTicketId;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'open';

    #[ORM\Column(name: 'github_pull_request_id', type: 'string', length: 255, nullable: true)]
    private ?string $githubPullRequestId = null;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: Feedback::class, cascade: ['persist'])]
    private Collection $feedbackItems;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: Question::class, cascade: ['persist'])]
    private Collection $questions;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct()
    {
        $this->feedbackItems = new ArrayCollection();
        $this->questions = new ArrayCollection();
    }

    public function getId(): string
    {
        return (string) $this->id;
    }

    public function getAgent(): Agent
    {
        return $this->agent;
    }

    public function setAgent(Agent $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getJiraTicketId(): string
    {
        return $this->jiraTicketId;
    }

    public function setJiraTicketId(string $jiraTicketId): self
    {
        $this->jiraTicketId = $jiraTicketId;

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

    public function getGithubPullRequestId(): ?string
    {
        return $this->githubPullRequestId;
    }

    public function setGithubPullRequestId(?string $githubPullRequestId): self
    {
        $this->githubPullRequestId = $githubPullRequestId;

        return $this;
    }

    /** @return Feedback[] */
    public function getFeedbackItems(): array
    {
        return $this->feedbackItems->toArray();
    }

    public function addFeedback(Feedback $feedback): self
    {
        if (!$this->feedbackItems->contains($feedback)) {
            $this->feedbackItems->add($feedback);
            $feedback->setTask($this);
        }

        return $this;
    }

    /** @return Question[] */
    public function getQuestions(): array
    {
        return $this->questions->toArray();
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setTask($this);
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
