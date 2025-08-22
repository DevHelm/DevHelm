ComControl Development Guidelines
=================================

This document outlines the development guidelines for the ComControl project. It is intended to ensure consistency, maintainability, and quality across the codebase.

This is a monorepo project, meaning all code for the ComControl project is contained within a single repository. This allows for easier management of dependencies and versioning.

## Project Structure

There are three main components in the ComControl project:

* Model Context Provider (MCP) - A server that provides a context for the models used in the ComControl project.
* Web Application - A web-based interface for interacting with the ComControl project and the APIs
* Plugin - An JetBrain's IDE plugin that integrates with the ComControl project to provide a better development experience.

The project is structured as follows:

* `mcp/` - Contains the Model Context Provider (MCP) server code
* `web/` - Contains the web application code
* `plugin/` - Containers the plugin code