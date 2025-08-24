# DevHelm Agent Development Guidelines

This document provides guidelines for developing and contributing to the DevHelm Agent project. It covers build instructions, testing procedures, and code style recommendations.

## Build and Configuration

### Prerequisites

- Python 3.8 or higher
- pip (Python package manager)

### Setup and Installation

1. **Clone the repository**:
   ```
   git clone https://github.com/devhelm/devhelm-monorepo.git
   cd devhelm-monorepo/agent
   ```

2. **Create a virtual environment** (recommended):
   ```
   python3 -m venv venv
   source venv/bin/activate  # On Windows: venv\Scripts\activate
   ```

3. **Install dependencies**:
   ```
   # Install core dependencies
   pip install -e .
   
   # Install development dependencies
   pip install -e ".[dev]"
   ```

4. **Setup pre-commit hooks**:
   ```
   pre-commit install
   ```

### Configuration

The agent requires configuration for API access and logging. You can configure the agent in several ways:

1. **Environment Variables**:
   ```
   export DEVHELM_API_URL=https://api.devhelm.example.com
   export DEVHELM_API_KEY=your_api_key
   export DEVHELM_LOG_LEVEL=INFO
   ```

2. **Configuration File**:
   Create a file at `~/.config/devhelm/config.ini` with:
   ```ini
   [api]
   url = https://api.devhelm.example.com
   key = your_api_key
   
   [logging]
   level = INFO
   format = pretty
   file = /path/to/logfile.log  # Optional
   ```

## Testing

### Testing Framework

The project uses pytest with the following features:
- Automated test discovery
- Code coverage reporting
- Fixtures for common test setup
- Mock objects for external dependencies

### Running Tests

1. **Run all tests**:
   ```
   python3 -m pytest
   ```

2. **Run tests with coverage**:
   ```
   python3 -m pytest --cov=devhelm_agent
   ```

3. **Run specific test file**:
   ```
   python3 -m pytest tests/test_utils.py
   ```

4. **Run tests with verbose output**:
   ```
   python3 -m pytest -v
   ```

### Writing Tests

When writing tests, follow these guidelines:

1. **Test File Organization**:
   - Place test files in the `tests/` directory
   - Name test files with the `test_` prefix
   - Structure test files to mirror the module structure

2. **Test Functions**:
   - Name test functions with the `test_` prefix
   - Use descriptive names that indicate what is being tested
   - Include docstrings explaining the test purpose

3. **Mocking**:
   - Mock external dependencies like `pyautogui` to prevent display errors
   - Use the mock fixtures provided in `conftest.py` when possible
   - For UI automation tests, always mock screen interactions

4. **Example Test**:
   ```python
   def test_extract_ticket_id_with_valid_ticket():
       """Test extract_ticket_id with text containing a valid ticket ID."""
       text = "Please work on ticket DH-123 as soon as possible."
       assert extract_ticket_id(text) == "DH-123"
   ```

5. **UI Automation Testing**:
   - The agent's UI automation is tested in a headless environment
   - PyAutoGUI is mocked to prevent DISPLAY environment variable errors
   - To test UI functionality, use the session-wide `mock_pyautogui` fixture

### Test Fixtures

Common test fixtures are defined in `conftest.py`:

- `mock_pyautogui`: Session-wide fixture that mocks PyAutoGUI
- `sample_task_data`: Provides sample task data for testing
- `task_requester`: Provides a configured TaskRequester instance
- `mock_config`: Provides a mock configuration object

Use these fixtures to simplify test setup and ensure consistency.

## Code Style and Development

### Code Style

The project follows these style guidelines:

1. **Formatting**:
   - Black (line length: 88 characters)
   - isort for import sorting (configured to be compatible with Black)

2. **Linting**:
   - flake8 for code quality checks
   - mypy for static type checking (Python 3.8 with strict typing)

3. **Documentation**:
   - Comprehensive docstrings in Google style
   - Type annotations for all functions and methods

### Development Workflow

1. **Create a feature branch**:
   ```
   git checkout -b feature/your-feature-name
   ```

2. **Make changes and run tests**:
   ```
   # Run tests to ensure everything works
   python3 -m pytest
   ```

3. **Format code**:
   ```
   # Format with Black
   black src/ tests/
   
   # Sort imports
   isort src/ tests/
   ```

4. **Run type checking**:
   ```
   mypy src/
   ```

5. **Commit changes**:
   ```
   git add .
   git commit -m "Add your descriptive commit message"
   ```

   The pre-commit hooks will automatically check formatting, imports, and linting.

### Project Structure

- `src/devhelm_agent/`: Source code
  - `__init__.py`: Package initialization
  - `main.py`: Entry point for the application
  - `config.py`: Configuration handling
  - `logger_factory.py`: Logging setup
  - `task_requester.py`: API interaction for tasks
  - `ui_interaction.py`: UI automation logic
  - `utils.py`: Utility functions
  - `images/`: Reference images for UI recognition

- `tests/`: Test files
  - `conftest.py`: Test configuration and fixtures
  - `test_*.py`: Individual test modules

### Debugging

1. **Enable Debug Logging**:
   ```python
   from devhelm_agent.logger_factory import get_logger
   
   logger = get_logger(__name__)
   logger.debug("Detailed debug information")
   ```

2. **Headless Environment Considerations**:
   - UI automation tests require special handling in headless environments
   - Use the provided mocks for pyautogui in tests
   - For manual debugging, ensure a display is available or use mock mode

## Common Issues and Solutions

1. **PyAutoGUI Display Issues**:
   - Error: "pyautogui fails with DISPLAY environment variable error"
   - Solution: Use the mock_pyautogui fixture for tests or set up a virtual display with Xvfb

2. **API Connection Issues**:
   - Error: "Cannot connect to DevHelm API"
   - Solution: Check API URL and key in configuration, verify network connectivity

3. **Type Checking Errors**:
   - Error: "mypy reports missing type annotations"
   - Solution: Add proper type hints for all function parameters and return values