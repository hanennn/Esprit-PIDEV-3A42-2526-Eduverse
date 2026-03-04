<?php

// ─────────────────────────────────────────────
//  CONFIG — lit depuis $_ENV (Symfony) ou fallback
// ─────────────────────────────────────────────
if (!defined('DB_HOST'))       define('DB_HOST',       $_ENV['AI_EVENT_DB_HOST'] ?? 'localhost');
if (!defined('DB_NAME'))       define('DB_NAME',        $_ENV['AI_EVENT_DB_NAME'] ?? 'eduverse4');
if (!defined('DB_USER'))       define('DB_USER',        $_ENV['AI_EVENT_DB_USER'] ?? 'root');
if (!defined('DB_PASS'))       define('DB_PASS',        $_ENV['AI_EVENT_DB_PASS'] ?? '');
if (!defined('DB_CHARSET'))    define('DB_CHARSET',     'utf8mb4');
if (!defined('GROQ_API_KEY'))  define('GROQ_API_KEY',   $_ENV['GROQ_API_KEY']     ?? '');
if (!defined('GROQ_MODEL'))    define('GROQ_MODEL',     $_ENV['GROQ_MODEL']       ?? 'llama-3.1-8b-instant');
if (!defined('GROQ_API_URL'))  define('GROQ_API_URL',   'https://api.groq.com/openai/v1/chat/completions');

// ─────────────────────────────────────────────
//  CONNEXION BASE DE DONNÉES
// ─────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

// ─────────────────────────────────────────────
//  ANALYSE DES ÉVÉNEMENTS EXISTANTS
// ─────────────────────────────────────────────

/**
 * Récupère les statistiques d'inscriptions par type d'événement
 */
function getEventStats(): array {
    $db = getDB();

    // Statistiques par type : nombre d'événements, total inscriptions, moyenne
    $sql = "
        SELECT 
            e.type,
            COUNT(DISTINCT e.id)              AS nb_events,
            COUNT(i.id)                       AS total_inscriptions,
            ROUND(COUNT(i.id) / COUNT(DISTINCT e.id), 1) AS avg_inscriptions,
            SUM(CASE WHEN i.statut = 'confirmé' OR i.statut = 'Confirmé' THEN 1 ELSE 0 END) AS confirmed,
            GROUP_CONCAT(DISTINCT e.niveau)   AS niveaux_utilises,
            MIN(e.heure_deb)                  AS heure_min,
            MAX(e.heure_deb)                  AS heure_max
        FROM event e
        LEFT JOIN event_inscription i ON i.event_id = e.id
        GROUP BY e.type
        ORDER BY avg_inscriptions DESC
    ";
    return $db->query($sql)->fetchAll();
}

/**
 * Récupère les 10 événements les plus populaires (avec détails)
 */
