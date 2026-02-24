<?php

namespace App\Controller;

use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiQuizController extends AbstractController
{
    #[Route('/formateur/ai-quiz/generate', name: 'formateur_ai_quiz_generate', methods: ['POST'])]
    public function generate(
        Request $request,
        CoursRepository $coursRepo,
        HttpClientInterface $http
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $courseId = (int) $request->request->get('course_id');
        $level    = (string) $request->request->get('level', 'intermediaire');
        $type     = (string) $request->request->get('type', 'qcm');
        $count    = (int) $request->request->get('count', 10);
        $topic    = trim((string) $request->request->get('topic', ''));

        $course = $courseId ? $coursRepo->find($courseId) : null;

        $sourceText = '';
        if ($course) {
            $sourceText = trim(
                ($course->getTitreCours() ?? '') . "\n" .
                ($course->getMatiereCours() ?? '') . "\n" .
                ($course->getNivCours() ?? '') . "\n" .
                ($course->getLangueCours() ?? '') . "\n\n" .
                ($course->getDescription() ?? '')
            );
        }

        if ($sourceText === '' && $topic !== '') {
            $sourceText = $topic;
        }

        if ($sourceText === '') {
            $this->addFlash('danger', 'Choisis un cours (avec description) ou écris un sujet.');
            return $this->redirectToRoute('app_profile');
        }

        $rawJson = $this->callGroq($http, $sourceText, $level, $type, $count);

        if (!$rawJson) {
            $this->addFlash('danger', "Erreur IA: réponse vide.");
            return $this->redirectToRoute('app_profile');
        }

        // Validation JSON
        $quizData = null;
        try {
            $quizData = $this->validateQuizJson($rawJson);
            $this->addFlash('success', "Quiz IA généré (preview).");
        } catch (\Throwable $e) {
            $this->addFlash('danger', "JSON IA invalide: " . $e->getMessage());
        }

        // Stocker en session pour affichage sur /profile
        $session = $request->getSession();
        $session->set('ai_quiz_raw', $rawJson);
        $session->set('ai_quiz_data', $quizData);

        return $this->redirectToRoute('app_profile');
    }

    private function callGroq(
        HttpClientInterface $http,
        string $sourceText,
        string $level,
        string $type,
        int $count
    ): ?string {
        $apiKey = $_ENV['GROQ_API_KEY'] ?? null;
        $model  = $_ENV['GROQ_MODEL'] ?? 'llama-3.1-8b-instant';
        if (!$apiKey) return null;

        $count = max(3, min(30, $count));

        $prompt = <<<PROMPT
Réponds UNIQUEMENT par un JSON valide (sans markdown, sans texte autour) :

{
  "title": "string",
  "durationMinutes": 10,
  "questions": [
    {
      "statement": "string",
      "choices": ["A","B","C","D"],
      "correctIndex": 0,
      "explanation": "string"
    }
  ]
}

Contraintes:
- questions: exactement $count
- choices: exactement 4
- correctIndex: entre 0 et 3
- niveau: $level
- type: $type (qcm/vrai_faux/mix)
- basé sur le TEXTE SOURCE uniquement.

TEXTE SOURCE:
{$sourceText}
PROMPT;

        try {
            $res = $http->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Tu produis du JSON strict.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                    'max_tokens'  => 2500,
                ],
            ]);

            $data = $res->toArray(false);
            return trim($data['choices'][0]['message']['content'] ?? '') ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function validateQuizJson(string $rawJson): array
    {
        $decoded = json_decode($rawJson, true);
        if (!is_array($decoded)) throw new \RuntimeException("JSON non parsable.");

        if (empty($decoded['title']) || !is_string($decoded['title'])) {
            throw new \RuntimeException("title manquant.");
        }

        $decoded['durationMinutes'] = (int)($decoded['durationMinutes'] ?? 10);

        if (empty($decoded['questions']) || !is_array($decoded['questions'])) {
            throw new \RuntimeException("questions manquant.");
        }

        foreach ($decoded['questions'] as $i => $q) {
            if (empty($q['statement'])) throw new \RuntimeException("Q$i: statement manquant.");
            if (!isset($q['choices']) || !is_array($q['choices']) || count($q['choices']) !== 4) {
                throw new \RuntimeException("Q$i: choices doit être 4.");
            }
            $ci = (int)($q['correctIndex'] ?? -1);
            if ($ci < 0 || $ci > 3) throw new \RuntimeException("Q$i: correctIndex invalide.");
            if (!isset($q['explanation'])) $decoded['questions'][$i]['explanation'] = '';
        }

        return $decoded;
    }
}
