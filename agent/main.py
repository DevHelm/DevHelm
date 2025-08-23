import pyautogui
import time


def find_and_click_input_box():
    """
    Searches for a UI element with the text "Start Again".
    If found, it then looks for the text "Type your" and clicks
    to the right of it, assuming an input box is there.
    """
    try:
        # Step 1: Locate the "Start Again" button/text on the screen.
        # You need to replace the path below with the location of your saved PNG image.
        start_button_location = pyautogui.locateOnScreen(
            'start_again.png',
            confidence=0.9,  # Adjust confidence (0.0 to 1.0) as needed
            grayscale=True  # Use grayscale for better performance and consistency
        )

        if start_button_location:
            print(f"'Start Again' button found at: {start_button_location}")

            # Step 2: Now that we've found the first element,
            # search for the "Type your" label.
            # Replace the path below with your saved PNG image of the label.
            input_label_location = pyautogui.locateOnScreen(
                'type_your.png',
                confidence=0.9,
                grayscale=True
            )

            if input_label_location:
                print(f"'Type your' label found at: {input_label_location}")

                # Step 3: Calculate the click position.
                # Assuming the input box is to the right of the label.
                # We'll click slightly to the right of the center of the label.
                # Adjust the 'x_offset' to match your UI layout.
                x_offset = input_label_location.width + 10

                # Get the center coordinates of the label
                center_x, center_y = pyautogui.center(input_label_location)

                # Calculate the final click coordinates for the input box
                click_x = center_x + x_offset
                click_y = center_y

                # Step 4: Click the calculated position to activate the input box.
                print(f"Clicking the input box at ({click_x}, {click_y})...")
                pyautogui.click(click_x, click_y)

                print("Clicked successfully! The input box should now be active.")

                # Add a short delay to allow the system to register the click.
                time.sleep(1)

                # Example: You can now type into the box if needed.
                pyautogui.write('Hello, World!', interval=0.1)
                pyautogui.press('enter')

            else:
                time.sleep(60)
        else:
            time.sleep(60)

    except pyautogui.ImageNotFoundException:
        time.sleep(60)
    except Exception:
        time.sleep(60)


def main():
    """Console entry point that continuously searches and clicks the input box."""
    while True:
        find_and_click_input_box()


if __name__ == "__main__":
    main()