function getTopEvents(int $limit = 10): array {
    $db = getDB();
    $sql = "
        SELECT 
            e.id,
            e.titre,
            e.type,
            e.niveau,
            e.date,
            e.heure_deb,
            e.heure_fin,
            COUNT(i.id) AS nb_inscriptions,
            SUM(CASE WHEN i.statut IN ('confirmé','Confirmé') THEN 1 ELSE 0 END) AS nb_confirmes
        FROM event e
        LEFT JOIN event_inscription i ON i.event_id = e.id
        GROUP BY e.id
        ORDER BY nb_inscriptions DESC
        LIMIT :limit
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Récupère tous les titres et descriptions pour inspiration
 */
function getAllEventsSummary(): array {
    $db = getDB();
    $sql = "
        SELECT 
            e.titre,
            e.description,
            e.type,
            e.niveau,
            COUNT(i.id) AS nb_inscriptions
        FROM event e
        LEFT JOIN event_inscription i ON i.event_id = e.id
        GROUP BY e.id
        ORDER BY nb_inscriptions DESC
        LIMIT 50
    ";
    return $db->query($sql)->fetchAll();
}

/**
 * Récupère les types d'événements distincts existants
 */
function getEventTypes(): array {
    $db = getDB();
    return $db->query("SELECT DISTINCT type FROM event WHERE type IS NOT NULL AND type != '' ORDER BY type")->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Récupère les niveaux distincts existants
 */
function getEventLevels(): array {
    $db = getDB();
    return $db->query("SELECT DISTINCT niveau FROM event WHERE niveau IS NOT NULL AND niveau != '' ORDER BY niveau")->fetchAll(PDO::FETCH_COLUMN);
}

// ─────────────────────────────────────────────
//  APPEL À L'API CLAUDE (IA)
// ─────────────────────────────────────────────

/**
 * Envoie le prompt à Groq (LLaMA) et retourne la réponse JSON
 * @param string $prompt
 * @return array  ['success' => bool, 'data' => array|null, 'error' => string|null]
 */
function callClaudeAPI(string $prompt): array {
    $payload = [
        'model'    => GROQ_MODEL,
        'messages' => [
            [
                'role'    => 'system',
                'content' => 'Tu es un expert en gestion d\'\u00e9vénements. Tu réponds UNIQUEMENT en JSON valide, sans texte avant ou après.'
            ],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7,
        'max_tokens'  => 1024,
    ];

    $ch = curl_init(GROQ_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY,
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 60,
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'data' => null, 'error' => 'Erreur cURL : ' . $curlError];
    }

    $decoded = json_decode($response, true);

    if ($httpCode !== 200 || empty($decoded['choices'][0]['message']['content'])) {
        $errMsg = $decoded['error']['message'] ?? ('HTTP ' . $httpCode);
        return ['success' => false, 'data' => null, 'error' => 'Erreur API : ' . $errMsg];
    }

    $text = $decoded['choices'][0]['message']['content'];

    // Extraire le JSON de la réponse
    if (preg_match('/```json\s*([\s\S]*?)\s*```/', $text, $matches)) {
        $jsonStr = $matches[1];
    } elseif (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
        $jsonStr = $matches[0];
    } else {
        $jsonStr = $text;
    }

    $eventData = json_decode($jsonStr, true);
    if (!$eventData) {
        return ['success' => false, 'data' => null, 'error' => 'Impossible de parser le JSON retourné par l\'IA.'];
    }

    return ['success' => true, 'data' => $eventData, 'error' => null];
}

// ─────────────────────────────────────────────
//  CONSTRUCTION DU PROMPT
// ─────────────────────────────────────────────

/**
 * Construit le prompt envoyé à Claude
 */
function buildPrompt(array $stats, array $topEvents, array $summary): string {

    $statsJson    = json_encode($stats,     JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $topJson      = json_encode($topEvents, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $summaryJson  = json_encode(array_slice($summary, 0, 15), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    return <<<PROMPT
Tu es un expert en gestion d'événements. Analyse les données suivantes issues de notre plateforme 
et génère le meilleur événement possible pour maximiser les inscriptions.

## STATISTIQUES PAR TYPE D'ÉVÉNEMENT
(classé par moyenne d'inscriptions décroissante)
{$statsJson}

## TOP 10 ÉVÉNEMENTS LES PLUS POPULAIRES
{$topJson}

## EXEMPLES D'ÉVÉNEMENTS (titre + description)
{$summaryJson}

## MISSION
En te basant sur ces données :
1. Identifie le type d'événement avec le meilleur taux d'inscription moyen
2. Identifie les patterns des événements populaires (mots clés dans les titres, niveaux, horaires)
3. Génère un événement optimisé

## FORMAT DE RÉPONSE OBLIGATOIRE
Réponds UNIQUEMENT avec un objet JSON valide, sans texte avant ou après :
{
  "titre": "Titre accrocheur et optimisé de l'événement",
  "description": "Description détaillée et engageante de l'événement (3-5 phrases)",
  "type": "type_d_evenement (doit être l'un des types existants)",
  "niveau": "niveau approprié (doit être l'un des niveaux existants)",
  "heure_deb": "HH:MM (heure de début optimale basée sur les données)",
  "heure_fin": "HH:MM (heure de fin cohérente)",
  "raison": "Explication courte (2-3 phrases) pourquoi cet événement a le plus de chances de succès",
  "type_le_plus_populaire": "nom du type avec la meilleure moyenne d'inscriptions",
  "score_confiance": "pourcentage estimé de succès basé sur les données (ex: 87%)"
}
PROMPT;
}

// ─────────────────────────────────────────────
//  SAUVEGARDE EN BASE DE DONNÉES (optionnel)
// ─────────────────────────────────────────────

/**
 * Insère l'événement généré par l'IA dans la table events
 * @param array $eventData  données retournées par l'IA
 * @param string $date      date de l'événement choisie par l'admin
 * @return int|false        id inséré ou false si échec
 */
function saveGeneratedEvent(array $eventData, string $date = '') {
    $db = getDB();

    if (empty($date)) {
        // Par défaut : 30 jours dans le futur
        $date = date('Y-m-d', strtotime('+30 days'));
    }

    $sql = "
        INSERT INTO event (titre, description, type, niveau, date, heure_deb, heure_fin)
        VALUES (:titre, :description, :type, :niveau, :date, :heure_deb, :heure_fin)
    ";
    $stmt = $db->prepare($sql);
    $ok = $stmt->execute([
        ':titre'       => $eventData['titre']       ?? 'Événement IA',
        ':description' => $eventData['description'] ?? '',
        ':type'        => $eventData['type']        ?? '',
        ':niveau'      => $eventData['niveau']      ?? '',
        ':date'        => $date,
        ':heure_deb'   => $eventData['heure_deb']   ?? '10:00',
        ':heure_fin'   => $eventData['heure_fin']   ?? '12:00',
    ]);

    return $ok ? (int)$db->lastInsertId() : false;
}

// ─────────────────────────────────────────────
//  POINT D'ENTRÉE PRINCIPAL
// ─────────────────────────────────────────────

/**
 * Fonction principale : analyse + appel IA + retour JSON
 * Appelée via AJAX depuis votre interface
 */
function generateAIEvent(): void {
    header('Content-Type: application/json; charset=utf-8');

    try {
        // 1. Récupération des données
        $stats     = getEventStats();
        $topEvents = getTopEvents(10);
        $summary   = getAllEventsSummary();

        if (empty($stats)) {
            echo json_encode(['success' => false, 'error' => 'Aucun événement en base de données pour analyser.']);
            return;
        }

        // 2. Construction du prompt
        $prompt = buildPrompt($stats, $topEvents, $summary);

        // 3. Appel à l'IA
        $result = callClaudeAPI($prompt);

        if (!$result['success']) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            return;
        }

        $eventData = $result['data'];

        // 4. Sauvegarde optionnelle si paramètre save=1
        $savedId = null;
        if (!empty($_POST['save']) && $_POST['save'] == '1') {
            $date    = $_POST['date'] ?? '';
            $savedId = saveGeneratedEvent($eventData, $date);
        }

        // 5. Réponse
        echo json_encode([
            'success'   => true,
            'event'     => $eventData,
            'saved_id'  => $savedId,
            'stats'     => $stats,   // pour affichage optionnel côté front
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// ─────────────────────────────────────────────
//  ROUTING
//  GET  → affiche l'interface HTML
//  POST action=generate → lance l'IA (AJAX)
//  POST action=save     → sauvegarde l'événement
// ─────────────────────────────────────────────

// ─────────────────────────────────────────────
//  ROUTING (uniquement en mode standalone, pas quand inclus par Symfony)
// ─────────────────────────────────────────────

if (!defined('AI_EVENT_INCLUDED')) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if ($action === 'generate') {
        generateAIEvent();
        exit;
    }

    if ($action === 'save') {
        header('Content-Type: application/json; charset=utf-8');
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['event'])) {
            echo json_encode(['success' => false, 'error' => 'Données manquantes']);
            exit;
        }
        $id = saveGeneratedEvent($data['event'], $data['date'] ?? '');
        echo json_encode(['success' => (bool)$id, 'id' => $id]);
        exit;
    }

    // ─────────────────────────────────────────────
    //  INTERFACE HTML (page principale — mode standalone uniquement)
    // ─────────────────────────────────────────────

    try {
        $types  = getEventTypes();
        $levels = getEventLevels();
    } catch (Exception $e) {
        $types  = [];
        $levels = [];
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🤖 Générateur d'événement par IA</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4f8;
            color: #2d3748;
            min-height: 100vh;
        }

        /* ── HEADER ── */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 28px 40px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 20px rgba(102,126,234,0.4);
        }
        .header h1 { font-size: 1.6rem; font-weight: 700; }
        .header p  { font-size: 0.9rem; opacity: 0.85; margin-top: 4px; }
        .badge {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* ── LAYOUT ── */
        .container { max-width: 1100px; margin: 0 auto; padding: 32px 20px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }

        /* ── CARDS ── */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header h2 { font-size: 1.05rem; font-weight: 700; color: #2d3748; }
        .card-body { padding: 24px; }

        /* ── BOUTON PRINCIPAL ── */
        .btn-generate {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-generate:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.45);
        }
        .btn-generate:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .btn-save {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
            margin-top: 12px;
            display: none;
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(72,187,120,0.4); }

        /* ── SPINNER ── */
        .spinner {
            display: none;
            text-align: center;
            padding: 40px;
        }
        .spinner .ring {
            width: 50px; height: 50px;
            border: 4px solid #e2e8f0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner p { color: #718096; font-size: 0.95rem; }

        /* ── RÉSULTAT ── */
        .result-box { display: none; }

        .confidence-bar {
            background: #e2e8f0;
            border-radius: 8px;
            height: 10px;
            overflow: hidden;
            margin: 6px 0 2px;
        }
        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, #48bb78, #38a169);
            border-radius: 8px;
            transition: width 1.2s ease;
        }

        .field-group { margin-bottom: 18px; }
        .field-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 5px;
        }
        .field-value {
            font-size: 0.95rem;
            color: #2d3748;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            line-height: 1.5;
        }
        .field-value.editable {
            background: white;
            border-color: #cbd5e0;
        }
        .field-value.editable:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
        }

        /* date picker pour la sauvegarde */
        .date-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 16px;
        }
        .date-row input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #2d3748;
        }

        /* ── ALERT ── */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 16px;
            display: none;
        }
        .alert-error   { background: #fff5f5; border: 1px solid #fed7d7; color: #c53030; }
        .alert-success { background: #f0fff4; border: 1px solid #c6f6d5; color: #276749; }

        /* ── RAISON / EXPLICATION ── */
        .reason-box {
            background: linear-gradient(135deg, #ebf4ff, #e9d8fd);
            border: 1px solid #bee3f8;
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 0.9rem;
            color: #2c5282;
            line-height: 1.6;
            margin-top: 4px;
        }

        /* ── STATS MINI ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 8px;
        }
        .stat-card {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
        }
        .stat-card .num { font-size: 1.5rem; font-weight: 800; color: #667eea; }
        .stat-card .lbl { font-size: 0.72rem; color: #718096; margin-top: 2px; }

        /* ── SUCCESS MESSAGE ── */
        .success-banner {
            display: none;
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            border-radius: 12px;
            padding: 16px 20px;
            text-align: center;
            font-weight: 700;
            margin-top: 12px;
        }
    </style>
</head>
<body>

<div class="header">
    <div>
        <div class="badge">IA Powered</div>
    </div>
    <div>
        <h1>🤖 Générateur d'événement par IA</h1>
        <p>Analyse vos données historiques et suggère l'événement avec le plus grand potentiel d'inscriptions</p>
    </div>
</div>

<div class="container">

    <div id="alert-box" class="alert"></div>

    <div class="grid">

        <!-- ── COLONNE GAUCHE : CONTRÔLES ── -->
        <div>
            <div class="card">
                <div class="card-header">
                    <span style="font-size:1.4rem">⚙️</span>
                    <h2>Lancer la génération</h2>
                </div>
                <div class="card-body">
                    <p style="color:#718096;font-size:0.9rem;margin-bottom:20px;line-height:1.6">
                        L'IA va analyser tous vos événements passés, calculer les types qui
                        génèrent le plus d'inscriptions, puis créer un événement optimisé.
                    </p>

                    <button class="btn-generate" id="btn-generate" onclick="generateEvent()">
                        <span>🚀</span>
                        <span>Générer un événement optimisé</span>
                    </button>

                    <!-- Spinner pendant le chargement -->
                    <div class="spinner" id="spinner">
                        <div class="ring"></div>
                        <p>L'IA analyse vos données…</p>
                    </div>

                    <!-- Bouton de sauvegarde -->
                    <div id="save-section" style="display:none">
                        <div class="date-row">
                            <div>
                                <label style="font-size:0.8rem;color:#718096;font-weight:600">Date de l'événement</label>
                                <input type="date" id="event-date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                            </div>
                        </div>
                        <button class="btn-save" id="btn-save" onclick="saveEvent()">
                            💾 Enregistrer cet événement
                        </button>
                        <div class="success-banner" id="success-banner"></div>
                    </div>
                </div>
            </div>

            <!-- Stats mini (remplies après génération) -->
            <div class="card" style="margin-top:20px;display:none" id="stats-card">
                <div class="card-header">
                    <span style="font-size:1.4rem">📊</span>
                    <h2>Données analysées</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid" id="stats-grid"></div>
                </div>
            </div>
        </div>

        <!-- ── COLONNE DROITE : RÉSULTAT ── -->
        <div>
            <div class="card">
                <div class="card-header">
                    <span style="font-size:1.4rem">✨</span>
                    <h2>Événement suggéré par l'IA</h2>
                </div>
                <div class="card-body">

                    <!-- Placeholder avant génération -->
                    <div id="placeholder" style="text-align:center;padding:40px 20px;color:#a0aec0">
                        <div style="font-size:3rem;margin-bottom:12px">🎯</div>
                        <p style="font-size:0.95rem">
                            Cliquez sur "Générer" pour obtenir une suggestion d'événement optimisée
                        </p>
                    </div>

                    <!-- Résultat -->
                    <div class="result-box" id="result-box">

                        <!-- Score de confiance -->
                        <div class="field-group">
                            <label>🎯 Score de succès estimé</label>
                            <div class="confidence-bar">
                                <div class="confidence-fill" id="confidence-fill" style="width:0%"></div>
                            </div>
                            <div style="font-size:0.85rem;color:#48bb78;font-weight:700" id="confidence-text"></div>
                        </div>

                        <!-- Titre -->
                        <div class="field-group">
                            <label>📌 Titre</label>
                            <div class="field-value editable" id="res-titre" contenteditable="true"></div>
                        </div>

                        <!-- Description -->
                        <div class="field-group">
                            <label>📝 Description</label>
                            <div class="field-value editable" id="res-description" contenteditable="true"
                                 style="min-height:80px"></div>
                        </div>

                        <!-- Type + Niveau -->
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <div class="field-group">
                                <label>🏷️ Type</label>
                                <select class="field-value editable" id="res-type">
                                    <?php foreach ($types as $t): ?>
                                        <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field-group">
                                <label>📶 Niveau</label>
                                <select class="field-value editable" id="res-niveau">
                                    <?php foreach ($levels as $l): ?>
                                        <option value="<?= htmlspecialchars($l) ?>"><?= htmlspecialchars($l) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Horaires -->
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <div class="field-group">
                                <label>🕐 Heure début</label>
                                <input type="time" class="field-value editable" id="res-heure-deb">
                            </div>
                            <div class="field-group">
                                <label>🕕 Heure fin</label>
                                <input type="time" class="field-value editable" id="res-heure-fin">
                            </div>
                        </div>

                        <!-- Raison -->
                        <div class="field-group">
                            <label>💡 Pourquoi cet événement ?</label>
                            <div class="reason-box" id="res-raison"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentEventData = null;

// ─── Génération ───────────────────────────────
async function generateEvent() {
    const btn     = document.getElementById('btn-generate');
    const spinner = document.getElementById('spinner');
    const alert   = document.getElementById('alert-box');

    // UI : loading
    btn.disabled = true;
    spinner.style.display = 'block';
    alert.style.display   = 'none';

    try {
        const res  = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=generate'
        });
        const json = await res.json();

        if (!json.success) {
            showAlert(json.error || 'Erreur inconnue', 'error');
            return;
        }

        currentEventData = json.event;
        renderResult(json.event);
        renderStats(json.stats);

    } catch (e) {
        showAlert('Erreur réseau : ' + e.message, 'error');
    } finally {
        btn.disabled          = false;
        spinner.style.display = 'none';
    }
}

// ─── Affichage du résultat ────────────────────
function renderResult(ev) {
    document.getElementById('placeholder').style.display = 'none';
    document.getElementById('result-box').style.display  = 'block';
    document.getElementById('save-section').style.display = 'block';
    document.getElementById('btn-save').style.display   = 'block';
    document.getElementById('success-banner').style.display = 'none';

    // Score
    const score = parseInt(ev.score_confiance) || 0;
    document.getElementById('confidence-text').textContent = ev.score_confiance || '—';
    setTimeout(() => {
        document.getElementById('confidence-fill').style.width = score + '%';
    }, 100);

    // Champs
    document.getElementById('res-titre').textContent       = ev.titre       || '';
    document.getElementById('res-description').textContent = ev.description || '';
    document.getElementById('res-raison').textContent      = ev.raison      || '';
    document.getElementById('res-heure-deb').value         = ev.heure_deb  || '';
    document.getElementById('res-heure-fin').value         = ev.heure_fin  || '';

    // Selects
    setSelectValue('res-type',   ev.type);
    setSelectValue('res-niveau', ev.niveau);
}

function setSelectValue(id, value) {
    const sel = document.getElementById(id);
    for (let opt of sel.options) {
        if (opt.value === value || opt.text === value) {
            opt.selected = true;
            return;
        }
    }
    // Valeur non trouvée → on l'ajoute
    const opt = document.createElement('option');
    opt.value = value; opt.text = value; opt.selected = true;
    sel.appendChild(opt);
}

// ─── Stats mini ───────────────────────────────
function renderStats(stats) {
    if (!stats || !stats.length) return;
    const card = document.getElementById('stats-card');
    const grid = document.getElementById('stats-grid');
    card.style.display = 'block';

    const top3 = stats.slice(0, 3);
    grid.innerHTML = top3.map(s => `
        <div class="stat-card">
            <div class="num">${s.avg_inscriptions}</div>
            <div class="lbl">moy. inscriptions</div>
            <div style="font-size:0.75rem;font-weight:700;color:#4a5568;margin-top:4px">${s.type}</div>
        </div>
    `).join('');
}

// ─── Sauvegarde ───────────────────────────────
async function saveEvent() {
    if (!currentEventData) return;

    // Récupérer les valeurs éventuellement modifiées par l'utilisateur
    const payload = {
        event: {
            ...currentEventData,
            titre:       document.getElementById('res-titre').textContent,
            description: document.getElementById('res-description').textContent,
            type:        document.getElementById('res-type').value,
            niveau:      document.getElementById('res-niveau').value,
            heure_deb:   document.getElementById('res-heure-deb').value,
            heure_fin:   document.getElementById('res-heure-fin').value,
        },
        date: document.getElementById('event-date').value
    };

    const btn = document.getElementById('btn-save');
    btn.disabled = true;
    btn.textContent = 'Enregistrement…';

    try {
        const res  = await fetch('?action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const json = await res.json();

        if (json.success) {
            btn.style.display = 'none';
            const banner = document.getElementById('success-banner');
            banner.style.display = 'block';
            banner.textContent   = `✅ Événement enregistré avec succès ! (ID : ${json.id})`;
        } else {
            showAlert('Erreur lors de la sauvegarde : ' + (json.error || '?'), 'error');
            btn.disabled    = false;
            btn.textContent = '💾 Enregistrer cet événement';
        }
    } catch (e) {
        showAlert('Erreur réseau : ' + e.message, 'error');
        btn.disabled    = false;
        btn.textContent = '💾 Enregistrer cet événement';
    }
}

// ─── Alert helper ─────────────────────────────
function showAlert(msg, type) {
    const el = document.getElementById('alert-box');
    el.className       = 'alert alert-' + type;
    el.textContent     = msg;
    el.style.display   = 'block';
}
</script>

</body>
</html>
<?php
} // end if (!defined('AI_EVENT_INCLUDED'))
