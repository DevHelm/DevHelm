ComControl Development Guidelines
=================================

This document outlines the development guidelines for the ComControl project. It is intended to ensure consistency, maintainability, and quality across the codebase.

This is a monorepo project, meaning all code for the ComControl project is contained within a single repository. This allows for easier management of dependencies and versioning.

## Project Structure

There are three main components in the ComControl project:

* Model Context Provider (MCP) - A server that provides a context for the models used in the ComControl project.
* Web Application - A web-based interface for interacting with the ComControl project and the APIs
* Agent - The agent that is to run on the user's machine to interact with the IntelliJ

The project is structured as follows:

* `mcp/` - Contains the Model Context Provider (MCP) server code
* `web/` - Contains the web application code
* `agent/` - Containers the agent code

## Model Context Provider (MCP)

All the information about the MCP server is contained in the `mcp/` directory and ".junie/mcp". This includes the server code, configuration files, and any related documentation.

## Web Application

All the information about the web application is contained in the `web/` directory and ".junie/web". This includes the web application code, configuration files, and any related documentation.