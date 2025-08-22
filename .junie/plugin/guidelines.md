ComControl IntelliJ Plugin Development Guidelines
=================================================

Scope
-----
This document captures project-specific guidance for developing the IntelliJ Platform plugin in the `plugin/` folder of the ComControl monorepo. It targets advanced contributors already familiar with Gradle, Kotlin, and JetBrains plugin development.

Environment and Tooling
-----------------------
- JDK/Toolchain: The project uses Java Toolchains pinned to Java 17 via Gradle (see `java { toolchain { ... } }` and `javaVersion` in `plugin/gradle.properties`).
  - Toolchain auto-download is enabled via `org.gradle.java.installations.auto-download=true` in `gradle.properties` and the Foojay toolchain resolver plugin is applied in `settings.gradle.kts`. This allows building and testing on machines without a locally preinstalled JDK 17.
- Gradle: 8.7. Wrapper is configured; CI explicitly regenerates wrapper when missing. Local usage:
  - Prefer `./gradlew` when the wrapper JAR exists. If missing, see Wrapper section below.
- Kotlin: 1.9.24, Kotlin style is `official` (see `kotlin.code.style = official`). Kotlin compilation targets the same JVM version as `javaVersion`.
- IntelliJ Gradle Plugin: `org.jetbrains.intellij` 1.17.2.
- IntelliJ Platform: `IC` (Community) with `platformVersion=2024.1` and `pluginSinceBuild=241`.

Build & Configuration
---------------------
Project files of interest:
- `plugin/build.gradle.kts`:
  - Plugins: `java`, `org.jetbrains.kotlin.jvm`, `org.jetbrains.intellij`.
  - Toolchains: Java 17 enforced; Kotlin jvmToolchain aligned.
  - IntelliJ Gradle plugin configured with platform `type` and `version` from `gradle.properties`.
  - `tasks.patchPluginXml` injects `sinceBuild` from properties.
  - `tasks.runIde { autoReloadPlugins = true }` is enabled for a fast dev loop.
  - Minimal test support added: `dependencies { testImplementation(kotlin("test")) }` and `tasks.test { useJUnitPlatform() }`.
- `plugin/gradle.properties`:
  - `pluginGroup`, `pluginName`, `pluginVersion`.
  - Platform compatibility: `pluginSinceBuild=241`, `platformType=IC`, `platformVersion=2024.1`.
  - Toolchain and memory settings: `javaVersion=17`, `org.gradle.java.installations.auto-download=true`, `org.gradle.jvmargs=-Xmx2g`.
- `plugin/settings.gradle.kts`:
  - Applies `org.gradle.toolchains.foojay-resolver-convention` (0.8.0) to resolve JDK distributions for toolchains.

Common build commands (run in `plugin/`):
- Build: `./gradlew build --stacktrace`
- Run IDE sandbox with plugin: `./gradlew runIde`
- Verify plugin metadata/compatibility: `./gradlew verifyPlugin --stacktrace`

Wrapper and JDK Notes
---------------------
- If `gradle/wrapper/gradle-wrapper.jar` is missing, the wrapper cannot run. Options:
  1) Use a system Gradle 8.7 to regenerate:
     - `gradle wrapper --gradle-version 8.7 --distribution-type all`
  2) Temporarily download a Gradle binary and run the same `wrapper` command with `-p plugin`:
     - Example: download from `https://services.gradle.org/distributions/gradle-8.7-bin.zip`, unzip, then run `gradle -p plugin wrapper --gradle-version 8.7 --distribution-type all`.
- On machines without JDK 17, the combination of Gradle toolchains + Foojay resolver plugin auto-downloads the toolchain. No manual JDK installation is required.

Testing
-------
Testing surfaces in two layers for this plugin:
1) Unit tests (Gradle `test`):
   - Framework: `kotlin("test")` with JUnit Platform runner (`useJUnitPlatform()`).
   - Run: `./gradlew test`.
   - Add tests under `plugin/src/test/kotlin/...` using `kotlin.test.*`.

2) Plugin checks:
   - `verifyPlugin`: Performs validations provided by `gradle-intellij-plugin`.
   - UI/functional sanity via IDE sandbox: `./gradlew runIde` and interact with actions defined in `plugin.xml` (e.g., Tools > "ComControl: Say Hello").

