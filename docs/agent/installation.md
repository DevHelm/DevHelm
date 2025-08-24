# DevHelm Agent Installation Guide

This document provides step-by-step installation instructions for the DevHelm Agent on Ubuntu systems.

## Overview

The DevHelm Agent is a specialized automation tool that integrates with the DevHelm platform to manage tasks and interact with Junie (IntelliJ-based AI assistant). It runs on the same machine as IntelliJ IDEA and provides automated task management capabilities.

## System Requirements

### Operating System
- Ubuntu 18.04 LTS or newer
- Desktop environment (GNOME, KDE, XFCE, etc.)
- X11 display server (required for screen automation)

### Hardware Requirements
- Minimum 2GB RAM
- 100MB free disk space
- Display with minimum 1024x768 resolution

### Software Prerequisites
- Python 3.8 or newer
- pip (Python package manager)
- Git (for cloning the repository)

## Installation Steps

### 1. System Package Installation

First, update your system and install required packages:

```bash
# Update package list
sudo apt update

# Install Python and development tools
sudo apt install python3 python3-pip python3-venv git

# Install system dependencies for UI automation
sudo apt install python3-tk python3-dev

# Install screenshot utility (used by the agent)
sudo apt install gnome-screenshot

# Install X11 development libraries (required for PyAutoGUI)
sudo apt install python3-xlib

# Install additional dependencies for OpenCV
sudo apt install libgl1-mesa-glx libglib2.0-0
```

### 2. Clone the Repository

Clone the DevHelm repository to your preferred location:

```bash
# Clone the repository
git clone https://github.com/DevHelm/monorepo.git devhelm

# Navigate to the agent directory
cd devhelm/agent
```

### 3. Create Virtual Environment

Create and activate a Python virtual environment:

```bash
# Create virtual environment
python3 -m venv .venv

# Activate virtual environment
source .venv/bin/activate

# Verify virtual environment is active (should show .venv in path)
which python
```

### 4. Install Agent Dependencies

Install the agent and its dependencies:

```bash
# Option A: Install as a package (recommended)
pip install .

# Option B: Install dependencies only
pip install -r requirements.txt
```

### 5. Environment Configuration

The agent requires several environment variables for proper operation:

```bash
# Create environment file
cat > .env << EOF
# DevHelm API Configuration
API_URL=https://your-devhelm-instance.com/api
API_KEY=your-api-key-here

# Logging Configuration (optional)
LOG_FORMAT=pretty
LOG_FILE=

# Agent Configuration (optional)
SCREENSHOT_PATH=./screenshots
EOF

# Load environment variables
source .env
```

## Environment Variables

### Required Variables

- **API_URL**: The base URL for your DevHelm API endpoint
- **API_KEY**: Your DevHelm API authentication key

### Optional Variables

- **LOG_FORMAT**: Log output format (`pretty` or `json`, default: `pretty`)
- **LOG_FILE**: Log file path (empty for stdout, default: stdout)
- **SCREENSHOT_PATH**: Directory for screenshot storage (default: `./screenshots`)

## Verification

### Test Installation

Verify the installation by running:

```bash
# If installed as package
devhelm-agent --help

# If using direct execution
python main.py --help
```

### Test System Dependencies

Test that the system can perform screen automation:

```bash
# Test screenshot capability
gnome-screenshot -w -f test-screenshot.png

# Test Python GUI access (should not produce errors)
python3 -c "import pyautogui; print('PyAutoGUI test successful')"
```

## Troubleshooting

### Common Installation Issues

#### 1. PyAutoGUI Installation Fails
```bash
# Install additional dependencies
sudo apt install python3-dev libpython3-dev
pip install --upgrade pip setuptools wheel
pip install pyautogui
```

#### 2. OpenCV Installation Issues
```bash
# Install OpenCV dependencies
sudo apt install libopencv-dev python3-opencv
pip install opencv-python
```

#### 3. Display/X11 Issues
```bash
# Ensure X11 forwarding is enabled (if using SSH)
ssh -X username@hostname

# Test X11 access
echo $DISPLAY
xrandr  # Should list available displays
```

#### 4. Permission Issues
```bash
# Ensure user is in appropriate groups
sudo usermod -a -G video $USER
sudo usermod -a -G input $USER

# Log out and back in for group changes to take effect
```

### Dependency Conflicts

If you encounter dependency conflicts:

```bash
# Create fresh virtual environment
rm -rf .venv
python3 -m venv .venv
source .venv/bin/activate

# Install with specific versions
pip install --upgrade pip
pip install -r requirements.txt
```

### System Compatibility

#### Ubuntu Version Compatibility
- **Ubuntu 24.04 LTS**: Fully supported
- **Ubuntu 22.04 LTS**: Fully supported  
- **Ubuntu 20.04 LTS**: Fully supported
- **Ubuntu 18.04 LTS**: Supported (may require additional packages)

#### Python Version Compatibility
- **Python 3.12**: Recommended
- **Python 3.11**: Fully supported
- **Python 3.10**: Fully supported
- **Python 3.9**: Fully supported
- **Python 3.8**: Minimum supported version

## Security Considerations

### API Key Management
- Store API keys securely using environment variables
- Never commit API keys to version control
- Use restricted API keys with minimal required permissions

### System Access
- The agent requires access to:
  - Screen capture capabilities
  - Mouse and keyboard control
  - Network access for API communication
  - File system access for logging and screenshots

### Network Configuration
- Ensure firewall allows outbound HTTPS connections
- Configure proxy settings if required by your network

## Next Steps

After successful installation, proceed to the [Usage Guide](usage.md) to learn how to configure and run the DevHelm Agent.

For advanced configuration options, see the [Logging Documentation](logging.md).