import sys
sys.stdout.reconfigure(encoding="utf-8")
sys.stderr.reconfigure(encoding="utf-8")

import json
import re
import fitz
import joblib
from sentence_transformers import SentenceTransformer

# ============================
# LOAD MODELS
# ============================

emb_model = SentenceTransformer("distiluse-base-multilingual-cased-v1")

clf_level = joblib.load("level_clf.joblib")
clf_topic = joblib.load("topic_clf.joblib")
clf_type = joblib.load("sentence_type_clf.joblib")

# ============================
# PDF EXTRACTION
# ============================

def extract_text(pdf_path):
    doc = fitz.open(pdf_path)
    text = ""
    for page in doc:
        text += page.get_text("text")
    return text

def clean_text(text):
    text = re.sub(r"\s+", " ", text)
    return text.strip()

# ============================
# PREDICTION FUNCTIONS
# ============================

def predict_level(text):
    emb = emb_model.encode([text], normalize_embeddings=True)
    return clf_level.predict(emb)[0]

def predict_topic(text):
    emb = emb_model.encode([text], normalize_embeddings=True)
    return clf_topic.predict(emb)[0]

def predict_type(sentence):
    emb = emb_model.encode([sentence], normalize_embeddings=True)
    return clf_type.predict(emb)[0]

# ============================
# SENTENCE SPLIT
# ============================

def split_sentences(text):
    sentences = re.split(r'(?<=[.!?]) +', text)
    clean = []
    for s in sentences:
        s = s.strip()
        if 50 < len(s) < 250:
            clean.append(s)
    return clean[:30]

# ============================
# QUESTION GENERATION LOGIC
# ============================

def generate_question_from_sentence(sentence, topic, level):
    sentence_type = predict_type(sentence)
    sentence = sentence.strip()

    # === DEFINITION ===
    if sentence_type == "definition":
        return f"Définir le concept suivant : {sentence[:80]}..."

    # === EXEMPLE ===
    if sentence_type == "exemple":
        return f"Donne un exemple pratique lié à : {sentence[:80]}..."

    # === COMPARAISON ===
    if sentence_type == "comparaison":
        return "Explique la différence entre les notions mentionnées dans ce passage."

    # === PROCEDURE ===
    if sentence_type == "procedure":
        return "Décris les étapes du processus présenté."

    # === REGLE ===
    if sentence_type == "regle":
        return "Explique la règle ou formule donnée et illustre avec un exemple."

    # fallback intelligent
    if level == "beginner":
        return "Explique l’idée principale de ce passage."
    elif level == "medium":
        return "Analyse le rôle du concept présenté dans ce passage."
    else:
        return "Analyse les implications avancées du concept présenté."

# ============================
# MAIN PIPELINE
# ============================

def main():
    try:
        if len(sys.argv) < 2:
            print(json.dumps({"ok": False, "message": "Usage: pdf_to_questions_cli.py <pdf_path>"}, ensure_ascii=False))
            return

        pdf_path = sys.argv[1]

        text = clean_text(extract_text(pdf_path))

        if len(text) < 200:
            print(json.dumps({"ok": False, "message": "PDF vide ou texte insuffisant."}, ensure_ascii=False))
            return

        level = predict_level(text)
        topic = predict_topic(text)

        sentences = split_sentences(text)

        questions = []
        for s in sentences:
            q = generate_question_from_sentence(s, topic, level)
            if q not in questions:
                questions.append(q)

        print(json.dumps({
            "ok": True,
            "topic": topic,
            "level": level,
            "questions": questions[:12]
        }, ensure_ascii=False))

    except Exception as e:
        print(json.dumps({"ok": False, "message": str(e)}, ensure_ascii=False))


if __name__ == "__main__":
    main()