Guidelines for adding tests:
- Place test sources under `src/test/kotlin`. Use concise unit tests for pure logic and helper classes. Avoid depending on IDE runtime where not necessary.
- If you need platform-level tests (light fixtures, etc.), consider the IntelliJ testing framework, but keep in mind it increases build time and complexity; for this project’s current scope, lean on unit tests plus manual verification via `runIde`.
- Be aware of the warning from `verifyPlugin` regarding Kotlin stdlib: the IntelliJ Platform provides its own stdlib. Avoid adding explicit stdlib versions that could conflict. Using the Kotlin Gradle plugin + kotlin("test") is acceptable for unit testing.

Demonstrated test run (validated on 2025-08-22 21:35 local time):
- A minimal unit test (kotlin.test) was created under `src/test/kotlin` and executed with `./gradlew test` using a temporary Gradle binary. The build succeeded and the test passed.
- After verification, the temporary test file was removed to keep the repository clean, as requested.

How to reproduce locally:
1) Ensure you’re in `plugin/`.
2) If wrapper JAR is present:
   - `./gradlew test`
   - Otherwise, regenerate wrapper with any Gradle 8.7, or temporarily download and run Gradle with `-p plugin`.
3) If you want to add a quick check, create `src/test/kotlin/com/comcontrol/DemoTest.kt` with:
   - `import kotlin.test.Test; import kotlin.test.assertEquals`
   - `@Test fun demo() { assertEquals(4, 2 + 2) }`
4) Run `./gradlew test` (wrapper) or `gradle -p plugin test` (system/temporary), confirm success.
5) Remove the temporary test file when done.

Adding new tests:
- For each new area of logic, prefer small, deterministic unit tests.
- Keep test runtime fast; avoid calling IntelliJ APIs unless strictly needed.
- If testing plugin.xml wiring, use `verifyPlugin` and manual `runIde` verification. For more advanced scenarios, consider IntelliJ test fixtures (out of current scope).

Running in IDE sandbox (debugging the plugin):
- `./gradlew runIde` launches a sandbox IDE with the plugin installed.
- `runIde { autoReloadPlugins = true }` supports quicker iteration.
- Logs: check the sandbox directory printed by the task for IDE logs when debugging.
- UI Action provided by skeleton: Tools menu > "ComControl: Say Hello" (implemented by `com.comcontrol.ide.actions.SayHelloAction`).

Code Style & Conventions
------------------------
- Kotlin style is `official` (enforced via `kotlin.code.style = official`). Use idiomatic Kotlin; keep plugin classes small and cohesive.
- Maintain compatibility with `pluginSinceBuild` (currently 241). If you bump `platformVersion`, revisit `pluginSinceBuild` accordingly.
- Prefer dependency versions aligned with IntelliJ Platform; avoid adding libraries that clash with bundled platform libs, especially Kotlin stdlib.
- Keep plugin.xml minimal and declarative; add actions, services, or components deliberately. Use Gradle patching (patchPluginXml) for build-range metadata.

CI Integration (reference)
--------------------------
- See `.github/workflows/plugin-ci.yml`:
  - Runs on Ubuntu with JDK 17 (Temurin), caches Gradle, ensures wrapper is present (downloads Gradle 8.7 and runs `gradle wrapper` if needed).
  - Executes `./gradlew build --stacktrace` and `./gradlew verifyPlugin --stacktrace` in `plugin/`.
- When adjusting `gradle.properties` (e.g., `platformVersion`, `pluginSinceBuild`), ensure CI still resolves toolchains and completes both tasks.

Troubleshooting
---------------
- Wrapper missing: regenerate as described in Wrapper section.
- JDK not found: rely on toolchain auto-download + Foojay resolver (already configured). If behind a proxy or with restricted networking, install JDK 17 locally and let Gradle auto-detect.
- Kotlin stdlib conflicts: rely on the Kotlin plugin’s managed stdlib, avoid hard coding stdlib dependencies incompatible with the IDE’s.
- Sandbox issues: delete the sandbox directories (Gradle prints location) to reset state if needed.

Change Log (dev guidelines)
---------------------------
- 2025-08-22: Added minimal test support to Gradle, documented toolchain auto-download and wrapper regeneration, verified a sample test run, then removed the sample test file as per repository hygiene requirements.
