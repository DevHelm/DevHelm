ComControl
==========

ComControl is an attempt to create a command-and-control system for Junie and JetBrains IDEs.

Repository layout
- plugin/ — IntelliJ Platform plugin for ComControl (skeleton). See plugin/README.md for build and run instructions.
- web/ — Web application (Symfony/PHP) and related assets.
- mcp/ — MCP-related components (if applicable to your setup).

Getting started — Plugin
1) Requirements
   - JDK 17
2) Build and run inside plugin directory:
   - cd plugin
   - ./gradlew build
   - ./gradlew runIde
   - (If the wrapper JAR is missing, run `gradle wrapper --gradle-version 8.7` once to regenerate it, or commit the JAR.)

Notes
- The plugin is currently a minimal skeleton that adds a "ComControl: Say Hello" action in the Tools menu.
- If you prefer, we can add the Gradle Wrapper to plugin/ so you don’t need a system Gradle.

Getting started — Web (Symfony)
- Standard PHP/Symfony setup applies. You’ll need PHP, Composer, and the usual Symfony stack. Consult web/ for configuration, Docker setup, and tests.

Next steps
- Expand the IntelliJ plugin to communicate with the ComControl backend.
- Add Gradle Wrapper to plugin/ for reproducible builds.
- Define APIs/protocols between IDE and backend.
