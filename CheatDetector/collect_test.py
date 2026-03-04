import mediapipe as mp
import cv2
import numpy as np
import torch
import torch.nn as nn
import joblib

class CheatDetector(nn.Module):
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
        x    = x[:, -1, :]
        x    = self.drop2(x)
        x    = self.relu(self.fc1(x))
        x    = self.fc2(x)
        return x

model = CheatDetector()
model.load_state_dict(torch.load('cheat_detector.pth'))
model.eval()

scaler  = joblib.load('scaler.pkl')
classes = np.load('label_classes.npy', allow_pickle=True)

def predict_sequence(sequence):
    arr    = np.array(sequence, dtype=np.float32)
    arr    = scaler.transform(arr)
    tensor = torch.tensor(arr).unsqueeze(0)
    with torch.no_grad():
        prob = torch.sigmoid(model(tensor)).item()
    label = classes[int(prob > 0.5)]
    return label, prob

BaseOptions       = mp.tasks.BaseOptions
FaceLandmarker    = mp.tasks.vision.FaceLandmarker
FaceLandmarkerOptions = mp.tasks.vision.FaceLandmarkerOptions
VisionRunningMode = mp.tasks.vision.RunningMode

options = FaceLandmarkerOptions(
    base_options=BaseOptions(model_asset_path='face_landmarker.task'),
    running_mode=VisionRunningMode.IMAGE,
    num_faces=1
)

WINDOW_SIZE  = 90
frame_buffer = []
recording    = False
mode         = None
result_text  = ""
result_color = (255, 255, 255)

cap = cv2.VideoCapture(0)

with FaceLandmarker.create_from_options(options) as landmarker:
    while True:
        ret, frame = cap.read()
        if not ret:
            break

        rgb       = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        mp_image  = mp.Image(image_format=mp.ImageFormat.SRGB, data=rgb)
        detection = landmarker.detect(mp_image)

        if detection.face_landmarks:
            landmarks  = detection.face_landmarks[0]
            left_iris  = landmarks[468]
            right_iris = landmarks[473]

            lx, ly = left_iris.x,  left_iris.y
            rx, ry = right_iris.x, right_iris.y

            h, w, _ = frame.shape
            cv2.circle(frame, (int(lx*w), int(ly*h)), 5, (0,255,0), -1)
            cv2.circle(frame, (int(rx*w), int(ry*h)), 5, (0,255,0), -1)

            if recording:
                frame_buffer.append((lx, ly, rx, ry))

                if len(frame_buffer) >= WINDOW_SIZE:
                    recording    = False
                    label, prob  = predict_sequence(frame_buffer[:WINDOW_SIZE])
                    frame_buffer = []
                    correct      = label == mode
                    result_text  = "correct" if correct else "wrong"
                    result_color = (0, 255, 0) if correct else (0, 0, 255)
                    print(result_text)

        if recording:
            cv2.putText(frame, f"recording... {len(frame_buffer)}/{WINDOW_SIZE}",
                        (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0,165,255), 2)
        else:
            cv2.putText(frame, "S=Straight  C=Cheat  Q=Quit",
                        (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (200,200,200), 2)

        if result_text:
            cv2.putText(frame, result_text,
                        (10, 70), cv2.FONT_HERSHEY_SIMPLEX, 0.8, result_color, 2)

        cv2.imshow('Cheat Detector', frame)

        key = cv2.waitKey(1) & 0xFF
        if key == ord('s') and not recording:
            frame_buffer = []
            recording    = True
            mode         = 'straight'
            result_text  = ""
        elif key == ord('c') and not recording:
            frame_buffer = []
            recording    = True
            mode         = 'cheat'
            result_text  = ""
        elif key == ord('q'):
            break

cap.release()
cv2.destroyAllWindows()