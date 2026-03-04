<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;

class DuplicateQuestionController extends AbstractController
{
    #[Route('/formateur/questions/check-duplicate', name: 'formateur_check_duplicate', methods: ['POST'])]
    public function checkDuplicate(Request $request, QuizRepository $quizRepo): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $quizId  = (int) $request->request->get('quiz_id');
        $newText = trim((string) $request->request->get('question_text'));

        if (!$quizId || $newText === '') {
            return $this->json(['ok' => false, 'message' => 'quiz_id ou question_text manquant.'], 400);
        }

        $quiz = $quizRepo->find($quizId);
        if (!$quiz) {
            return $this->json(['ok' => false, 'message' => 'Quiz introuvable.'], 404);
        }

        // ✅ chemins (Windows)
        $projectDir = $this->getParameter('kernel.project_dir');
        $aiDir = $projectDir . DIRECTORY_SEPARATOR . 'ai';

        $python = $aiDir . DIRECTORY_SEPARATOR . 'venv' . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe';
        $script = $aiDir . DIRECTORY_SEPARATOR . 'predict_batch_cli.py';
        $model  = $aiDir . DIRECTORY_SEPARATOR . 'duplicate_model_multilingual.joblib';

        if (!file_exists($python)) {
            return $this->json(['ok' => false, 'message' => 'Python venv introuvable: ' . $python], 500);
        }
        if (!file_exists($script)) {
            return $this->json(['ok' => false, 'message' => 'Script batch introuvable: ' . $script], 500);
        }
        if (!file_exists($model)) {
            return $this->json(['ok' => false, 'message' => 'Modèle joblib introuvable: ' . $model], 500);
        }

        // ✅ candidats (limite à 50)
        $existingQuestions = $quiz->getQuestions()->toArray();
        $existingQuestions = array_slice($existingQuestions, -50);

        $candidates = [];
        foreach ($existingQuestions as $q) {
            $txt = trim((string) $q->getQuestion());
            if ($txt !== '') $candidates[] = $txt;
        }

        // ✅ payload vers python (stdin)
        $payload = json_encode([
            'new_text' => $newText,
            'candidates' => $candidates
        ], JSON_UNESCAPED_UNICODE);

        $process = new Process([$python, $script], $aiDir);
        $process->setInput($payload);
        $process->setTimeout(120); // mets 120 au début, puis redescends à 30-60 quand stable
        $process->run();

        if (!$process->isSuccessful()) {
            return $this->json([
                'ok' => false,
                'message' => 'Erreur Python: ' . trim($process->getErrorOutput() ?: $process->getOutput())
            ], 500);
        }

        $out = trim($process->getOutput());
        $data = json_decode($out, true);

        if (!is_array($data) || !($data['ok'] ?? false)) {
            return $this->json([
                'ok' => false,
                'message' => 'JSON invalide depuis Python',
                'raw' => $out
            ], 500);
        }

        $bestProb = (float)($data['bestProbability'] ?? 0);
        $bestText = (string)($data['bestQuestionText'] ?? '');

        $warningLevel =
            $bestProb >= 0.85 ? 'high' :
            ($bestProb >= 0.75 ? 'medium' : 'low');

        return $this->json([
            'ok' => true,
            'bestProbability' => $bestProb,
            'bestQuestionText' => $bestText,
            'warningLevel' => $warningLevel,
        ]);
    }
   
}