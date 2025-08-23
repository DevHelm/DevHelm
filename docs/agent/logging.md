# Agent Logging

This document describes the logging implementation for the DevHelm Agent, which uses [loguru](https://loguru.readthedocs.io/) for structured and configurable logging.

## Overview

The DevHelm Agent includes a comprehensive logging system that replaces all print statements with structured log entries. The logging system is configurable via environment variables and uses a factory pattern for initialization.

## Features

- **Loguru Integration**: Uses the loguru library for powerful and flexible logging
- **Environment Variable Configuration**: Configurable via `LOG_FORMAT` and `LOG_FILE` environment variables
- **Multiple Output Formats**: Supports both pretty-printed and JSON formats
- **Flexible Output Destinations**: Can log to stdout or files
- **Factory Pattern**: Clean, reusable logger initialization
- **Log Levels**: Appropriate log levels (DEBUG, INFO, WARNING, ERROR) based on message context

## Environment Variables

### LOG_FORMAT

Controls the output format of log messages.

- **Values**: 
  - `json` - Outputs logs in JSON format for machine parsing
  - Any other value or unset - Uses pretty-printed format with colors (default)

- **Examples**:
  ```bash
  # JSON format
  export LOG_FORMAT=json
  
  # Pretty format (default)
  export LOG_FORMAT=pretty
  # or
  unset LOG_FORMAT
  ```

### LOG_FILE

Controls where log messages are written.

- **Values**:
  - Empty/unset - Logs to stdout (default)
  - File path - Logs to the specified file with automatic rotation

- **Examples**:
  ```bash
  # Log to stdout (default)
  unset LOG_FILE
  # or
  export LOG_FILE=""
  
  # Log to file
  export LOG_FILE="/var/log/devhelm-agent.log"
  export LOG_FILE="./agent.log"
  ```

## Usage

### Basic Usage

The logging system is automatically initialized when the agent starts:

```python
from logger_factory import LoggerFactory

# Initialize logger using the factory
logger = LoggerFactory.get_logger()

# Use the logger
logger.info("Agent starting up")
logger.error("Something went wrong")
logger.debug("Detailed debugging information")
```

### Log Levels

The agent uses appropriate log levels for different types of messages:

- **ERROR**: Critical errors, failures, and exceptions
- **WARNING**: Warning conditions, non-critical issues
- **INFO**: General information, successful operations, task updates
- **DEBUG**: Detailed debugging information, UI state checks, routine operations

## Configuration Examples

### Development Setup (Pretty Logs to Console)
```bash
# Default configuration - pretty logs to stdout
unset LOG_FORMAT
unset LOG_FILE
python main.py
```

### Production Setup (JSON Logs to File)
```bash
# JSON logs to file for production monitoring
export LOG_FORMAT=json
export LOG_FILE="/var/log/devhelm-agent.log"
python main.py
```

### Debugging Setup (Verbose Pretty Logs)
```bash
# Pretty logs to file for debugging
export LOG_FORMAT=pretty
export LOG_FILE="./debug.log"
python main.py
```

## Log Output Examples

### Pretty Format (Default)
```
2025-08-23 21:40:15.123 | INFO     | main:main:77 - Starting DevHelm Agent...
2025-08-23 21:40:15.124 | INFO     | main:fetch_initial_task:55 - Initial task received: DH-6 - Add logging functionality
2025-08-23 21:40:15.125 | DEBUG    | main:main:96 - UI is ready for prompt - 'Start Again' button detected
2025-08-23 21:40:15.126 | ERROR    | main:main:114 - Failed to enter task prompt
```

### JSON Format
```json
{"time":"2025-08-23 21:40:15.123","level":"INFO","message":"Starting DevHelm Agent...","file":"main.py","function":"main","line":77}
{"time":"2025-08-23 21:40:15.124","level":"INFO","message":"Initial task received: DH-6 - Add logging functionality","file":"main.py","function":"fetch_initial_task","line":55}
{"time":"2025-08-23 21:40:15.125","level":"DEBUG","message":"UI is ready for prompt - 'Start Again' button detected","file":"main.py","function":"main","line":96}
{"time":"2025-08-23 21:40:15.126","level":"ERROR","message":"Failed to enter task prompt","file":"main.py","function":"main","line":114}
```

## File Logging Features

When `LOG_FILE` is set, the logger includes additional features:

- **Automatic Rotation**: Log files are rotated when they reach 10 MB
- **Retention**: Old log files are kept for 7 days
- **Compression**: Old log files are compressed with zip
- **Directory Creation**: Parent directories are created automatically if they don't exist

## Implementation Details

### Logger Factory

The `LoggerFactory` class provides a clean interface for creating configured logger instances:

```python
class LoggerFactory:
    @staticmethod
    def get_logger():
        # Configures and returns a loguru logger instance
        # based on environment variables
```

### Migration from Print Statements

All print statements in the agent have been replaced with appropriate logger calls:

- Error messages → `logger.error()`
- Informational messages → `logger.info()`
- Debug/trace messages → `logger.debug()`
- Warning conditions → `logger.warning()`

## Dependencies

The logging system requires the `loguru` package, which is automatically installed with the agent:

```toml
dependencies = [
  "loguru>=0.7.0"
]
```

## Troubleshooting

### Common Issues

1. **Logs not appearing**: Check that LOG_FILE directory exists and is writable
2. **JSON format issues**: Ensure LOG_FORMAT is exactly "json" (case-sensitive)
3. **File permission errors**: Ensure the process has write permissions to the log file location

### Debugging

To troubleshoot logging issues, you can temporarily use a simple configuration:

```bash
unset LOG_FORMAT
unset LOG_FILE
python main.py
```

This will output pretty-formatted logs directly to the console where they can be easily observed.