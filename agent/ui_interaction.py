"""
UI Interaction module for automating GUI interactions.

This module provides the UIInteraction class that handles all pyautogui-based
UI automation tasks. It encapsulates screen detection, clicking, and text input
functionality in a clean interface that can be easily swapped out later.
"""

import os
import pyautogui
import time
from pathlib import Path


class UIInteraction:
    """
    Handles all GUI interaction tasks using pyautogui.
    
    This class provides methods for detecting UI elements on screen,
    clicking elements, and entering text. All image detection resources
    are stored in a dedicated images folder.
    """
    
    def __init__(self):
        """
        Initialize the UIInteraction class.
        
        No parameters are accepted as per the requirements.
        Sets up the path to the images directory.
        """
        # Get the directory where this file is located
        current_dir = Path(__file__).parent
        self.images_dir = current_dir / "images"
        
        # Ensure images directory exists
        if not self.images_dir.exists():
            raise FileNotFoundError(f"Images directory not found: {self.images_dir}")
    
    def isReadyForPrompt(self) -> bool:
        """
        Check if the UI is ready for a prompt by looking for start_again.png.
        
        Returns:
            bool: True if start_again.png is found on screen, False otherwise
        """
        try:
            start_again_path = self.images_dir / "start_again.png"
            if not start_again_path.exists():
                raise FileNotFoundError(f"start_again.png not found in {self.images_dir}")
            
            location = pyautogui.locateOnScreen(
                str(start_again_path),
                confidence=0.9,
                grayscale=True
            )
            
            return location is not None
            
        except pyautogui.ImageNotFoundException:
            return False
        except Exception:
            return False
    
    def continuePrompt(self):
        """
        Enter "continue" into the prompt box and press enter.
        
        This method assumes the prompt box is already active/focused.
        Note: Method renamed from 'continue' to avoid Python reserved keyword.
        """
        try:
            pyautogui.write('continue', interval=0.1)
            pyautogui.press('enter')
            
        except Exception as e:
            # Re-raise the exception to let the caller handle it
            raise e
    
    def givePrompt(self, prompt: str):
        """
        Enter the provided prompt string into the prompt box and press enter.
        
        Args:
            prompt (str): The prompt text to enter
            
        This method assumes the prompt box is already active/focused.
        """
        try:
            if not isinstance(prompt, str):
                raise ValueError("Prompt must be a string")
            
            pyautogui.write(prompt, interval=0.1)
            pyautogui.press('enter')
            
        except Exception as e:
            # Re-raise the exception to let the caller handle it
            raise e
    
    def _find_and_click_input_box(self):
        """
        Private method to locate and click the input box.
        
        This method replicates the existing logic from main.py
        for finding and clicking the input box after detecting
        the start_again element.
        
        Returns:
            bool: True if successfully clicked the input box, False otherwise
        """
        try:
            # Check if we're ready for prompt first
            if not self.isReadyForPrompt():
                return False
            
            # Look for the "Type your" label
            type_your_path = self.images_dir / "type_your.png"
            if not type_your_path.exists():
                raise FileNotFoundError(f"type_your.png not found in {self.images_dir}")
            
            input_label_location = pyautogui.locateOnScreen(
                str(type_your_path),
                confidence=0.9,
                grayscale=True
            )
            
            if input_label_location:
                # Calculate the click position to the right of the label
                x_offset = input_label_location.width + 10
                center_x, center_y = pyautogui.center(input_label_location)
                click_x = center_x + x_offset
                click_y = center_y
                
                # Click the input box
                pyautogui.click(click_x, click_y)
                
                # Add a short delay to allow the system to register the click
                time.sleep(1)
                
                return True
            
            return False
            
        except pyautogui.ImageNotFoundException:
            return False
        except Exception:
            return False