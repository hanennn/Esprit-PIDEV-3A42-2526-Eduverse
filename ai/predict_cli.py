import os, sys, json, re
import joblib
import numpy as np
from sentence_transformers import SentenceTransformer

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, "duplicate_model_multilingual.joblib")

clf = joblib.load(MODEL_PATH)
emb_model = SentenceTransformer("distiluse-base-multilingual-cased-v1")

def tokens(s):
    s = str(s).lower()
    s = re.sub(r"[^a-z0-9àâçéèêëîïôùûüÿñ\s]", " ", s)
    return [t for t in s.split() if t]

def jaccard(a, b):
    sa, sb = set(tokens(a)), set(tokens(b))
    if not sa and not sb:
        return 0.0
    return len(sa & sb) / max(1, len(sa | sb))

def len_ratio(a, b):
    la, lb = len(str(a)), len(str(b))
    return min(la, lb) / max(1, max(la, lb))

def main():
    if len(sys.argv) < 3:
        print(json.dumps({"ok": False, "message": "Usage: predict_cli.py <q1> <q2>"}))
        return

    q1 = sys.argv[1]
    q2 = sys.argv[2]

    # ✅ ici on n'a plus joblib.load() ni SentenceTransformer()
    e1 = emb_model.encode([q1], normalize_embeddings=True)[0]
    e2 = emb_model.encode([q2], normalize_embeddings=True)[0]
    embed_cos = float(np.dot(e1, e2))

    jac = float(jaccard(q1, q2))
    lr  = float(len_ratio(q1, q2))

    proba = float(clf.predict_proba([[embed_cos, jac, lr]])[0][1])

    print(json.dumps({
        "ok": True,
        "probability": proba,
        "is_duplicate": int(proba >= 0.5),
    }))

if __name__ == "__main__":
    main()