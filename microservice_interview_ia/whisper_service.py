import logging
import os
import whisper

logger = logging.getLogger("interview-ia.whisper")

# Model size: "tiny" (fast), "base", "small", "medium" (recommended), "large" (slow)
# Use WHISPER_MODEL env var to override, default to "small" for speed/quality balance
WHISPER_MODEL_SIZE = os.getenv("WHISPER_MODEL", "small")

logger.info("Loading Whisper model '%s'...", WHISPER_MODEL_SIZE)
model = whisper.load_model(WHISPER_MODEL_SIZE)
logger.info("Whisper model '%s' loaded successfully.", WHISPER_MODEL_SIZE)


def transcrire_audio(chemin_audio: str) -> str:
    """
    Transcribe an audio file to French text using local Whisper.
    No data is sent to OpenAI — everything runs locally.
    """
    result = model.transcribe(
        chemin_audio,
        language="fr",
        task="transcribe",
        fp16=False,  # Disable FP16 if no GPU
    )
    return result["text"].strip()
