import os
import sys


class Config:
    """
    Configuration class for DevHelm Agent.
    
    Encapsulates all configuration data including API access and logging settings.
    """
    
    def __init__(self, api_url: str, api_key: str, log_format: str, log_file: str):
        """
        Initialize Config with validated configuration values.
        
        Args:
            api_url: The base URL for API access
            api_key: The API key for authentication
            log_format: Log format setting ('json' or other)
            log_file: Log file path (empty string means stdout)
        """
        self.api_url = api_url
        self.api_key = api_key
        self.log_format = log_format
        self.log_file = log_file


def get_config() -> Config:
    """
    Read required environment variables for ComControl API access and logging configuration.
    
    Returns:
        Config: Configuration object containing validated settings
        
    Raises:
        SystemExit: If required environment variables are not set
    """
    api_url = os.getenv('BASE_URL')
    api_key = os.getenv('API_KEY')
    
    # Fetch logging environment variables
    log_format = os.getenv('LOG_FORMAT', '')
    log_file = os.getenv('LOG_FILE', '')
    
    if not api_url:
        # Note: We can't use logger here yet since it needs the logging configuration
        print("Error: BASE_URL environment variable is not set")
        sys.exit(1)
        
    if not api_key:
        # Note: We can't use logger here yet since it needs the logging configuration
        print("Error: API_KEY environment variable is not set")
        sys.exit(1)
    
    return Config(api_url, api_key, log_format, log_file)