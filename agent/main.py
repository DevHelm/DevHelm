import time
from ui_interaction import UIInteraction


def find_and_click_input_box():
    """
    Searches for a UI element with the text "Start Again".
    If found, it then uses givePrompt to find the input box, click it, and enter text.
    
    Now refactored to use the UIInteraction class with integrated input box logic.
    """
    ui = UIInteraction()
    
    try:
        # Check if the UI is ready for a prompt
        if ui.isReadyForPrompt():
            print("'Start Again' button found - UI is ready for prompt")
            
            # Give prompt now handles finding, clicking input box, and entering text
            if ui.givePrompt('Hello, World!'):
                print("Successfully found input box and entered prompt")
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
