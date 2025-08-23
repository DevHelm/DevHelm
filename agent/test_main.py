"""
Basic tests for the DevHelm Agent.

This file contains basic tests to ensure the agent functions correctly.
"""

import pytest
import sys
import os

# Add the agent directory to the Python path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

# Import modules to test
import main
import ui_interaction


def test_imports():
    """Test that all modules can be imported successfully."""
    assert main is not None
    assert ui_interaction is not None


def test_main_module_exists():
    """Test that main module has expected functions."""
    assert hasattr(main, 'main'), "main module should have a main() function"


def test_ui_interaction_module_exists():
    """Test that ui_interaction module has expected classes/functions."""
    # Basic test to ensure the module loads
    assert ui_interaction is not None


if __name__ == "__main__":
    pytest.main([__file__])