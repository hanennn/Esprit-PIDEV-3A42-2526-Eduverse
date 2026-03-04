import logging
import librosa
import numpy as np

logger = logging.getLogger("interview-ia.audio")


def extraire_features(chemin_audio: str) -> dict:
    """
    Acoustic analysis of the audio file via librosa.
    Returns: speech rate, hesitations, vocal energy, duration.
    """
    y, sr = librosa.load(chemin_audio, sr=None, mono=True)
    duree = librosa.get_duration(y=y, sr=sr)

    if duree < 1.0:
        logger.warning("Audio too short: %.1fs", duree)
        return {
            "debit_mots_par_min": 0,
            "taux_hesitations_pct": 0.0,
            "energie_vocale": "faible",
            "duree_secondes": round(duree, 1),
        }

    # Speech segments (vs silences)
    intervalles = librosa.effects.split(y, top_db=30)
    nb_segments = len(intervalles)

    # Speech rate estimation (~3 words per speech segment)
    debit = int((nb_segments * 3) / (duree / 60)) if duree > 0 else 0

    # Hesitations (silences > 1.5 seconds between segments)
    silences_longs = 0
    for i in range(len(intervalles) - 1):
        duree_silence = (intervalles[i + 1][0] - intervalles[i][1]) / sr
        if duree_silence > 1.5:
            silences_longs += 1

    taux_hesitations = round(
        (silences_longs / max(nb_segments, 1)) * 100, 1
    )

    # Vocal energy (RMS)
    rms = float(np.mean(librosa.feature.rms(y=y)))
    if rms > 0.05:
        energie = "élevée"
    elif rms > 0.02:
        energie = "modérée"
    else:
        energie = "faible"

    return {
        "debit_mots_par_min": debit,
        "taux_hesitations_pct": taux_hesitations,
        "energie_vocale": energie,
        "duree_secondes": round(duree, 1),
    }
