"""
TaskRequester module for fetching tasks from DevHelm API.

This module provides the TaskRequester class that handles HTTP requests to
the DevHelm API to fetch new tasks for the agent.
"""

import json
from dataclasses import dataclass
from typing import Optional
import urllib3


@dataclass
class Task:
    """
    Represents a task from the DevHelm API.
    
    Attributes:
        id: Unique identifier for the task (UUID format)
        ticket_id: Jira ticket identifier (e.g., "BB-23")
        prompt: The prompt/instructions for the task
    """
    id: str
    ticket_id: str
    prompt: str


class TaskRequesterException(Exception):
    """Exception raised for invalid responses from the DevHelm server."""
    pass


class TaskRequester:
    """
    Handles requests to the DevHelm API to fetch new tasks.
    
    This class makes HTTP requests to the DevHelm API endpoint to retrieve
    new tasks for the agent to process.
    """
    
    def __init__(self, base_url: str, api_key: str):
        """
        Initialize the TaskRequester.
        
        Args:
            base_url: The base URL for the DevHelm API
            api_key: The API key for authentication
        """
        self.base_url = base_url.rstrip('/')  # Remove trailing slash if present
        self.api_key = api_key
        self.http = urllib3.PoolManager()
    
    def request_task(self) -> Optional[Task]:
        """
        Request a new task from the DevHelm API.
        
        Makes a GET request to the /v1/task endpoint with the API key
        for authentication.
        
        Returns:
            Task: A Task object if a new task is available (HTTP 200)
            None: If no task is available or task already in progress (HTTP 409)
            
        Raises:
            TaskRequesterException: If the server returns an invalid response
                or an unexpected HTTP status code
        """
        url = f"{self.base_url}/v1/task"
        headers = {
            'X-API-KEY': self.api_key,
            'Content-Type': 'application/json'
        }
        
        try:
            response = self.http.request('GET', url, headers=headers)
            
            # Handle successful response with new task
            if response.status == 200:
                try:
                    data = json.loads(response.data.decode('utf-8'))
                    
                    # Validate required fields
                    if not isinstance(data, dict):
                        raise TaskRequesterException("Invalid response format: expected JSON object")
                    
                    required_fields = ['id', 'ticket_id', 'prompt']
                    missing_fields = [field for field in required_fields if field not in data]
                    if missing_fields:
                        raise TaskRequesterException(f"Missing required fields in response: {missing_fields}")
                    
                    # Validate field types
                    if not isinstance(data['id'], str):
                        raise TaskRequesterException("Invalid response: 'id' must be a string")
                    if not isinstance(data['ticket_id'], str):
                        raise TaskRequesterException("Invalid response: 'ticket_id' must be a string")
                    if not isinstance(data['prompt'], str):
                        raise TaskRequesterException("Invalid response: 'prompt' must be a string")
                    
                    return Task(
                        id=data['id'],
                        ticket_id=data['ticket_id'],
                        prompt=data['prompt']
                    )
                    
                except json.JSONDecodeError as e:
                    raise TaskRequesterException(f"Invalid JSON response: {e}")
            
            # Handle conflict response (task already in progress)
            elif response.status == 409:
                return None
            
            # Handle no valid tickets response
            elif response.status == 204:
                return None
            
            # Handle other HTTP status codes as errors
            else:
                try:
                    error_data = json.loads(response.data.decode('utf-8'))
                    error_message = error_data.get('message', f'HTTP {response.status}')
                except (json.JSONDecodeError, UnicodeDecodeError):
                    error_message = f'HTTP {response.status}'
                
                raise TaskRequesterException(f"Server returned error: {error_message}")
                
        except urllib3.exceptions.HTTPError as e:
            raise TaskRequesterException(f"HTTP request failed: {e}")
        except Exception as e:
            if isinstance(e, TaskRequesterException):
                raise
            raise TaskRequesterException(f"Unexpected error: {e}")