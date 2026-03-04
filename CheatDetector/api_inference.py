import sys
import json
import torch
import torch.nn as nn
import numpy as np
import joblib
import os

# --- Model Definition (Must match the training architecture) ---
class CheatDetectorModel(nn.Module):
    def __init__(self):
        super().__init__()
        self.lstm1 = nn.LSTM(input_size=4, hidden_size=64, batch_first=True)
        self.drop1 = nn.Dropout(0.3)
        self.lstm2 = nn.LSTM(input_size=64, hidden_size=32, batch_first=True)
        self.drop2 = nn.Dropout(0.3)
        self.fc1   = nn.Linear(32, 16)
        self.relu  = nn.ReLU()
        self.fc2   = nn.Linear(16, 1)

    def forward(self, x):
        x, _ = self.lstm1(x)
        x    = self.drop1(x)
        x, _ = self.lstm2(x)
        x    = x[:, -1, :] # Last sequence output
        x    = self.drop2(x)
        x    = self.relu(self.fc1(x))
        x    = self.fc2(x)
        return x

def main():
    # Set relative paths to local directory
    dir_path = os.path.dirname(os.path.realpath(__file__))
    model_path = os.path.join(dir_path, 'cheat_detector.pth')
    scaler_path = os.path.join(dir_path, 'scaler.pkl')
    label_path = os.path.join(dir_path, 'label_classes.npy')

    # 1. Load Model & Assets
    try:
        model = CheatDetectorModel()
        model.load_state_dict(torch.load(model_path, map_location=torch.device('cpu')))
        model.eval()

        scaler = joblib.load(scaler_path)
        classes = np.load(label_path, allow_pickle=True)
    except Exception as e:
        print(json.dumps({"success": False, "error": f"Initialization error: {str(e)}"}))
        return

    # 2. Read input from stdin
    try:
        input_data = sys.stdin.read()
        if not input_data:
            return

        data = json.loads(input_data)
        sequence = data.get('sequence', [])

        if len(sequence) < 90:
             print(json.dumps({"success": False, "error": f"Invalid sequence length: {len(sequence)}/90"}))
             return

        # 3. Inference
        arr = np.array(sequence, dtype=np.float32)
        arr = scaler.transform(arr) # Standardize
        tensor = torch.tensor(arr).unsqueeze(0) # Add batch dimension

        with torch.no_grad():
            output = model(tensor)
            prob = torch.sigmoid(output).item()

        label = classes[int(prob > 0.5)]

        # 4. Return JSON to stdout
        print(json.dumps({
            "success": True,
            "label": str(label),
            "probability": float(prob)
        }))

    except Exception as e:
        print(json.dumps({"success": False, "error": f"Inference error: {str(e)}"}))

if __name__ == "__main__":
    main()
