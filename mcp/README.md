# DevHelm MCP Server (Go)

This is a minimal MCP server built with Go using the `github.com/mark3labs/mcp-go` library.

It exposes a single tool:
- `report_done`: accepts a required input `jira_ticket` and performs a POST request to a remote endpoint, returning the `title` from the response.

Implementation details:
- The tool performs an HTTP POST to a configurable endpoint and extracts `title` from the JSON payload.
- By default, it uses `https://jsonplaceholder.typicode.com/todos/1` if not configured otherwise.

## Environment Variables

The MCP server supports the following environment variables:

- `BASE_URL`: The base URL for API requests (e.g., `https://control.devhelm.ai`). Defaults to `https://jsonplaceholder.typicode.com` if not set.
- `API_KEY`: The API key to include in the `X-API-KEY` header of requests. No header is added if not set.

## Requirements
- Go 1.22+
- Docker (optional, for containerized build)

## Build & Run (Local)

```bash
cd mcp
# Download dependencies
go mod download

# Build the server
go build -o mcp-server

# Run the server (stdio transport)
./mcp-server
```

The server communicates over stdio (stdin/stdout) as typical for MCP transports. Integrate it with your MCP client by configuring a stdio command pointing to the compiled binary.

## Docker

Build the image:

```bash
# From repository root
docker build -f mcp/Dockerfile -t comcontrol-mcp:latest .
```

Run the container:

```bash
# Basic run
docker run --rm -i comcontrol-mcp:latest

# Run with environment variables
docker run --rm -i \
  -e BASE_URL=https://control.devhelm.ai \
  -e API_KEY=your_api_key_here \
  comcontrol-mcp:latest
```

Use `-i` to keep stdin open for stdio-based communication and `-e` to set environment variables.

## GitHub Actions
A CI workflow is provided at `.github/workflows/mcp-go.yml` which builds and vets the Go module when changes under `mcp/` are pushed or a PR is opened.

## Tool Schema
Input example for `report_done`:

```json
{
  "jira_ticket": "PROJ-123"
}
```

Output example:

```json
{
  "jira_ticket": "PROJ-123",
  "title": "delectus aut autem"
}
```

## Notes
- The remote JSONPlaceholder endpoint is for demonstration only.
- Networking must be allowed in the environment for the HTTP call to succeed.
