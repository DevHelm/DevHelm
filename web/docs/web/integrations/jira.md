# Jira Integration

This document describes how to configure and use the Jira integration in the DevHelm web application.

## Overview

The Jira integration provides functionality to fetch the next available issue from a Jira project. It uses the `lesstif/jira-cloud-restapi` library to communicate with Jira Cloud instances.

## Configuration

### Environment Variables

Add the following environment variables to your `.env` file or environment configuration:

```env
JIRA_HOST=your-jira-instance.atlassian.net
JIRA_USERNAME=your-email@example.com
JIRA_PERSONAL_ACCESS_TOKEN=your-personal-access-token
```

#### Getting Your Personal Access Token

1. Log in to your Jira Cloud instance
2. Go to Account Settings > Security > Create and manage API tokens
3. Create a new API token
4. Copy the token and use it as the `JIRA_PERSONAL_ACCESS_TOKEN`

**Note**: Keep your personal access token secure and never commit it to version control.

### Service Configuration

The Jira client is automatically configured in `config/services.yaml`. The configuration includes:

- JiraClient service with environment variable injection
- TicketProviderInterface binding to JiraTicketProvider

## Usage

### Injecting the Ticket Provider

You can inject the `TicketProviderInterface` into your services or controllers:

```php
<?php

namespace App\Controller;

use App\Interface\TicketProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SomeController extends AbstractController
{
    public function __construct(
        private TicketProviderInterface $ticketProvider
    ) {
    }

    public function getNextTicket(string $projectKey): void
    {
        $ticket = $this->ticketProvider->getNext($projectKey);
        
        if ($ticket !== null) {
            // Process the ticket
            echo "Next ticket: " . $ticket->getKey() . " - " . $ticket->getSummary();
        } else {
            echo "No tickets available";
        }
    }
}
```

### Ticket Value Object

The `getNext()` method returns a `Ticket` value object with the following properties:

- `getKey()`: The Jira issue key (e.g., "PROJ-123")
- `getSummary()`: The issue summary/title
- `getDescription()`: The issue description (nullable)
- `getStatus()`: The current status (nullable)
- `getPriority()`: The priority level (nullable)
- `getAssignee()`: The assignee display name (nullable)
- `getReporter()`: The reporter display name (nullable)
- `getLabels()`: Array of labels
- `getCreated()`: Creation date (nullable)
- `getUpdated()`: Last update date (nullable)

### Query Logic

The `JiraTicketProvider` searches for issues using the following JQL query:

```jql
project = "{PROJECT}" AND status != "Done" AND status != "Resolved" ORDER BY created ASC
```

This returns the oldest unresolved issue in the specified project.

## Architecture

### Components

1. **TicketProviderInterface**: Defines the contract for ticket providers
2. **Ticket**: Value object representing a Jira issue
3. **JiraTicketProvider**: Implementation that fetches issues from Jira
4. **JiraClient**: Configured service for Jira API communication

### Dependencies

- `lesstif/jira-cloud-restapi`: PHP library for Jira Cloud REST API
- `symfony/dependency-injection`: For service container configuration

## Error Handling

The `JiraTicketProvider` includes basic error handling:

- Returns `null` if no issues are found
- Returns `null` if there's an API error or exception
- Catches and silently handles exceptions (consider adding logging in production)

## Testing

When testing locally, you can use a test Jira instance or create mock implementations of the `TicketProviderInterface`.

## Security Considerations

- Never commit your Personal Access Token to version control
- Use environment-specific configuration files (`.env.local`, `.env.prod`, etc.)
- Consider using Symfony's secrets management for production environments
- Regularly rotate your Personal Access Tokens

## Troubleshooting

### Common Issues

1. **Authentication Errors**: Verify your email and personal access token are correct
2. **Host Connection Issues**: Ensure the JIRA_HOST doesn't include `https://` protocol
3. **No Tickets Returned**: Check if there are any unresolved issues in the specified project
4. **Permission Errors**: Ensure your Jira user has read access to the project

### Debugging

Enable Symfony's debug mode and check the logs for detailed error messages when issues occur.