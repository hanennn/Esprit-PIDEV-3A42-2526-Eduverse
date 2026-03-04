# ============================================================
# COLLE CE CODE DANS GOOGLE COLAB CELLULE PAR CELLULE
# URL : https://colab.research.google.com
# Runtime → Change runtime type → GPU (T4)
# ============================================================

# ── CELLULE 1 : Installer les librairies ────────────────────
!pip install transformers datasets scikit-learn torch -q

# ── CELLULE 2 : Uploader ton dataset.csv ────────────────────
from google.colab import files
uploaded = files.upload()
# → Sélectionne ton fichier dataset.csv quand la fenêtre s'ouvre

# ── CELLULE 3 : Charger et préparer les données ─────────────
import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from datasets import Dataset

# Charge le dataset
df = pd.read_csv("dataset.csv")
print(f"✅ Dataset chargé : {len(df)} exemples")
print(df["label"].value_counts())

# Convertit les labels en chiffres
labels = ["déterminé", "anxieux", "confiant", "hésitant", "motivé"]
label2id = {l: i for i, l in enumerate(labels)}
id2label  = {i: l for i, l in enumerate(labels)}
df["label"] = df["label"].map(label2id)

# Vérifie qu'il n'y a pas de NaN
print(f"\n❌ Labels inconnus : {df['label'].isna().sum()}")
df = df.dropna(subset=["label"])
df["label"] = df["label"].astype(int)

# Split train 80% / test 20%
train_df, test_df = train_test_split(df, test_size=0.2, random_state=42, stratify=df["label"])
print(f"\n📊 Train : {len(train_df)} | Test : {len(test_df)}")

train_dataset = Dataset.from_pandas(train_df.reset_index(drop=True))
test_dataset  = Dataset.from_pandas(test_df.reset_index(drop=True))

# ── CELLULE 4 : Charger camembert-base et tokenizer ─────────
from transformers import AutoTokenizer, AutoModelForSequenceClassification

model_name = "camembert-base"   # Modèle français pré-entraîné
tokenizer  = AutoTokenizer.from_pretrained(model_name)

model = AutoModelForSequenceClassification.from_pretrained(
    model_name,
    num_labels=len(labels),
    id2label=id2label,
    label2id=label2id,
)
print(f"✅ camembert-base chargé — {sum(p.numel() for p in model.parameters()):,} paramètres")

# ── CELLULE 5 : Tokenizer les données ───────────────────────
def tokenize_fn(batch):
    return tokenizer(
        batch["texte"],
        truncation=True,
        padding="max_length",
        max_length=128,
    )

train_dataset = train_dataset.map(tokenize_fn, batched=True)
test_dataset  = test_dataset.map(tokenize_fn, batched=True)

# Colonnes nécessaires pour PyTorch
train_dataset = train_dataset.rename_column("label", "labels")
test_dataset  = test_dataset.rename_column("label", "labels")
train_dataset.set_format("torch", columns=["input_ids", "attention_mask", "labels"])
test_dataset.set_format("torch",  columns=["input_ids", "attention_mask", "labels"])

print("✅ Tokenisation terminée")

# ── CELLULE 6 : Métriques d'évaluation ──────────────────────
from sklearn.metrics import f1_score, accuracy_score, classification_report

def compute_metrics(eval_pred):
    logits, labels_true = eval_pred
    preds = np.argmax(logits, axis=1)
    f1  = f1_score(labels_true, preds, average="weighted")
    acc = accuracy_score(labels_true, preds)
    return {"f1": f1, "accuracy": acc}

# ── CELLULE 7 : Configuration de l'entraînement ─────────────
from transformers import TrainingArguments, Trainer

training_args = TrainingArguments(
    output_dir="./resultats",
    num_train_epochs=5,              # 5 passages complets sur tes données
    per_device_train_batch_size=8,   # Réduit si erreur mémoire GPU
    per_device_eval_batch_size=8,
    warmup_steps=30,
    weight_decay=0.01,
    logging_dir="./logs",
    logging_steps=10,
    evaluation_strategy="epoch",     # Évalue après chaque epoch
    save_strategy="epoch",
    load_best_model_at_end=True,     # Garde le meilleur modèle
    metric_for_best_model="f1",
    greater_is_better=True,
    report_to="none",                # Pas de wandb
)

trainer = Trainer(
    model=model,
    args=training_args,
    train_dataset=train_dataset,
    eval_dataset=test_dataset,
    tokenizer=tokenizer,
    compute_metrics=compute_metrics,
)

# ── CELLULE 8 : LANCER L'ENTRAÎNEMENT ───────────────────────
# ⏳ Environ 20-30 minutes sur GPU T4 Colab
print("🚀 Début de l'entraînement...")
print("Tu verras la loss diminuer et le F1 augmenter à chaque epoch.")
print("=" * 60)

trainer.train()

print("\n✅ Entraînement terminé !")

# ── CELLULE 9 : Évaluation finale ───────────────────────────
print("\n📊 Évaluation sur le jeu de test :")
results = trainer.evaluate()
print(f"  F1-score  : {results['eval_f1']:.4f}")
print(f"  Accuracy  : {results['eval_accuracy']:.4f}")

# Rapport détaillé par label
predictions = trainer.predict(test_dataset)
preds = np.argmax(predictions.predictions, axis=1)
true_labels = predictions.label_ids

print("\n📋 Rapport détaillé :")
print(classification_report(true_labels, preds, target_names=labels))

# ── CELLULE 10 : Sauvegarder le modèle ──────────────────────
model.save_pretrained("./mon_modele_bourse")
tokenizer.save_pretrained("./mon_modele_bourse")
print("✅ Modèle sauvegardé dans ./mon_modele_bourse/")

# ── CELLULE 11 : Télécharger le modèle ──────────────────────
import shutil
from google.colab import files

shutil.make_archive("mon_modele_bourse", "zip", "./mon_modele_bourse")
files.download("mon_modele_bourse.zip")
print("✅ Téléchargement lancé !")

# ── CELLULE 12 : Tester le modèle ───────────────────────────
from transformers import pipeline

classifier = pipeline(
    "text-classification",
    model="./mon_modele_bourse",
    tokenizer="./mon_modele_bourse",
    return_all_scores=True,
)

# Tests rapides
phrases_test = [
    "Je suis totalement déterminé à réussir cette opportunité",
    "J'ai peur de ne pas être à la hauteur",
    "Euh je sais pas trop ce que je veux vraiment faire",
    "Je suis passionné par ce domaine depuis toujours",
    "Je suis confiant dans mes capacités académiques",
]

print("\n🧪 Tests du modèle :")
print("=" * 60)
for phrase in phrases_test:
    scores = classifier(phrase)[0]
    meilleur = max(scores, key=lambda x: x["score"])
    print(f"\n📝 '{phrase[:50]}...'")
    print(f"   → {meilleur['label']} ({meilleur['score']:.1%})")
