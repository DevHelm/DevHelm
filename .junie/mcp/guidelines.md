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



Web Application (Symfony) — Project-Specific Guidance
Last verified: 2025-08-22

Scope
- This section documents build/config, testing, and development practices for the Symfony-based web app under ./web aimed at advanced contributors. It mirrors the project’s CI expectations (see .github/workflows/web-php.yml) and highlights repo-specific conventions.

Build and Configuration
1. PHP/Composer toolchain
- PHP: Target 8.4 in CI; locally use >= 8.2 with required extensions: ctype, iconv, mbstring, intl, json.
- Composer: v2. Ensure composer.lock is respected (prefer-dist).
- Working directory: All commands assume you’re in ./web unless noted.

2. Dependency installation
- composer install --no-interaction --no-progress --prefer-dist
- The project relies on Symfony Flex and auto-scripts for cache clear and asset install. APP_ENV=test disables Symfony deprecation helper in CI.

3. Environment configuration
- Copy environment as needed:
  - cp .env .env.local   # customize locally
- Test env variables (used in CI):
  - APP_ENV=test
  - SYMFONY_DEPRECATIONS_HELPER=disabled
- Kernel bootstrap for tests is wired via tests/bootstrap.php and FriendsOfBehat\SymfonyExtension.

4. Routing and controllers
- Routes are attribute-driven. See config/routes.yaml mapping to src/Controller/. The FrontendController maps '/', '/login', '/signup', '/forgot-password[...]', '/confirm-email[...]', '/app[...]' and renders templates/index.html.twig.

5. Assets
- Webpack Encore bundle is present. If/when asset builds are required:
  - yarn install && yarn encore dev   # or: encore production in CI/CD
  - Output is served via public/bundles; template uses Twig to include assets.
  - No asset step is currently run in CI, so runtime must rely on committed/previously-built assets.

Testing
1. Test runners used by CI
- Code style: vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no --allow-risky=no --rules='@Symfony' src
- PHPUnit: vendor/bin/phpunit --colors=always
- Behat: vendor/bin/behat --no-interaction --colors --format=progress

2. Installing test dependencies
- composer install (as above) pulls dev requirements (behat/behat, mink, symfony-extension, php-cs-fixer). PHPUnit is available via transitive dev dependencies; vendor/bin/phpunit is expected to be present after install.

3. Behat configuration
- behat.yml.dist defines contexts:
  - App\Tests\Behat\DemoContext, GeneralContext, UserContext, PlanContext, TeamContext
- tests/bootstrap.php wires Symfony kernel and Dotenv. FriendsOfBehat\SymfonyExtension bootstrap points to this file.
- GeneralContext recreates Doctrine schema before each scenario using Doctrine SchemaTool; no external DB setup is required for features that don’t hit a real database server (in-memory/ephemeral SQLite or configured test DB).

4. Running tests locally
- Ensure PHP extensions and Composer dependencies are installed, then from ./web:
  - APP_ENV=test vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no --allow-risky=no --rules='@Symfony' src
  - APP_ENV=test vendor/bin/phpunit --colors=always
  - APP_ENV=test vendor/bin/behat --no-interaction --colors --format=progress -c behat.yml.dist

5. Adding new PHPUnit tests
- Location: ./web/tests (PSR-4: App\Tests\ maps to tests/ in composer.json). Example layout:
  - tests/Unit/FooTest.php with namespace App\Tests\Unit
- Minimal smoke test example (uses only Symfony runtime):
  - <?php
    declare(strict_types=1);
    namespace App\Tests\Unit;
    use PHPUnit\Framework\TestCase;
    final class SmokeTest extends TestCase {
        public function testTruth(): void { $this->assertTrue(true); }
    }
- Run: vendor/bin/phpunit --colors=always
- If using KernelTestCase, ensure KERNEL_CLASS env is set or Symfony test bootstrap is configured; otherwise use plain TestCase for pure units.

6. Adding new Behat scenarios
- Feature files: ./web/features/** (e.g., features/User/*.feature)
- Contexts: ./web/tests/Behat/**. Register context in behat.yml(.dist) if introducing a new class.
- Running a subset:
  - vendor/bin/behat --suite=default --tags=@fast
  - vendor/bin/behat features/User/Login.feature:12

7. Creating and running a simple demonstration test (repo-specific guidance)
- PHPUnit demo (non-intrusive):
  - Create tests/Unit/SmokeTest.php as shown above.
  - Run: vendor/bin/phpunit --colors=always --filter SmokeTest
  - Remove the file after verifying the setup, to keep the repo clean.
- Behat demo:
  - A working sample is already present (DemoContext + behat.yml.dist). You can add features/Demo/smoke.feature:
    Feature: Simple smoke
      Scenario: GET homepage
        When a demo scenario sends a request to "/"
        Then the response should be received
  - Run: vendor/bin/behat --no-interaction --colors --format=progress --tags=@demo or path to the feature
  - Remove the temporary feature file after verification.

Additional Development and Debugging Tips (web)
1. Coding standards
- Use PHP-CS-Fixer with @Symfony rules (as in CI). For local autofix:
  - vendor/bin/php-cs-fixer fix --using-cache=no --allow-risky=no --rules='@Symfony' src

2. Database and state in tests
- GeneralContext resets schema per scenario. If adding stateful contexts, ensure clean-up to keep scenarios hermetic.
- For Doctrine-related unit tests, consider sqlite:///:memory: with a dedicated test config or use Doctrine TestBundle patterns.

3. HTTP/kernel testing
- Prefer KernelBrowser/HttpKernel for functional tests; for ultra-fast tests, isolate pure domain logic with plain PHPUnit.

4. Environment pitfalls
- Missing extensions (mbstring, intl) will break composer install or runtime. Match CI’s extension set for parity.
- Ensure APP_ENV=test for fastest/quietest runs and to align with behat and phpunit settings.

5. CI parity
- The GitHub Action at .github/workflows/web-php.yml is the authoritative reference for supported PHP version, commands, and extensions. Validate local changes by mirroring those commands.

Proven working examples
- The repository already contains working Behat contexts (DemoContext) that exercise the Symfony kernel on GET "/". CI runs phpunit and behat with the commands above.
- When introducing new tests locally, follow the demo steps, run the commands, then remove any temporary files used for validation.
