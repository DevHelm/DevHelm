import time
from ui_interaction import UIInteraction


def find_and_click_input_box():
    """
    Searches for a UI element with the text "Start Again".
    If found, it then looks for the text "Type your" and clicks
    to the right of it, assuming an input box is there.
    
    Now refactored to use the UIInteraction class.
    """
    ui = UIInteraction()
    
    try:
        # Check if the UI is ready for a prompt
        if ui.isReadyForPrompt():
            print("'Start Again' button found - UI is ready for prompt")
            
            # Try to find and click the input box
            if ui._find_and_click_input_box():
                print("Successfully clicked the input box")
                
                # Example: You can now use the UI interaction methods
                ui.givePrompt('Hello, World!')
            else:
                print("Could not find or click the input box")
                time.sleep(60)
        else:
            print("UI not ready for prompt - waiting...")
            time.sleep(60)
            
    except Exception as e:
        print(f"Error during UI interaction: {e}")
        time.sleep(60)


def main():
    """Console entry point that continuously searches and clicks the input box."""
    while True:
        find_and_click_input_box()


if __name__ == "__main__":
    main()
