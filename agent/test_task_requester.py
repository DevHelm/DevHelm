"""
Tests for the TaskRequester module.

This module contains basic tests to verify the TaskRequester functionality
and ensure it handles different response scenarios correctly.
"""

import json
import unittest
from unittest.mock import Mock, patch
from task_requester import TaskRequester, Task, TaskRequesterException


class TestTaskRequester(unittest.TestCase):
    """Test cases for TaskRequester class."""
    
    def setUp(self):
        """Set up test fixtures."""
        self.base_url = "https://api.devhelm.example.com"
        self.api_key = "test-api-key-123"
        self.task_requester = TaskRequester(self.base_url, self.api_key)
    
    def test_init(self):
        """Test TaskRequester initialization."""
        self.assertEqual(self.task_requester.base_url, self.base_url)
        self.assertEqual(self.task_requester.api_key, self.api_key)
        self.assertIsNotNone(self.task_requester.http)
    
    def test_init_with_trailing_slash(self):
        """Test TaskRequester initialization removes trailing slash from base_url."""
        task_requester = TaskRequester("https://api.devhelm.example.com/", self.api_key)
        self.assertEqual(task_requester.base_url, "https://api.devhelm.example.com")
    
    @patch('urllib3.PoolManager')
    def test_request_task_success(self, mock_pool_manager):
        """Test successful task request returns Task object."""
        # Mock response data
        task_data = {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "ticket_id": "DH-123",
            "prompt": "Work on ticket DH-123"
        }
        
        # Mock HTTP response
        mock_response = Mock()
        mock_response.status = 200
        mock_response.data = json.dumps(task_data).encode('utf-8')
        
        mock_http = Mock()
        mock_http.request.return_value = mock_response
        mock_pool_manager.return_value = mock_http
        
        task_requester = TaskRequester(self.base_url, self.api_key)
        result = task_requester.request_task()
        
        # Verify the result
        self.assertIsInstance(result, Task)
        self.assertEqual(result.id, task_data["id"])
        self.assertEqual(result.ticket_id, task_data["ticket_id"])
        self.assertEqual(result.prompt, task_data["prompt"])
        
        # Verify the HTTP request was made correctly
        mock_http.request.assert_called_once_with(
            'GET',
            f"{self.base_url}/v1/task",
            headers={
                'X-API-KEY': self.api_key,
                'Content-Type': 'application/json'
            }
        )
    
    @patch('urllib3.PoolManager')
    def test_request_task_conflict(self, mock_pool_manager):
        """Test task request returns None on 409 conflict."""
        mock_response = Mock()
        mock_response.status = 409
        mock_response.data = json.dumps({"type": "Task already in progress"}).encode('utf-8')
        
        mock_http = Mock()
        mock_http.request.return_value = mock_response
        mock_pool_manager.return_value = mock_http
        
        task_requester = TaskRequester(self.base_url, self.api_key)
        result = task_requester.request_task()
        
        self.assertIsNone(result)
    
    @patch('urllib3.PoolManager')
    def test_request_task_no_content(self, mock_pool_manager):
        """Test task request returns None on 204 no content."""
        mock_response = Mock()
        mock_response.status = 204
        mock_response.data = b""
        
        mock_http = Mock()
        mock_http.request.return_value = mock_response
        mock_pool_manager.return_value = mock_http
        
        task_requester = TaskRequester(self.base_url, self.api_key)
        result = task_requester.request_task()
        
        self.assertIsNone(result)
    
    @patch('urllib3.PoolManager')
    def test_request_task_invalid_json(self, mock_pool_manager):
        """Test task request raises exception on invalid JSON."""
        mock_response = Mock()
        mock_response.status = 200
        mock_response.data = b"invalid json"
        
        mock_http = Mock()
        mock_http.request.return_value = mock_response
        mock_pool_manager.return_value = mock_http
        
        task_requester = TaskRequester(self.base_url, self.api_key)
        
        with self.assertRaises(TaskRequesterException) as context:
            task_requester.request_task()
        
        self.assertIn("Invalid JSON response", str(context.exception))
    
    @patch('urllib3.PoolManager')
    def test_request_task_missing_fields(self, mock_pool_manager):
        """Test task request raises exception on missing required fields."""
        task_data = {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            # Missing ticket_id and prompt
        }
        
        mock_response = Mock()
        mock_response.status = 200
        mock_response.data = json.dumps(task_data).encode('utf-8')
        
        mock_http = Mock()
        mock_http.request.return_value = mock_response
        mock_pool_manager.return_value = mock_http
        
        task_requester = TaskRequester(self.base_url, self.api_key)
        
        with self.assertRaises(TaskRequesterException) as context:
            task_requester.request_task()
        
        self.assertIn("Missing required fields", str(context.exception))
    
    @patch('urllib3.PoolManager')
    def test_request_task_server_error(self, mock_pool_manager):
        """Test task request raises exception on server error."""
        mock_response = Mock()
        mock_response.status = 500
        mock_response.data = json.dumps({"message": "Internal server error"}).encode('utf-8')
        
        mock_http = Mock()
        mock_http.request.return_value = mock_response
        mock_pool_manager.return_value = mock_http
        
        task_requester = TaskRequester(self.base_url, self.api_key)
        
        with self.assertRaises(TaskRequesterException) as context:
            task_requester.request_task()
        
        self.assertIn("Server returned error", str(context.exception))
    
    def test_task_dataclass(self):
        """Test Task dataclass functionality."""
        task = Task(
            id="123e4567-e89b-12d3-a456-426614174000",
            ticket_id="DH-123",
            prompt="Work on ticket DH-123"
        )
        
        self.assertEqual(task.id, "123e4567-e89b-12d3-a456-426614174000")
        self.assertEqual(task.ticket_id, "DH-123")
        self.assertEqual(task.prompt, "Work on ticket DH-123")


if __name__ == '__main__':
    unittest.main()