import logging
import os
from transformers import pipeline

logger = logging.getLogger("interview-ia.emotion")

MODEL_PATH = os.getenv("EMOTION_MODEL_PATH", "./mon_modele_bourse")

# ── Load model ────────────────────────────────────────────────────────────────
if os.path.exists(MODEL_PATH):
    logger.info("Loading fine-tuned model from: %s", MODEL_PATH)
    classifier = pipeline(
        "text-classification",
        model=MODEL_PATH,
        tokenizer=MODEL_PATH,
        top_k=None,
    )
    logger.info("Fine-tuned model loaded successfully.")
else:
    logger.warning("Fine-tuned model not found at %s — using keyword fallback!", MODEL_PATH)
    classifier = None

EXPECTED_LABELS = ["déterminé", "anxieux", "confiant", "hésitant", "motivé"]


def analyser_emotions(texte: str) -> dict:
    """
    Analyze transcribed text and return a score per emotion.
    Returns: {"déterminé": 0.87, "anxieux": 0.12, ...}
    """
    if classifier is None:
        return _fallback_analyse(texte)

    # Truncate very long text to avoid model issues
    texte_tronque = texte[:512] if len(texte) > 512 else texte

    try:
        results = classifier(texte_tronque)[0]
        return {r["label"]: round(r["score"], 3) for r in results}
    except Exception as e:
        logger.error("Model inference failed, using fallback: %s", e)
        return _fallback_analyse(texte)


def _fallback_analyse(texte: str) -> dict:
    """
    Basic keyword-based analysis when the fine-tuned model is not available.
    """
    texte_lower = texte.lower()

    mots_determine = ["déterminé", "convaincu", "sûr", "certain", "absolument", "engagé", "persévérer", "volonté"]
    mots_motiv = ["motivé", "passion", "envie", "objectif", "ambition", "réussir", "enthousiaste", "rêve"]
    mots_confiant = ["confiant", "capable", "compétent", "expérience", "mérite", "assurance", "fierté"]
    mots_anxieux = ["inquiet", "nerveux", "peur", "anxieux", "stress", "difficile", "préoccupé", "soucieux"]
    mots_hesitant = ["peut-être", "je ne sais", "hm", "euh", "bof", "pas sûr", "hésiter", "incertain"]

    def score(mots):
        hits = sum(1 for m in mots if m in texte_lower)
        return round(min(hits / max(len(mots), 1), 1.0), 3)

    scores = {
        "déterminé": score(mots_determine),
        "motivé": score(mots_motiv),
        "confiant": score(mots_confiant),
        "anxieux": score(mots_anxieux),
        "hésitant": score(mots_hesitant),
    }

    # Normalize so scores sum to ~1
    total = sum(scores.values()) or 1
    return {k: round(v / total, 3) for k, v in scores.items()}
