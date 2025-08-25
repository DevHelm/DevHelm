"""
Shared test configuration and fixtures for DevHelm Agent tests.

This module provides pytest fixtures and configuration that are shared
across all test modules.
"""

import sys
from unittest.mock import Mock
import pytest


@pytest.fixture(scope="session", autouse=True)
def mock_pyautogui():
    """
    Mock pyautogui module to prevent DISPLAY environment variable errors
    in headless test environments.
    
    This fixture automatically applies to all tests in the session.
    """
    # Create mock pyautogui module
    mock_pyautogui = Mock()
    mock_pyautogui.locateOnScreen = Mock(return_value=None)
    mock_pyautogui.write = Mock()
    mock_pyautogui.press = Mock()
    mock_pyautogui.click = Mock()
    mock_pyautogui.center = Mock(return_value=(100, 100))
    mock_pyautogui.ImageNotFoundException = Exception
    
    # Apply the mock to sys.modules
    sys.modules['pyautogui'] = mock_pyautogui
    
    return mock_pyautogui


@pytest.fixture
def sample_task_data():
    """
    Provide sample task data for testing.
    
    Returns:
        dict: Sample task data with all required fields
    """
    return {
        "id": "123e4567-e89b-12d3-a456-426614174000",
        "ticket_id": "DH-123", 
        "prompt": "Work on ticket DH-123"
    }


@pytest.fixture
def task_requester():
    """
    Provide a TaskRequester instance for testing.
    
    Returns:
        TaskRequester: Configured TaskRequester instance
    """
    from devhelm_agent.task_requester import TaskRequester
    return TaskRequester(
        base_url="https://api.devhelm.example.com",
        api_key="test-api-key-123"
    )


@pytest.fixture
def mock_config():
    """
    Provide a mock configuration object for testing.
    
    Returns:
        Mock: Mock configuration with common test values
    """
    config = Mock()
    config.api_url = "https://api.devhelm.example.com"
    config.api_key = "test-api-key-123"
    config.log_format = "pretty"
    config.log_file = ""
    return config