"""
DevHelm Agent - Screen automation agent for DevHelm platform.

This package provides intelligent automation tools that integrate with the DevHelm 
platform to provide seamless task management and automated interaction with Junie 
(IntelliJ-based AI assistant).
"""

__version__ = "0.1.0"
__author__ = "DevHelm Team"

# Main entry points
from .main import main
from .task_requester import TaskRequester, Task, TaskStatus
from .ui_interaction import UIInteraction
from .logger_factory import LoggerFactory
from .config import get_config

__all__ = [
    "main",
    "TaskRequester", 
    "Task",
    "TaskStatus", 
    "UIInteraction",
    "LoggerFactory",
    "get_config"
]