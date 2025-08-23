package ai.devhelm.plugins.junie.startup

import com.intellij.notification.NotificationType
import com.intellij.notification.NotificationGroupManager
import com.intellij.openapi.diagnostic.thisLogger
import com.intellij.openapi.project.Project
import com.intellij.openapi.startup.ProjectActivity
import java.time.ZonedDateTime
import java.time.format.DateTimeFormatter

class MyProjectActivity : ProjectActivity {

    override suspend fun execute(project: Project) {
        val finishedAt = ZonedDateTime.now().format(DateTimeFormatter.ISO_OFFSET_DATE_TIME)
        // Primary requirement: log that Junie just finished
        thisLogger().info("Junie just finished for project='${'$'}{project.name}' at ${'$'}finishedAt")

        // Extra work: show a user notification balloon with timestamp
        NotificationGroupManager.getInstance()
            .getNotificationGroup("Junie Notifications")
            .createNotification(
                "Junie finished",
                "Junie just finished for project '${'$'}{project.name}' at ${'$'}finishedAt",
                NotificationType.INFORMATION
            )
            .notify(project)
    }
}