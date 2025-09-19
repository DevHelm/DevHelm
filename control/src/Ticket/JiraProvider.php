<?php

namespace DevHelm\Control\Ticket;

use JiraCloud\Issue\IssueService;
use JiraCloud\JiraClient;
use Parthenon\Common\LoggerAwareTrait;

class JiraProvider implements ProviderInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private JiraClient $jiraClient,
        private string $targetStatus = 'TODO',
    ) {
    }

    public function getNext(string $project): ?Ticket
    {
        try {
            $issueService = new IssueService($this->jiraClient->getConfiguration());

            // Search for the next issue in the project that is not resolved
            $query = sprintf(
                'project = "%s" AND status != "%s" ORDER BY created ASC',
                $project,
                $this->targetStatus
            );
            $result = $issueService->search($query, 0, 1);

            if (empty($result->issues)) {
                return null;
            }

            $issue = $result->issues[0];

            return new Ticket(
                key: $issue->key,
                summary: $issue->fields->summary,
                description: $issue->fields->description ?? null,
                status: $issue->fields->status->name ?? null,
                priority: $issue->fields->priority->name ?? null,
                assignee: $issue->fields->assignee->displayName ?? null,
                reporter: $issue->fields->reporter->displayName ?? null,
                labels: $issue->fields->labels ?? [],
                created: isset($issue->fields->created) ? new \DateTime($issue->fields->created) : null,
                updated: isset($issue->fields->updated) ? new \DateTime($issue->fields->updated) : null
            );
        } catch (\Exception $e) {
            $this->getLogger()->error('Failed to fetch next issue from JIRA', [
                'project' => $project,
                'targetStatus' => $this->targetStatus,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
