plugins {
    id("java")
    id("org.jetbrains.kotlin.jvm") version "1.9.24"
    id("org.jetbrains.intellij") version "1.17.2"
}

group = providers.gradleProperty("pluginGroup").get()
version = providers.gradleProperty("pluginVersion").get()

repositories {
    mavenCentral()
}

java {
    toolchain {
        languageVersion.set(JavaLanguageVersion.of(providers.gradleProperty("javaVersion").get()))
    }
}

kotlin {
    jvmToolchain(providers.gradleProperty("javaVersion").get().toInt())
}

intellij {
    type.set(providers.gradleProperty("platformType").get())
    version.set(providers.gradleProperty("platformVersion").get())
}

// Patch plugin.xml with dynamic values from gradle.properties
 tasks.patchPluginXml {
    sinceBuild.set(providers.gradleProperty("pluginSinceBuild"))
    // untilBuild left empty by default (compatible with future builds)
}

tasks {
    wrapper {
        gradleVersion = "8.7"
        distributionType = Wrapper.DistributionType.ALL
    }
    compileJava {
        options.encoding = "UTF-8"
    }
    compileKotlin {
        kotlinOptions.jvmTarget = providers.gradleProperty("javaVersion").get()
        kotlinOptions.freeCompilerArgs += listOf("-Xjvm-default=all")
    }
    runIde {
        // Useful defaults; can be customized
        autoReloadPlugins.set(true)
    }
}
