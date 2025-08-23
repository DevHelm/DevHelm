import os
import sys
from typing import Dict


class Config:
    """
    Configuration class for DevHelm Agent.
    
    Encapsulates all configuration data including API access and logging settings.
    """
    
    def __init__(self, api_url: str, api_key: str, logging_env: Dict[str, str]):
        """
        Initialize Config with validated configuration values.
        
        Args:
            api_url: The base URL for API access
            api_key: The API key for authentication
            logging_env: Dictionary containing logging configuration
        """
        self.api_url = api_url
        self.api_key = api_key
        self.logging_env = logging_env


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
    logging_env = {
        'LOG_FORMAT': os.getenv('LOG_FORMAT', ''),
        'LOG_FILE': os.getenv('LOG_FILE', '')
    }
    
    if not api_url:
        # Note: We can't use logger here yet since it needs the logging_env
        print("Error: BASE_URL environment variable is not set")
        sys.exit(1)
        
    if not api_key:
        # Note: We can't use logger here yet since it needs the logging_env
        print("Error: API_KEY environment variable is not set")
        sys.exit(1)
    
    return Config(api_url, api_key, logging_env)