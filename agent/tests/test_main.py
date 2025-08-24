"""
Basic tests for the DevHelm Agent.

This file contains basic tests to ensure the agent functions correctly.
"""

import pytest
import sys
import os
from unittest.mock import Mock, patch

# Mock pyautogui before importing modules that depend on it
# This prevents the DISPLAY environment variable error in headless environments
mock_pyautogui = Mock()
mock_pyautogui.locateOnScreen = Mock(return_value=None)
mock_pyautogui.write = Mock()
mock_pyautogui.press = Mock()
mock_pyautogui.click = Mock()
mock_pyautogui.center = Mock(return_value=(100, 100))
mock_pyautogui.ImageNotFoundException = Exception

# Apply the mock before importing
sys.modules['pyautogui'] = mock_pyautogui

# Import modules to test (after mocking)
from devhelm_agent import main, ui_interaction


def test_imports():
    """Test that all modules can be imported successfully."""
    assert main is not None
    assert ui_interaction is not None


def test_main_module_exists():
    """Test that main function is available and callable."""
    assert callable(main), "main should be a callable function"


def test_ui_interaction_module_exists():
    """Test that ui_interaction module has expected classes/functions."""
    # Basic test to ensure the module loads
    assert ui_interaction is not None


if __name__ == "__main__":
    pytest.main([__file__])