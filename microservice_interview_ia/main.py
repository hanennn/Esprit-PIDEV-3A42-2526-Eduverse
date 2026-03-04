import logging
import os
import tempfile

from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware

from whisper_service import transcrire_audio
from emotion_service import analyser_emotions
from audio_features import extraire_features

# ── Configuration ────────────────────────────────────────────────────────────
ALLOWED_ORIGINS = os.getenv(
    "CORS_ORIGINS",
    "http://localhost:8000,http://127.0.0.1:8000"
).split(",")

# ── Logging ──────────────────────────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
)
logger = logging.getLogger("interview-ia")

# ── App ──────────────────────────────────────────────────────────────────────
app = FastAPI(title="EduVerse Interview IA")

app.add_middleware(
    CORSMiddleware,
    allow_origins=[o.strip() for o in ALLOWED_ORIGINS],
    allow_methods=["GET", "POST"],
    allow_headers=["*"],
)


@app.get("/")
def health():
    return {"status": "ok", "service": "EduVerse Interview IA"}


@app.post("/analyser-interview")
async def analyser(audio: UploadFile = File(...)):
    logger.info("Received audio file: %s (%s)", audio.filename, audio.content_type)

    # 1. Save to temp file
    with tempfile.NamedTemporaryFile(delete=False, suffix=".webm") as tmp:
        content = await audio.read()
        if len(content) == 0:
            raise HTTPException(status_code=400, detail="Fichier audio vide.")
        tmp.write(content)
        tmp_path = tmp.name

    try:
        # 2. Transcription (voice -> text)
        logger.info("Starting Whisper transcription...")
        transcription = transcrire_audio(tmp_path)
        logger.info("Transcription done (%d chars)", len(transcription))

        if not transcription or len(transcription.strip()) < 5:
            logger.warning("Transcription too short or empty")
            raise HTTPException(
                status_code=422,
                detail="La transcription est vide. Veuillez parler plus fort ou plus longtemps."
            )

        # 3. Emotion analysis (fine-tuned model)
        logger.info("Analyzing emotions...")
        emotions = analyser_emotions(transcription)
        logger.info("Emotions: %s", emotions)

        # 4. Audio features via librosa
        logger.info("Extracting audio features...")
        features = extraire_features(tmp_path)
        logger.info("Features: %s", features)

        # 5. Generate profile and recommendation
        profil = generer_profil(emotions, features)
        recommandation = generer_recommandation(emotions)

        logger.info("Analysis complete. Profile: %s", profil)

        return {
            "transcription": transcription,
            "scores_emotions": emotions,
            "features_audio": features,
            "profil_global": profil,
            "recommandation": recommandation,
        }

    except HTTPException:
        raise
    except Exception as e:
        logger.exception("Error during analysis")
        raise HTTPException(status_code=500, detail=f"Erreur d'analyse: {str(e)}")
    finally:
        if os.path.exists(tmp_path):
            os.unlink(tmp_path)


def generer_profil(emotions: dict, features: dict) -> str:
    dominant = max(emotions, key=emotions.get)

    descriptions = {
        "déterminé": "Étudiant très déterminé, montrant une forte volonté",
        "motivé": "Étudiant très motivé, exprimant passion et enthousiasme",
        "confiant": "Étudiant confiant, avec une assurance dans ses propos",
        "anxieux": "Étudiant montrant de l'anxiété dans son discours",
        "hésitant": "Étudiant hésitant, avec des incertitudes",
    }
    profil = descriptions.get(dominant, f"Étudiant {dominant}")

    # Audio-based modifiers
    if features.get("taux_hesitations_pct", 0) > 20:
        profil += ", discours avec hésitations fréquentes"
    if features.get("debit_mots_par_min", 130) > 160:
        profil += ", débit de parole rapide"
    elif features.get("debit_mots_par_min", 130) < 90:
        profil += ", débit de parole lent"
    if features.get("energie_vocale") == "élevée":
        profil += ", voix assurée"
    elif features.get("energie_vocale") == "faible":
        profil += ", voix faible"

    return profil + "."


def generer_recommandation(emotions: dict) -> str:
    det = emotions.get("déterminé", 0)
    mot = emotions.get("motivé", 0)
    anx = emotions.get("anxieux", 0)
    conf = emotions.get("confiant", 0)

    if (det + mot) / 2 > 0.70:
        return "Profil excellent. Candidat très motivé et déterminé. Fortement recommandé pour l'attribution."
    elif conf > 0.65:
        return "Profil solide. Candidat confiant et structuré. Recommandé pour l'attribution."
    elif anx > 0.60:
        return "Candidat montrant de l'anxiété. Un entretien de suivi est conseillé avant la décision finale."
    elif (det + mot + conf) / 3 > 0.40:
        return "Profil correct. Le candidat montre des qualités intéressantes. Attribution envisageable."
    else:
        return "Profil neutre. Une analyse complémentaire ou un entretien direct est recommandé."
