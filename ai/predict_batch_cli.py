import os, sys, json, re, warnings, logging
import joblib
import numpy as np
from sentence_transformers import SentenceTransformer

# ✅ réduire les logs HF/transformers
os.environ["TOKENIZERS_PARALLELISM"] = "false"
os.environ["HF_HUB_DISABLE_TELEMETRY"] = "1"
os.environ["HF_HUB_DISABLE_PROGRESS_BARS"] = "1"
warnings.filterwarnings("ignore")
logging.getLogger().setLevel(logging.ERROR)

# ✅ chemins ABSOLUS (critique)
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, "duplicate_model_multilingual.joblib")
EMB_NAME = "distiluse-base-multilingual-cased-v1"

def safe_print(obj):
    sys.stdout.write(json.dumps(obj, ensure_ascii=False))
    sys.stdout.flush()

# ✅ chargement
try:
    clf = joblib.load(MODEL_PATH)
except Exception as e:
    safe_print({"ok": False, "message": f"Erreur load model: {e}", "model_path": MODEL_PATH})
    sys.exit(0)

try:
    emb_model = SentenceTransformer(EMB_NAME)
except Exception as e:
    safe_print({"ok": False, "message": f"Erreur SentenceTransformer: {e}"})
    sys.exit(0)

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

def proba(a, b):
    e = emb_model.encode([a, b], normalize_embeddings=True)
    embed_cos = float(np.dot(e[0], e[1]))
    jac = float(jaccard(a, b))
    lr  = float(len_ratio(a, b))
    return float(clf.predict_proba([[embed_cos, jac, lr]])[0][1])

def main():
    try:
        raw = sys.stdin.read().strip()
        if not raw:
            safe_print({"ok": False, "message": "stdin vide"})
            return

        payload = json.loads(raw)
        new_text = (payload.get("new_text") or "").strip()
        candidates = payload.get("candidates") or []

        if not new_text or not isinstance(candidates, list):
            safe_print({"ok": False, "message": "Bad payload (need new_text + candidates[])"})
            return

        best_p = 0.0
        best_idx = None
        best_text = ""

        for i, old in enumerate(candidates):
            old = str(old or "").strip()
            if not old:
                continue

            p = proba(new_text, old)
            if p > best_p:
                best_p, best_idx, best_text = p, i, old

        safe_print({
            "ok": True,
            "bestProbability": best_p,
            "bestIndex": best_idx,
            "bestQuestionText": best_text,
        })

    except Exception as e:
        safe_print({"ok": False, "message": str(e)})

if __name__ == "__main__":
    main()