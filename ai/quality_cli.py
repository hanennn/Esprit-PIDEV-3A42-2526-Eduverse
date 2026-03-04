import sys, json, re
import joblib
import numpy as np
from sentence_transformers import SentenceTransformer

MODEL_PATH = "question_quality_model.joblib"

def basic_features(text: str):
    t = text.strip()
    t_low = t.lower()

    n_chars = len(t)
    n_words = len([w for w in re.split(r"\s+", t) if w])
    has_qmark = 1 if "?" in t else 0
    ends_qmark = 1 if t.endswith("?") else 0
    starts_interrog = 1 if re.match(r"^(qu|que|quoi|comment|pourquoi|où|quand|quel|quelle|quels|quelles)\b", t_low) else 0
    very_short = 1 if n_words <= 2 else 0

    return np.array([n_chars, n_words, has_qmark, ends_qmark, starts_interrog, very_short], dtype=float)

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"ok": False, "message": "Usage: quality_cli.py <question>"}))
        return

    text = sys.argv[1].strip()
    if not text:
        print(json.dumps({"ok": False, "message": "Question vide"}))
        return

    clf = joblib.load(MODEL_PATH)
    emb_model = SentenceTransformer("distiluse-base-multilingual-cased-v1")

    emb = emb_model.encode([text], normalize_embeddings=True)
    feats = basic_features(text).reshape(1, -1)
    X = np.hstack([emb, feats])

    p_bad = float(clf.predict_proba(X)[0][1])
    quality = 1.0 - p_bad

    # 🔥 Niveaux
    if quality >= 0.85:
        level = "good"
        message = "Question claire et bien formulée."
    elif quality >= 0.60:
        level = "medium"
        message = "Question correcte mais peut être améliorée."
    else:
        level = "bad"
        message = "Question mal formulée ou trop courte."

    print(json.dumps({
        "ok": True,
        "quality": quality,
        "level": level,
        "message": message
    }))

if __name__ == "__main__":
    main()