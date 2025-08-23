<p align="center">
  <img width="450px" src="./logo-small.png">
</p>

Helping automate code writing.

- plugin/ — IntelliJ Platform plugin for ComControl (skeleton). See plugin/README.md for build and run instructions.
- web/ — Web application (Symfony/PHP) and related assets.
- mcp/ — Model Context Provider (MCP) server implemented in Go. See mcp/README.md for details.

## MCP subproject

- Location: mcp/
- Purpose: Go-based MCP server that communicates over stdio. It currently exposes a single tool `report_done` which accepts `jira_ticket` and returns a title from a remote JSON endpoint.
- Build: `go -C mcp build -mod=vendor -o bin/comcontrol-mcp`
- Run tests: `go -C mcp test -v`
- Docker: `docker build -f mcp/Dockerfile -t comcontrol-mcp:local .` then `docker run --rm -i comcontrol-mcp:local`
- More docs: see mcp/README.md and .junie/mcp/guidelines.md

You can find the confluence documentation at https://humblyarrogant.atlassian.net/wiki/spaces/devhelm/overview
You can find the JIRA board that is used for the agent at https://humblyarrogant.atlassian.net/jira/software/projects/DH/summary

If you wish to report issues or request features please use GitHub issues on this repository.