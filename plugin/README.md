ComControl IntelliJ Plugin (Skeleton)
====================================

This directory contains a minimal skeleton for a JetBrains IntelliJ Platform plugin named "ComControl".

How to build and run
- Install JDK 17
- From this directory, run:
  - Using the Gradle Wrapper (recommended):
    - ./gradlew build
    - ./gradlew runIde
  - Or using a system Gradle 8.x:
    - gradle build
    - gradle runIde

Structure
- build.gradle.kts, settings.gradle.kts, gradle.properties – Gradle project setup.
- src/main/resources/META-INF/plugin.xml – Plugin descriptor (id, name, actions).
- src/main/kotlin/com/comcontrol/ide/actions/SayHelloAction.kt – Example action added to Tools menu.
- src/main/resources/META-INF/pluginIcon.svg – Simple plugin icon.

Notes
- If the wrapper JAR is missing (gradle/wrapper/gradle-wrapper.jar), run `gradle wrapper --gradle-version 8.7` once to regenerate it, or commit the JAR if preferred.
- You can customize platformVersion in gradle.properties to target different IDE versions.
- Update plugin metadata (vendor, description) in plugin.xml.
