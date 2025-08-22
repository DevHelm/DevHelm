package com.comcontrol.ide.actions

import com.intellij.openapi.actionSystem.AnAction
import com.intellij.openapi.actionSystem.AnActionEvent
import com.intellij.openapi.ui.Messages

class SayHelloAction : AnAction() {
    override fun actionPerformed(e: AnActionEvent) {
        val project = e.project
        Messages.showInfoMessage(project, "Hello from ComControl plugin!", "ComControl")
    }
}
