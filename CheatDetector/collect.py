import mediapipe as mp
import cv2
import csv
import time
import os
import urllib.request

# Download model if not already present
if not os.path.exists("face_landmarker.task"):
    print("Downloading face landmarker model...")
    urllib.request.urlretrieve(
        "https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/1/face_landmarker.task",
        "face_landmarker.task"
    )

BaseOptions = mp.tasks.BaseOptions
FaceLandmarker = mp.tasks.vision.FaceLandmarker
FaceLandmarkerOptions = mp.tasks.vision.FaceLandmarkerOptions
VisionRunningMode = mp.tasks.vision.RunningMode

options = FaceLandmarkerOptions(
    base_options=BaseOptions(model_asset_path='face_landmarker.task'),
    running_mode=VisionRunningMode.IMAGE,
    output_face_blendshapes=False,
    num_faces=1
)

# ---- Settings ----
WINDOW_SIZE = 90       # 2 seconds at ~30fps
STEP_SIZE = 15          # slide window every 0.5 seconds (overlap)
OUTPUT_FILE = "eye_data.csv"

# ---- State ----
current_label = None    # 'straight' or 'cheat'
frame_buffer = []       # rolling buffer of (lx, ly, rx, ry)
sequences = []          # finished labeled sequences

def save_sequences_to_csv(sequences, filename):
    with open(filename, "a", newline="") as f:
        writer = csv.writer(f)
        # Header: frame_0_lx, frame_0_ly, frame_0_rx, frame_0_ry, frame_1_lx, ... label
        header = []
        for i in range(WINDOW_SIZE):
            header += [f"f{i}_lx", f"f{i}_ly", f"f{i}_rx", f"f{i}_ry"]
        header.append("label")
        writer.writerow(header)
        for seq, label in sequences:
            row = [val for frame in seq for val in frame]
            row.append(label)
            writer.writerow(row)
    print(f"\n✅ Saved {len(sequences)} sequences to {filename}")

cap = cv2.VideoCapture(0)
print("\n========================================")
print("  EYE DATA COLLECTOR")
print("========================================")
print("  Press 'S' → record STRAIGHT (honest)")
print("  Press 'C' → record CHEAT (looking away)")
print("  Press 'P' → pause recording")
print("  Press 'Q' → quit and save")
print("========================================\n")

with FaceLandmarker.create_from_options(options) as landmarker:
    while True:
        ret, frame = cap.read()
        if not ret:
            break

        rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        mp_image = mp.Image(image_format=mp.ImageFormat.SRGB, data=rgb)
        result = landmarker.detect(mp_image)

        if result.face_landmarks:
            landmarks = result.face_landmarks[0]
            left_iris  = landmarks[468]
            right_iris = landmarks[473]

            lx, ly = left_iris.x, left_iris.y
            rx, ry = right_iris.x, right_iris.y

            # Draw iris points
            h, w, _ = frame.shape
            cv2.circle(frame, (int(lx * w), int(ly * h)), 5, (0, 255, 0), -1)
            cv2.circle(frame, (int(rx * w), int(ry * h)), 5, (0, 255, 0), -1)

            # If recording, add to buffer
            if current_label is not None:
                frame_buffer.append((lx, ly, rx, ry))

                # Once we have enough frames, save a window and slide
                if len(frame_buffer) >= WINDOW_SIZE:
                    window = frame_buffer[:WINDOW_SIZE]
                    sequences.append((window, current_label))
                    frame_buffer = frame_buffer[STEP_SIZE:]  # slide

        # ---- HUD ----
        status_color = (0, 200, 0) if current_label == "straight" else \
                       (0, 0, 255) if current_label == "cheat" else \
                       (150, 150, 150)

        status_text = f"Recording: {current_label.upper()}" if current_label else "Paused — press S or C to record"
        cv2.putText(frame, status_text, (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.8, status_color, 2)
        cv2.putText(frame, f"Sequences saved: {len(sequences)}", (10, 65), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
        cv2.putText(frame, "S=Straight  C=Cheat  P=Pause  Q=Quit", (10, frame.shape[0] - 15),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.55, (200, 200, 200), 1)

        cv2.imshow('Eye Data Collector', frame)

        key = cv2.waitKey(1) & 0xFF
        if key == ord('s'):
            current_label = "straight"
            frame_buffer = []
            print("▶ Recording STRAIGHT")
        elif key == ord('c'):
            current_label = "cheat"
            frame_buffer = []
            print("▶ Recording CHEAT")
        elif key == ord('p'):
            current_label = None
            frame_buffer = []
            print("⏸ Paused")
        elif key == ord('q'):
            break

cap.release()
cv2.destroyAllWindows()

if sequences:
    save_sequences_to_csv(sequences, OUTPUT_FILE)
    straight_count = sum(1 for _, l in sequences if l == "straight")
    cheat_count    = sum(1 for _, l in sequences if l == "cheat")
    print(f"   Straight sequences: {straight_count}")
    print(f"   Cheat sequences:    {cheat_count}")
else:
    print("No sequences recorded.")