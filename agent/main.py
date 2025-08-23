import os
import sys
import time
from typing import Optional, Tuple

from task_requester import TaskRequester, Task, TaskStatus, TaskRequesterException
from ui_interaction import UIInteraction
from logger_factory import LoggerFactory

# Initialize logger using the factory
logger = LoggerFactory.get_logger()


def get_environment_variables() -> Tuple[str, str]:
    """
    Read required environment variables for ComControl API access.
    
    Returns:
        tuple: (api_url, api_key) from environment variables
        
    Raises:
        SystemExit: If required environment variables are not set
    """
    api_url = os.getenv('BASE_URL')
    api_key = os.getenv('API_KEY')
    
    if not api_url:
        logger.error("BASE_URL environment variable is not set")
        sys.exit(1)
        
    if not api_key:
        logger.error("API_KEY environment variable is not set")
        sys.exit(1)
    
    return api_url, api_key


def fetch_initial_task(task_requester: TaskRequester) -> Task:
    """
    Fetch the initial task on startup.
    
    Args:
        task_requester: TaskRequester instance for API calls
        
    Returns:
        Task: The initial task to process
        
    Raises:
        SystemExit: If no initial task is available
    """
    try:
        result = task_requester.request_task()
        
        if isinstance(result, Task):
            logger.info(f"Initial task received: {result.ticket_id} - {result.prompt}")
            return result
        else:
            logger.warning(f"No initial task available: {result.value}")
            sys.exit(1)
            
    except TaskRequesterException as e:
        logger.error(f"Error fetching initial task: {e}")
        sys.exit(1)


def main():
    """
    Main agent runtime implementing the business logic from the agent overview.
    
    This implementation follows the acceptance criteria:
    - Reads environment variables for API access
    - Fetches initial task on startup (exits if none available)
    - Runs infinite loop checking UI readiness every minute
    - Requests new tasks and handles responses appropriately
    - Sleeps 60 seconds between loop iterations
    """
    logger.info("Starting DevHelm Agent...")
    
    # Read environment variables
    api_url, api_key = get_environment_variables()
    
    # Initialize components
    task_requester = TaskRequester(api_url, api_key)
    ui = UIInteraction()
    
    # Fetch initial task (exit if none available)
    current_task = fetch_initial_task(task_requester)
    
    logger.info("Entering main runtime loop...")
    
    # Main runtime loop
    while True:
        try:
            # Check if UI is ready for a prompt (looking for "Start Again" button)
            if ui.isReadyForPrompt():
                logger.debug("UI is ready for prompt - 'Start Again' button detected")
                
                # Sleep to avoid race conditions as specified in business logic
                time.sleep(60)
                
                # Request a new task
                try:
                    result = task_requester.request_task()
                    
                    if isinstance(result, Task):
                        # New task received - update current task and give prompt
                        current_task = result
                        logger.info(f"New task received: {current_task.ticket_id} - {current_task.prompt}")
                        
                        success = ui.givePrompt(current_task.prompt)
                        if success:
                            logger.info("Successfully entered new task prompt")
                        else:
                            logger.error("Failed to enter task prompt")
                            
                    elif result == TaskStatus.BUSY:
                        # DevHelm says still busy - tell Junie to continue
                        logger.info("DevHelm indicates task still in progress - telling Junie to continue")
                        
                        success = ui.givePrompt("continue")
                        if success:
                            logger.info("Successfully entered 'continue' prompt")
                        else:
                            logger.error("Failed to enter 'continue' prompt")
                            
                    elif result == TaskStatus.NONE:
                        # DevHelm has no tasks - do nothing
                        logger.debug("DevHelm has no tasks available - doing nothing")
                        
                except TaskRequesterException as e:
                    logger.error(f"Error requesting task: {e}")
                    
            else:
                # UI not ready - just wait
                logger.debug("UI not ready for prompt - waiting...")
            
            # Sleep for 60 seconds as specified in acceptance criteria
            time.sleep(60)
            
        except KeyboardInterrupt:
            logger.info("Shutting down agent...")
            break
        except Exception as e:
            logger.error(f"Unexpected error in main loop: {e}")
            time.sleep(60)  # Continue after error


if __name__ == "__main__":
    main()
