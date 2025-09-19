# Control Application Guidelines

This document outlines the development guidelines specific to the control application component of the DevHelm project.

## Code Organization

### Namespace Guidelines

Namespaces must describe the domain aspect of the code, not just technical implementation details. This ensures better organization and maintainability.

**Good examples:**
- `DevHelm\Control\Ticket\*` - for ticket/task related functionality
- `DevHelm\Control\Agent\*` - for agent-related functionality
- `DevHelm\Control\User\*` - for user-related functionality
- `DevHelm\Control\Security\*` - for security-related functionality

**Avoid generic namespaces:**
- `DevHelm\Control\Interfaces\*` - too generic, doesn't indicate domain
- `DevHelm\Control\Services\*` - too generic, classes should be grouped by domain
- `DevHelm\Control\ValueObjects\*` - too generic, classes should be grouped by domain
- `DevHelm\Control\Helpers\*` - too generic, doesn't indicate purpose
- `DevHelm\Control\Utils\*` - too generic, doesn't indicate domain

**Domain-based grouping rule:**
All classes should be grouped by their domain aspect, not by technical implementation pattern. For example:
- `ApiKeyGenerator` belongs in `Security\*` (domain: security/authentication)
- `JiraProvider` belongs in `Ticket\*` (domain: ticket management)
- `Ticket` value object belongs in `Ticket\*` (domain: ticket management)
- `UserManager` belongs in `User\*` (domain: user management)

**Namespace structure conventions:**
- Use domain-specific names for interfaces: `Ticket\ProviderInterface` instead of `Interfaces\TicketProviderInterface`
- Group all functionality by domain first, then by type if needed within the domain
- Avoid ALL technical implementation namespaces like `Services`, `Managers`, `Handlers`, `ValueObjects`, `Entities`
- Every class, regardless of implementation pattern (service, value object, entity, etc.), should be grouped by its business domain

### Factory Guidelines

Factories should be merged when they serve the same domain concept. For example, `AgentFactory` should handle both entity creation and DTO creation for agents rather than having separate factories.

### Exception Handling

- Symfony interfaces should throw Symfony exceptions
- DevHelm domain code should throw DevHelm exceptions when appropriate
- Do not create custom exceptions unless there's a clear domain-specific need