# DevHelm Agent Usage Guide

This document explains how to configure and run the DevHelm Agent for automated task management and Junie integration.

## Overview

The DevHelm Agent is designed to run continuously on your development machine, monitoring for completed tasks in Junie and automatically requesting new tasks from the DevHelm platform. It operates by detecting UI states and providing appropriate prompts to continue workflow automation.

## Prerequisites

Before running the agent, ensure you have:

1. Completed the [installation process](installation.md)
2. Access to a DevHelm platform instance
3. Valid API credentials
4. IntelliJ IDEA with Junie plugin installed
5. Desktop environment with screen access

## Configuration

### Environment Variables

The agent requires configuration through environment variables. Create a `.env` file or export these variables:

#### Required Configuration

```bash
# DevHelm API Configuration
export API_URL="https://your-devhelm-instance.com/api"
export API_KEY="your-api-key-here"
```

#### Optional Configuration

```bash
# Logging Configuration
export LOG_FORMAT="pretty"        # Options: "pretty" or "json"
export LOG_FILE=""                # Empty for stdout, or path to log file

# UI Detection Configuration (advanced)
export SCREENSHOT_PATH="./screenshots"
export UI_CONFIDENCE_THRESHOLD="0.9"
```

### Configuration File Method

Alternatively, create a `.env` file in the agent directory:

```bash
# Navigate to agent directory
cd devhelm/agent

# Create configuration file
cat > .env << 'EOF'
# DevHelm API Configuration
API_URL=https://your-devhelm-instance.com/api
API_KEY=your-api-key-here

# Logging Configuration
LOG_FORMAT=pretty
LOG_FILE=

# Optional: Advanced Configuration
SCREENSHOT_PATH=./screenshots
UI_CONFIDENCE_THRESHOLD=0.9
EOF

# Load environment variables
source .env
```

## Running the Agent

### Method 1: Package Installation (Recommended)

If you installed the agent as a package:

```bash
# Activate virtual environment
source .venv/bin/activate

# Run the agent
devhelm-agent
```

### Method 2: Direct Execution

If you installed dependencies only:

```bash
# Activate virtual environment
source .venv/bin/activate

# Load environment variables
source .env

# Run the agent
python main.py
```

### Running as a Background Service

For production use, run the agent as a background service:

```bash
# Using nohup
nohup devhelm-agent > agent.log 2>&1 &

# Using screen
screen -S devhelm-agent devhelm-agent

# Using systemd (see Service Configuration section)
sudo systemctl start devhelm-agent
```

## How the Agent Works

### Business Logic

The agent implements a continuous monitoring loop:

1. **Startup**: Requests an initial task from DevHelm API
   - If no task available, the agent exits
   - If task received, stores it as the current task

2. **Main Loop** (runs every 60 seconds):
   - Monitors screen for "Start Again" button
   - When detected, waits 60 seconds to avoid race conditions
   - Requests a new task from DevHelm API
   - Handles three possible responses:
     - **New Task**: Updates current task, enters new prompt in Junie
     - **Continue (409 response)**: Enters "continue" to resume current task
     - **No Tasks**: Waits for next cycle

3. **UI Interaction**:
   - Detects Junie's "Start Again" button using computer vision
   - Locates text input field using "Type your" label detection  
   - Types prompts and presses Enter automatically

### Workflow States

```
┌─────────────┐    ┌──────────────┐    ┌─────────────┐
│   Startup   │───▶│ Request Task │───▶│ Enter Prompt│
└─────────────┘    └──────────────┘    └─────────────┘
        │                   │                   │
        ▼                   │                   ▼
┌─────────────┐             │          ┌─────────────┐
│    Exit     │◀────────────┘          │  Main Loop  │
└─────────────┘                        └─────────────┘
                                              │
                  ┌───────────────────────────┘
                  │
                  ▼
        ┌─────────────────┐
        │ Wait for "Start │
        │   Again" UI     │
        └─────────────────┘
```

## Command Line Options

### Basic Usage

```bash
# Run with default configuration
devhelm-agent

# Run with verbose logging
LOG_FORMAT=pretty LOG_FILE=debug.log devhelm-agent

# Run with JSON logging for monitoring
LOG_FORMAT=json devhelm-agent
```

### Environment Override

You can override configuration at runtime:

```bash
# Use different API endpoint
API_URL=https://staging-devhelm.com/api devhelm-agent

# Enable file logging
LOG_FILE=./agent.log devhelm-agent

# Combine multiple overrides
API_URL=https://dev.devhelm.com/api LOG_FORMAT=json devhelm-agent
```

## Monitoring and Logging

### Log Levels

The agent uses structured logging with appropriate levels:

- **INFO**: General operations, task updates, successful actions
- **DEBUG**: Detailed UI state checks, routine operations
- **WARNING**: Non-critical issues, retries
- **ERROR**: Failures, exceptions, critical problems

### Log Output Examples

#### Console Output (Pretty Format)
```
2025-08-23 21:40:15.123 | INFO     | main:62 - Starting DevHelm Agent...
2025-08-23 21:40:15.124 | INFO     | main:34 - Initial task received: DH-7 - Write documentation
2025-08-23 21:40:15.125 | DEBUG    | main:78 - UI is ready for prompt - 'Start Again' button detected
2025-08-23 21:40:15.126 | INFO     | main:90 - New task received: DH-8 - Fix bug in authentication
```

#### JSON Format (for monitoring systems)
```json
{"time":"2025-08-23 21:40:15.123","level":"INFO","message":"Starting DevHelm Agent..."}
{"time":"2025-08-23 21:40:15.124","level":"INFO","message":"Initial task received: DH-7 - Write documentation"}
```

