import os
import sys
from loguru import logger
from typing import Optional


class LoggerFactory:
    """
    Factory class for creating and configuring loguru logger instances.
    
    Supports configuration via environment variables:
    - LOG_FORMAT: 'json' for JSON formatting, anything else for pretty formatting
    - LOG_FILE: File path for logging output, empty/unset means stdout
    """
    
    @staticmethod
    def create_logger() -> logger:
        """
        Create and configure a loguru logger based on environment variables.
        
        Environment Variables:
        - LOG_FORMAT: If 'json', uses JSON formatter; otherwise uses pretty formatter
        - LOG_FILE: If empty/unset, logs to stdout; otherwise logs to specified file
        
        Returns:
            logger: Configured loguru logger instance
        """
        # Remove default handler first
        logger.remove()
        
        # Get environment variables with defaults
        log_format = os.getenv('LOG_FORMAT', '').lower()
        log_file = os.getenv('LOG_FILE', '')
        
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
                rotation="10 MB",
                retention="7 days",
                compression="zip"
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
    def get_logger() -> logger:
        """
        Get a configured logger instance.
        This is a convenience method that calls create_logger().
        
        Returns:
            logger: Configured loguru logger instance
        """
        return LoggerFactory.create_logger()