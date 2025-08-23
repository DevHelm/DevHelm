<?php

namespace App\Service;

use App\Interface\TicketProviderInterface;
use App\ValueObject\Ticket;
use LessTif\JiraCloud\Issue\IssueService;
use LessTif\JiraCloud\JiraClient;
use LessTif\JiraCloud\JQL\JqlQuery;

class JiraTicketProvider implements TicketProviderInterface
{
    public function __construct(
        private JiraClient $jiraClient,
    ) {
    }

    public function getNext(string $project): ?Ticket
    {
        try {
            $issueService = new IssueService($this->jiraClient);

            // Search for the next issue in the project that is not resolved
            $jql = new JqlQuery();
            $query = sprintf(
                'project = "%s" AND status != "Done" AND status != "Resolved" ORDER BY created ASC',
                $project
            );
            $jql->setQuery($query);

            $result = $issueService->search($jql, 0, 1);

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
            // Log the error or handle as needed
            // For now, return null if there's an error
            return null;
        }
    }
}
