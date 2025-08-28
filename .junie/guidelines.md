DevHelm Development Guidelines
==============================

This document outlines the development guidelines for the DevHelm project. It is intended to ensure consistency, maintainability, and quality across the codebase.

This is a monorepo project, meaning all code for the DevHelm project is contained within a single repository. This allows for easier management of dependencies and versioning.

## Project Structure

There are three main components in the DevHelm project:

* Model Context Provider (MCP) - A server that provides a context for the models used in the DevHelm project.
* Control - A web-based interface for interacting with the DevHelm project and the APIs
* Junie Agent - The agent that is to run on the user's machine to interact with the IntelliJ

The project is structured as follows:

* `mcp/` - Contains the Model Context Provider (MCP) server code - the guidelines can be found in `mcp/guidelines.md`
* `control/` - Contains the web application code - the guidelines can be found in `control/guidelines.md`
* `junie_agent/` - Containers the agent code - the guidelines can be found in `junie_agent/guidelines.md`

## Model Context Provider (MCP)

All the information about the MCP server is contained in the `mcp/` directory and ".junie/mcp". This includes the server code, configuration files, and any related documentation. All the information about the web application is contained in the `web/` directory and ".junie/web". This includes the web application code, configuration files, and any related documentation. YOU MUST FOLLOW THESE WHEN WORKING ON THE MCP PROJECT.

## Web Application

All the information about the web application is contained in the `web/` directory and ".junie/web". This includes the web application code, configuration files, and any related documentation. YOU MUST FOLLOW THESE WHEN WORKING ON THE WEB PROJECT.

# Global Coding Standards

## Logging

With logging the aim is to have a consistent implementation for log levels throughout the project. The following guidelines should be followed:

* Use `DEBUG` level for detailed information, typically of interest only when diagnosing problems.
* Use `INFO` level to confirm that things are working as expected.
* Use `WARNING` level to indicate that something, if it happens a lot it needs fixed.
* Use `ERROR` level to indicate a problem that needs to be fixed.
* Use `CRITICAL` level to indicate something so serious it requires immediate attention.

When handling the difference between WARNING and ERROR, we should use an error log and then if we see that it happens and we don't need it to be an error, it should become a warning. It's easier to downgrade a log level than to notice one that needs to be upgraded. If it's clear when it happens, it doesn't require any work from us, it should be a warning. For example, if a third party service is down, it should be a warning. If we can't process the response from a third party service, it should be an error.





