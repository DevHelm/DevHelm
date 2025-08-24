import os
import sys
from loguru import logger
from typing import Optional
from .config import Config


class LoggerFactory:
    """
    Factory class for creating and configuring loguru logger instances.
    
    Supports configuration via parameters:
    - log_format: 'json' for JSON formatting, anything else for pretty formatting
    - log_file: File path for logging output, empty/unset means stdout
    """
    
    @staticmethod
    def create_logger(config: Config) -> logger:
        """
        Create and configure a loguru logger based on provided Config object.
        
        Args:
            config: Config object containing log_format and log_file settings
        
        Returns:
            logger: Configured loguru logger instance
        """
        # Remove default handler first
        logger.remove()
        
        # Process the format parameter from config
        log_format = config.log_format.lower()
        log_file = config.log_file
        
        # Determine format based on LOG_FORMAT env var
        if log_format == 'json':
            format_string = "{\"time\":\"{time:YYYY-MM-DD HH:mm:ss.SSS}\",\"level\":\"{level}\",\"message\":\"{message}\",\"file\":\"{file.name}\",\"function\":\"{function}\",\"line\":{line}}"
        else:
            # Pretty format (default)
            format_string = "<green>{time:YYYY-MM-DD HH:mm:ss.SSS}</green> | <level>{level: <8}</level> | <cyan>{name}</cyan>:<cyan>{function}</cyan>:<cyan>{line}</cyan> - <level>{message}</level>"
        
        # Determine output destination based on LOG_FILE env var
        if log_file:
            # Log to file
            logger.add(
                log_file,
                format=format_string,
                level="DEBUG",
                rotation="100 MB",
                retention="7 days"
            )
        else:
            # Log to stdout (default)
            logger.add(
                sys.stdout,
                format=format_string,
                level="DEBUG",
                colorize=True if log_format != 'json' else False
            )
        
        return logger
    
    @staticmethod
    def get_logger(config: Config) -> logger:
        """
        Get a configured logger instance.
        This is a convenience method that calls create_logger().
        
        Args:
            config: Config object containing log_format and log_file settings
        
        Returns:
            logger: Configured loguru logger instance
        """
        return LoggerFactory.create_logger(config)