# DevHelm Agent

The DevHelm Agent is an intelligent automation tool that integrates with the DevHelm platform to provide seamless task management and automated interaction with Junie (IntelliJ-based AI assistant).

## What is the DevHelm Agent?

The agent runs continuously on your development machine, monitoring for completed tasks in Junie and automatically requesting new tasks from the DevHelm platform. It uses computer vision to detect UI states and provides appropriate prompts to maintain workflow automation.

### Key Features

- **Automated Task Management**: Continuously monitors for task completion and requests new work
- **Intelligent UI Detection**: Uses computer vision to detect Junie's "Start Again" state
- **API Integration**: Seamlessly communicates with DevHelm platform for task orchestration  
- **Robust Error Handling**: Handles network issues, API timeouts, and UI detection failures
- **Comprehensive Logging**: Structured logging with configurable output formats
- **Production Ready**: Supports background service operation and monitoring

## Quick Start

### Prerequisites
- Ubuntu 18.04+ with desktop environment
- Python 3.8+
- DevHelm platform access with API credentials
- IntelliJ IDEA with Junie plugin

### Installation
```bash
# Clone repository and navigate to agent
git clone https://github.com/DevHelm/monorepo.git devhelm
cd devhelm/agent

# Create virtual environment and install
python3 -m venv .venv
source .venv/bin/activate
pip install .
```

### Configuration
```bash
# Set required environment variables
export API_URL="https://your-devhelm-instance.com/api"
export API_KEY="your-api-key-here"
```

### Run
```bash
# Run the agent
devhelm-agent
```

## How It Works

The agent implements a continuous monitoring loop:

1. **Startup**: Requests initial task from DevHelm API
2. **Monitor**: Watches for Junie's "Start Again" button every 60 seconds
3. **Request**: When detected, requests new task from DevHelm
4. **Execute**: Handles three responses:
   - **New Task**: Enters new prompt in Junie
   - **Continue**: Tells Junie to continue current task
   - **No Tasks**: Waits for next cycle

This creates a fully automated workflow where Junie can work continuously on tasks provided by the DevHelm platform.

## Documentation

### User Guides
- **[Installation Guide](../docs/agent/installation.md)** - Complete Ubuntu installation instructions
- **[Usage Guide](../docs/agent/usage.md)** - How to configure and run the agent
- **[Logging Guide](../docs/agent/logging.md)** - Logging configuration and monitoring

### Architecture
The agent consists of several key components:
- **TaskRequester**: Handles DevHelm API communication
- **UIInteraction**: Manages computer vision and UI automation
- **LoggerFactory**: Provides structured logging capabilities
- **Config**: Manages environment variable configuration

## System Requirements

- **OS**: Ubuntu 18.04 LTS or newer with desktop environment
- **Python**: 3.8+ with pip and venv
- **Display**: X11 display server (required for screen automation)
- **Network**: HTTPS access to DevHelm platform
- **Memory**: Minimum 2GB RAM
- **Disk**: 100MB free space

## Support

### Getting Help
- Read the [Installation Guide](../docs/agent/installation.md) for setup issues
- Check the [Usage Guide](../docs/agent/usage.md) for operational problems
- Review logs with `LOG_FORMAT=pretty LOG_FILE=debug.log devhelm-agent`

### Common Issues
- **Agent exits immediately**: Check API credentials and connectivity
- **UI not detected**: Verify display access and image files
- **Permission errors**: Add user to video/input groups
- **Network issues**: Check firewall and proxy settings

### Reporting Bugs
When reporting issues, include:
- Agent version and configuration
- Recent log entries (last 50 lines)
- System information (OS, Python version)
- Steps to reproduce

## Security

- Store API keys in environment variables, never in code
- Use restricted API keys with minimal permissions
- Run with minimum required system privileges
- Monitor agent logs for suspicious activity
- Keep system and dependencies updated

## License

Please see the repository root [LICENSE.md](../LICENSE.md) for license details.
