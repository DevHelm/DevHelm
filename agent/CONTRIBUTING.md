# Contributing to DevHelm Agent

Thank you for your interest in contributing to the DevHelm Agent project! This document provides guidelines and instructions for contributors.

## Table of Contents

- [Development Setup](#development-setup)
- [Code Style](#code-style)
- [Testing](#testing)
- [Pull Request Process](#pull-request-process)
- [Issue Reporting](#issue-reporting)
- [Development Workflow](#development-workflow)

## Development Setup

### Prerequisites

- Python 3.8 or higher
- Git
- Ubuntu 18.04+ with desktop environment (for UI automation testing)

### Getting Started

1. **Fork and Clone the Repository**
   ```bash
   git clone https://github.com/your-username/devhelm-monorepo.git
   cd devhelm-monorepo/agent
   ```

2. **Set Up Development Environment**
   ```bash
   # Create virtual environment
   python3 -m venv .venv
   source .venv/bin/activate
   
   # Install in editable mode with development dependencies
   pip install -e .[dev]
   
   # Install pre-commit hooks
   pre-commit install
   ```

3. **Verify Setup**
   ```bash
   # Run tests to ensure everything works
   pytest
   
   # Check code quality tools
   black --check src/ tests/
   isort --check-only src/ tests/
   flake8 src/ tests/
   mypy src/
   ```

## Code Style

We enforce consistent code style using automated tools:

### Formatting
- **Black**: Code formatter with 88-character line length
- **isort**: Import statement organizer

### Linting
- **Flake8**: PEP 8 compliance and code quality
- **MyPy**: Static type checking

### Pre-commit Hooks
All code quality checks run automatically before commits. To run manually:

```bash
# Run all pre-commit hooks
pre-commit run --all-files

# Run specific tools
black src/ tests/
isort src/ tests/
flake8 src/ tests/
mypy src/
```

## Testing

### Running Tests

```bash
# Run all tests with coverage
pytest

# Run tests with verbose output
pytest -v

# Run specific test file
pytest tests/test_main.py

# Run tests with coverage report
pytest --cov=devhelm_agent --cov-report=html
```

### Writing Tests

- Place tests in the `tests/` directory
- Use pytest fixtures from `tests/conftest.py`
- Mock external dependencies (pyautogui, network calls)
- Aim for good test coverage on new code
- Follow the existing test patterns

### Test Guidelines

- **Unit Tests**: Test individual functions and classes
- **Integration Tests**: Test component interactions
- **Mock External Dependencies**: Use the provided pyautogui mock
- **Descriptive Names**: Test function names should describe what they test

## Pull Request Process

### Before Submitting

1. **Create Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make Your Changes**
   - Follow the code style guidelines
   - Add tests for new functionality
   - Update documentation if needed

3. **Run Quality Checks**
   ```bash
   # Ensure all tests pass
   pytest
   
   # Check code formatting and linting
   pre-commit run --all-files
   ```

4. **Update Documentation**
   - Update README.md if needed
   - Add entry to CHANGELOG.md
   - Update docstrings for new/modified functions

### Submitting Pull Request

1. **Push Changes**
   ```bash
   git push origin feature/your-feature-name
   ```

2. **Create Pull Request**
   - Use descriptive title and description
   - Reference any related issues
   - Include screenshots for UI changes
   - Fill out the pull request template

3. **Address Review Feedback**
   - Respond to reviewer comments
   - Make requested changes
   - Update tests if needed

## Issue Reporting

### Bug Reports

When reporting bugs, please include:

- **Environment Information**: OS, Python version, package version
- **Steps to Reproduce**: Clear, numbered steps
- **Expected Behavior**: What should happen
- **Actual Behavior**: What actually happens
- **Error Messages**: Full error messages and stack traces
- **Log Output**: Relevant log entries
- **Screenshots**: If applicable

### Feature Requests

When requesting features, please include:

- **Use Case**: Why this feature is needed
- **Proposed Solution**: How it should work
- **Alternatives**: Other approaches considered
- **Impact**: Who would benefit from this feature

## Development Workflow

### Git Workflow

1. Create feature branches from `main`
2. Make small, focused commits
3. Write descriptive commit messages
4. Rebase on `main` before submitting PR
5. Squash commits if requested

### Commit Message Format

```
type: brief description

Longer explanation if needed.

Closes #issue-number
```

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

### Release Process

1. Update version in `pyproject.toml`
2. Update CHANGELOG.md
3. Create release tag
4. Build and publish package (maintainers only)

## Code Organization

### Package Structure

```
agent/
├── src/
│   └── devhelm_agent/
│       ├── __init__.py
│       ├── main.py          # Entry point and main logic
│       ├── config.py        # Configuration management
│       ├── task_requester.py # API communication
│       ├── ui_interaction.py # UI automation
│       ├── logger_factory.py # Logging setup
│       └── images/          # UI detection images
├── tests/
│   ├── __init__.py
│   ├── conftest.py         # Shared test fixtures
│   ├── test_main.py        # Main module tests
│   └── test_task_requester.py # API tests
├── pyproject.toml          # Project configuration
├── README.md              # User documentation
├── CHANGELOG.md           # Version history
└── CONTRIBUTING.md        # This file
```

### Adding New Features

1. **Plan the Feature**: Discuss in an issue first
2. **Design the API**: Consider backwards compatibility
3. **Implement**: Follow existing patterns
4. **Test**: Add comprehensive tests
5. **Document**: Update README and docstrings
6. **Review**: Submit PR for code review

## Questions?

If you have questions about contributing, please:

1. Check existing issues and documentation
2. Create a new issue with the "question" label
3. Join our community discussions (if available)

Thank you for contributing to DevHelm Agent!