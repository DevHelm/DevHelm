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
- `DevHelm\Control\Helpers\*` - too generic, doesn't indicate purpose
- `DevHelm\Control\Utils\*` - too generic, doesn't indicate domain

**Namespace structure conventions:**
- Use plural forms for collections: `Services`, `ValueObjects`, `Entities`
- Use domain-specific names for interfaces: `Ticket\ProviderInterface` instead of `Interfaces\TicketProviderInterface`
- Group related functionality together under domain namespaces

### Factory Guidelines

Factories should be merged when they serve the same domain concept. For example, `AgentFactory` should handle both entity creation and DTO creation for agents rather than having separate factories.

### Exception Handling

- Symfony interfaces should throw Symfony exceptions
- DevHelm domain code should throw DevHelm exceptions when appropriate
- Do not create custom exceptions unless there's a clear domain-specific need