### Monitoring Health

Monitor agent health by checking:

```bash
# Check if agent is running
ps aux | grep devhelm-agent

# Monitor recent log entries
tail -f agent.log

# Check API connectivity
curl -H "Authorization: Bearer $API_KEY" "$API_URL/health"
```

## Troubleshooting

### Common Issues

#### 1. Agent Exits Immediately
**Symptoms**: Agent starts then exits with "No initial task available"
**Solution**:
```bash
# Check API connectivity
curl -H "Authorization: Bearer $API_KEY" "$API_URL/tasks"

# Verify API credentials
echo "API_URL: $API_URL"
echo "API_KEY: ${API_KEY:0:10}..."  # Show first 10 chars only
```

#### 2. UI Detection Issues
**Symptoms**: Agent doesn't detect "Start Again" button
**Solutions**:
```bash
# Verify display access
echo $DISPLAY
xrandr

# Test screenshot capability
python -c "import pyautogui; pyautogui.screenshot('test.png'); print('Screenshot saved')"

# Check image files exist
ls -la images/start_again.png images/type_your.png
```

#### 3. Permission Errors
**Symptoms**: "Permission denied" or input device errors
**Solutions**:
```bash
# Add user to required groups
sudo usermod -a -G video $USER
sudo usermod -a -G input $USER

# Log out and back in, then test
python -c "import pyautogui; pyautogui.click(100,100); print('Click test successful')"
```

#### 4. Network/API Issues
**Symptoms**: Connection timeouts, authentication errors
**Solutions**:
```bash
# Test network connectivity
ping your-devhelm-instance.com

# Test HTTPS access
curl -I https://your-devhelm-instance.com/api

# Check firewall settings
sudo ufw status
```

### Debug Mode

Enable detailed debugging:

```bash
# Run with debug logging to file
LOG_FORMAT=pretty LOG_FILE=debug.log devhelm-agent

# Monitor debug output in real-time
tail -f debug.log
```

### Performance Tuning

#### Reduce Resource Usage
```bash
# Increase loop interval (less frequent checks)
LOOP_INTERVAL=120 devhelm-agent

# Reduce screenshot quality
SCREENSHOT_QUALITY=0.8 devhelm-agent
```

#### Improve Response Time
```bash
# Decrease confidence threshold for faster UI detection
UI_CONFIDENCE_THRESHOLD=0.8 devhelm-agent

# Use faster screenshot method
SCREENSHOT_METHOD=fast devhelm-agent
```

## Service Configuration

### Systemd Service (Ubuntu 18.04+)

Create a systemd service for automatic startup:

```bash
# Create service file
sudo tee /etc/systemd/system/devhelm-agent.service << EOF
[Unit]
Description=DevHelm Agent
After=graphical-session.target

[Service]
Type=simple
User=$USER
WorkingDirectory=$HOME/devhelm/agent
Environment=DISPLAY=:0
EnvironmentFile=$HOME/devhelm/agent/.env
ExecStart=$HOME/devhelm/agent/.venv/bin/devhelm-agent
Restart=always
RestartSec=30

[Install]
WantedBy=graphical-session.target
EOF

# Enable and start service
sudo systemctl enable devhelm-agent
sudo systemctl start devhelm-agent

# Check service status
sudo systemctl status devhelm-agent
```

### Auto-start with Desktop Session

Add to startup applications:

```bash
# Create desktop entry
mkdir -p ~/.config/autostart
cat > ~/.config/autostart/devhelm-agent.desktop << EOF
[Desktop Entry]
Type=Application
Name=DevHelm Agent
Exec=$HOME/devhelm/agent/.venv/bin/devhelm-agent
Hidden=false
NoDisplay=false
X-GNOME-Autostart-enabled=true
EOF
```

## Security Best Practices

### API Key Security
- Store API keys in environment variables, never in code
- Use restricted API keys with minimal required permissions
- Rotate API keys regularly
- Monitor API key usage through DevHelm platform

### System Security
- Run agent with minimum required privileges
- Keep system and dependencies updated
- Monitor agent logs for suspicious activity
- Use firewall to restrict network access

### Access Control
- Limit agent access to specific screens/applications
- Use virtual displays for isolation if needed
- Implement monitoring and alerting for agent activities

## Advanced Configuration

### Custom UI Detection Images
Replace default detection images:

```bash
# Navigate to images directory
cd images/

# Replace with your custom images
cp /path/to/your/start_again.png .
cp /path/to/your/type_your.png .

# Ensure correct permissions
chmod 644 *.png
```

### Integration with CI/CD
```bash
# Example: Run agent in pipeline
#!/bin/bash
export API_URL="$DEVHELM_API_URL"
export API_KEY="$DEVHELM_API_KEY"
export LOG_FORMAT="json"

# Start agent in background
devhelm-agent &
AGENT_PID=$!

# Run your tests/deployment
./run-tests.sh

# Stop agent
kill $AGENT_PID
```

## Getting Help

### Log Analysis
When reporting issues, include:
- Agent version and configuration
- Recent log entries (last 50 lines)
- System information (OS, Python version)
- Steps to reproduce the issue

### Support Resources
- [Installation Guide](installation.md)
- [Logging Documentation](logging.md)  
- DevHelm Platform Documentation
- GitHub Issues (for bug reports)

### Performance Monitoring
```bash
# Monitor resource usage
top -p $(pgrep -f devhelm-agent)

# Check network connections
netstat -an | grep $(pgrep -f devhelm-agent)

# Monitor file descriptor usage
lsof -p $(pgrep -f devhelm-agent)
```