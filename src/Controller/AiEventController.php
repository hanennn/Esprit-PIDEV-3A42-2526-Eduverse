<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Endpoint AJAX pour la génération d'événement par IA.
 * Proxy Symfony vers ai_event_generator.php (Claude API).
 *
 * Route : POST /eventAdmin/ai/generate
 * Réponse : JSON { success, event, stats } ou { success: false, error }
 */
#[Route('/eventAdmin/ai')]
class AiEventController extends AbstractController
{
    #[Route('/generate', name: 'app_event_ai_generate', methods: ['POST'])]
    public function generate(Request $request): JsonResponse
    {
        // Charger le fichier AI sans déclencher son routing HTML ni sa connexion PDO globale
        define('AI_EVENT_INCLUDED', true);

        $aiFile = $this->getParameter('kernel.project_dir') . '/ai_event/ai_event_generator.php';

        if (!file_exists($aiFile)) {
            return $this->json([
                'success' => false,
                'error'   => 'Fichier AI introuvable : ' . $aiFile,
            ], 500);
        }

        require_once $aiFile;

        // ── Appel à l'IA ──────────────────────────────────────────────
        try {
            $stats     = getEventStats();
            $topEvents = getTopEvents(10);
            $summary   = getAllEventsSummary();

            if (empty($stats)) {
                return $this->json([
                    'success' => false,
                    'error'   => 'Aucun événement en base de données pour analyser.',
                ]);
            }

            $prompt = buildPrompt($stats, $topEvents, $summary);
            $result = callClaudeAPI($prompt);

            if (!$result['success']) {
                return $this->json([
                    'success' => false,
                    'error'   => $result['error'],
                ]);
            }

            return $this->json([
                'success' => true,
                'event'   => $result['data'],
                'stats'   => $stats,
            ]);

        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
