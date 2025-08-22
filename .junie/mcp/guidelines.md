Project: comcontrol — MCP app (Go) in ./mcp
Last verified: 2025-08-22

Scope
- This document captures project-specific guidance for building, configuring, testing, and extending the MCP (Model Context Protocol) server implemented in Go under the mcp directory. It assumes an advanced developer familiar with Go, MCP, JSON-RPC over stdio, and containerized builds.

Build and Configuration
1. Go toolchain and module layout
- The Go module for the MCP app is rooted at ./mcp with its own go.mod and vendored dependencies under ./mcp/vendor.
- Run Go commands from the module directory or use go -C mcp ... from the repository root (recommended in CI and scripts).

2. Building the binary
- Local build (using vendored deps):
  - go -C mcp build -mod=vendor -o bin/comcontrol-mcp
- Cross-compilation example:
  - GOOS=linux GOARCH=amd64 go -C mcp build -mod=vendor -o bin/comcontrol-mcp-linux-amd64
- Notes:
  - The project vendors dependencies; prefer -mod=vendor to ensure hermetic builds.
  - The resulting binary speaks MCP over stdio and is intended to be launched by an MCP-compatible host (editor/agent). It does not expose a network port by itself.

3. Docker build
- A Dockerfile exists at mcp/Dockerfile. Typical build and run:
  - docker build -f mcp/Dockerfile -t comcontrol-mcp:local .
  - docker run --rm -i comcontrol-mcp:local  # The containerized server will communicate via stdio; drive it from an MCP client.
- If embedding into a larger system, you’ll likely exec the container and connect stdio to your MCP client.

4. Runtime configuration
- Tooling: The server currently exposes a single tool report_done which accepts jira_ticket (string) and performs an HTTP POST to a remote endpoint, extracting the title from JSON into the tool result.
- Network: The function postAndExtractTitle uses a package-level variable jsonPlaceholderURL (default https://jsonplaceholder.typicode.com/todos/1). For production, keep the default; for testing, override at runtime in tests (see Testing section). If you need runtime configurability outside tests, consider sourcing from env (e.g., MCP_REMOTE_URL) and defaulting to the current value.
- Timeouts: HTTP client timeout is set to 15s; adjust if host runtimes are constrained.

Testing
1. Running tests
- From repository root using Go 1.20+ (for -C):
  - go -C mcp test -v
- Alternatively (inside module directory):
  - cd mcp && go test -v
- When running under CI or constrained networks, avoid external calls by using the override pattern described below.

2. Unit test patterns specific to this repo
- postAndExtractTitle uses jsonPlaceholderURL; it is a package-level var (not a const) to enable tests to redirect outbound calls to a local httptest.Server. Pattern:
  - Start httptest.Server returning a deterministic JSON payload like {"title":"..."}.
  - Save orig := jsonPlaceholderURL; set jsonPlaceholderURL = ts.URL; defer restore.
  - Use a short context deadline (e.g., 2s) to keep tests snappy.
- Example skeleton:
  - ts := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
      w.Header().Set("Content-Type", "application/json")
      w.WriteHeader(http.StatusOK)
      _, _ = w.Write([]byte(`{"title":"Done!"}`))
    }))
    defer ts.Close()
    orig := jsonPlaceholderURL
    jsonPlaceholderURL = ts.URL
    defer func(){ jsonPlaceholderURL = orig }()
    ctx, cancel := context.WithTimeout(context.Background(), 2*time.Second)
    defer cancel()
    got, err := postAndExtractTitle(ctx)
    // assert got == "Done!"
- handleToolCall can be exercised by constructing an mcp.CallToolRequest from the vendored mark3labs/mcp-go package. Do this only if you need integration-level verification; otherwise, target postAndExtractTitle.

3. Adding new tests
- Create *_test.go files under mcp/. Use standard Go testing with net/http/httptest for network interactions.
- Avoid mutating global state without restoring it; always save and defer restore when changing package vars (e.g., jsonPlaceholderURL).
- Place shared test helpers in mcp/internal or mcp/testutil if the test surface grows (add subpackages as needed). Keep them out of the final binary path.

4. Proven working example
- We verified the pattern by temporarily adding a test and running it:
  - go -C mcp test -v
  - Output (abridged):
    === RUN   TestPostAndExtractTitle_WithLocalServer
    --- PASS: TestPostAndExtractTitle_WithLocalServer (0.00s)
    PASS
- After verification, the example test file was removed to keep the repository clean, per instructions.

Additional Development Guidance
1. Extending tools
- Tools are declared via mcp.NewTool in main; the server responds to:
  - initialize → returns capabilities and implementation metadata.
  - tools/list → returns the tool registry.
  - tools/call → dispatches to handleToolCall; currently validates jira_ticket and returns a structured-only result.
- When adding a new tool:
  - Define schema using mcp.WithString/... builders.
  - Add to the tools slice.
  - Extend the tools/call switch to route to your handler(s) (or implement a small registry map[name]func).
  - Keep responses JSON-RPC compliant via the mcp helpers.

2. Error handling and observability
- Network errors are wrapped into a ToolResultError; callers see a structured tool error.
- For unexpected JSON or status codes, errors include the HTTP status and body excerpt for diagnostics.
- stderr logging is minimal and only for malformed JSON input; consider adding structured logs behind a build tag or an env flag (e.g., COMCONTROL_LOG=debug) if you need more insight in dev without polluting stdio used by the MCP transport.

3. Dependency management
- Prefer using the vendored dependencies for deterministic builds (ensure go -C mcp mod vendor if you update deps).
- If changing major versions of mark3labs/mcp-go, verify any API surface changes (InitializeResult, Tool types, JSON-RPC structs) and adjust main accordingly.

4. Code style and static analysis
- Use go fmt ./... and go vet ./... within mcp.
- Recommended (optional): staticcheck ./mcp and govulncheck ./mcp in CI for additional coverage.

5. Versioning
- serverVersion is a constant in main; update on meaningful behavior changes. Keep serverName stable for client identification.

6. Protocol notes
- The server expects to be run under an MCP host over stdio. Do not print non-JSON to stdout; reserve stderr for human-readable diagnostics. Flushing encoded JSON (enc.Encode + out.Flush) is already handled in main.

Quick Commands Reference
- Build: go -C mcp build -mod=vendor -o bin/comcontrol-mcp
- Run tests: go -C mcp test -v
- Docker: docker build -f mcp/Dockerfile -t comcontrol-mcp:local .

End of guidelines.
