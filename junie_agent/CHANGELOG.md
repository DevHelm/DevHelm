# Changelog

All notable changes to the DevHelm Agent project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Standard Python package structure with `src/devhelm_junie_agent/` layout
- Comprehensive development tools configuration (black, isort, flake8, mypy)
- Pre-commit hooks for code quality enforcement
- Pytest configuration with coverage reporting
- Shared test fixtures in `conftest.py`
- Development documentation in README.md
- CONTRIBUTING.md for development guidelines
- This CHANGELOG.md file

### Changed
- Restructured project from flat module layout to standard Python package
- Moved all Python modules to `src/devhelm_junie_agent/` directory
- Updated import statements to use relative imports within package
- Moved test files to `tests/` directory with proper package imports
- Updated `pyproject.toml` for src layout and added development dependencies
- Enhanced README.md with development installation and usage instructions
- Consolidated all dependencies in `pyproject.toml` (added urllib3)

### Fixed
- Package entry point now correctly references `devhelm_junie_agent.main:main`
- Import paths updated for new package structure
- Test mocking improved with shared fixtures

## [0.1.0] - 2024-08-24

### Added
- Initial DevHelm Agent implementation
- Task management and API integration
- UI automation with computer vision
- Structured logging with loguru
- Basic test suite
- Configuration management
- README.md with user documentation

### Features
- Automated task requesting from DevHelm platform
- Junie UI state detection using computer vision
- Continuous monitoring loop with 60-second intervals
- Error handling for network and UI issues
- Configurable logging (JSON/pretty formats, file/stdout output)
- Production-ready entry point script