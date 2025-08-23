DevHelm Agent (Python)
======================

Overview
--------
This is a lightweight screen-automation agent used by the DevHelm project. It
leverages PyAutoGUI and OpenCV to locate UI elements on the screen and interact
with them automatically.

Important: This agent looks for two images on your screen and interacts with the UI:
- start_again.png — an image of the "Start Again" button or text
- type_your.png — an image of the "Type your" label adjacent to an input field

When it finds them, it clicks to the right of the "Type your" label and types a
message, then presses Enter. The search runs continuously.

Prerequisites
-------------
- Python 3.8+
- A desktop environment (PyAutoGUI needs access to the display)
- The reference images (PNG): start_again.png and type_your.png placed in the
  working directory where you run the agent (or adjust the code to absolute paths)

Install (option A: using requirements.txt)
-----------------------------------------
```
python -m venv .venv
source .venv/bin/activate  # Windows: .venv\\Scripts\\activate
pip install -r requirements.txt
sudo apt install gnome-screenshot
```

Run directly:
```
python main.py
```

Install (option B: as a package via pyproject)
---------------------------------------------
```
python -m venv .venv
source .venv/bin/activate
pip install .
```

This will install a console script named `devhelm-agent`.

Run as console script:
```
devhelm-agent
```

How it works
------------
- Searches for start_again.png (grayscale; confidence 0.9)
- If found, searches for type_your.png
- Clicks to the right of the label and types a message, then presses Enter
- Loops indefinitely with short sleeps to avoid overloading the system

Notes and Tips
--------------
- Confidence-based image search requires OpenCV; we include opencv-python as a dependency.
- Make sure your screenshots match the on-screen scale. If you use display scaling,
  capture reference images at the same scale.
- You can adjust search confidence, offsets, and delays in `main.py`.
- Running on headless systems is not supported without a virtual display (e.g., Xvfb on Linux).

Safety
------
This agent will move the mouse and type. Be cautious when running it on your main
machine—save your work and ensure it targets the right window.

License
-------
Please see the repository root LICENSE.md for license details.
