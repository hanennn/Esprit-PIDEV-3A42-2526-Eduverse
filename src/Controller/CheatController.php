<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Process;

class CheatController extends AbstractController
{
    #[Route('/quiz/cheat/check', name: 'app_quiz_cheat_check', methods: ['POST'])]
    public function check(Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $sequence = $data['sequence'] ?? [];

        if (count($sequence) < 90) {
            return new JsonResponse(['success' => false, 'error' => 'Sequence too short'], 400);
        }

        // 1. Prepare to run Python script
        $projectDir = $this->getParameter('kernel.project_dir');
        if (!is_string($projectDir)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid project directory'], 500);
        }
        $scriptPath = $projectDir . DIRECTORY_SEPARATOR . 'CheatDetector' . DIRECTORY_SEPARATOR . 'api_inference.py';

        // Pass JSON sequence to python via stdin
        $jsonInput = json_encode(['sequence' => $sequence]);
        if ($jsonInput === false) {
            return new JsonResponse(['success' => false, 'error' => 'Failed to encode sequence'], 400);
        }

        $process = new Process(['python', $scriptPath]);
        $process->setInput($jsonInput);
        $process->run();

        if (!$process->isSuccessful()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Python script failed',
                'details' => $process->getErrorOutput()
            ], 500);
        }

        // 2. Parse AI result
        $result = json_decode($process->getOutput(), true);

        if (!$result || !isset($result['success']) || !$result['success']) {
            return new JsonResponse([
                'success' => false,
                'error' => $result['error'] ?? 'Invalid AI output'
            ], 500);
        }

        // 3. Manage striking system in session
        $label = $result['label']; // 'straight' or 'cheat'
        $cheatCount = $session->get('quiz_cheat_count', 0);

        if ($label === 'cheat') {
            $cheatCount++;
            $session->set('quiz_cheat_count', $cheatCount);
        }

        $terminate = ($cheatCount >= 2);

        return new JsonResponse([
            'success'   => true,
            'label'     => $label,
            'count'     => $cheatCount,
            'terminate' => $terminate,
            'prob'      => $result['probability'] ?? 0
        ]);
    }

    #[Route('/quiz/cheat/reset', name: 'app_quiz_cheat_reset', methods: ['POST'])]
    public function reset(SessionInterface $session): JsonResponse
    {
        $session->remove('quiz_cheat_count');
        return new JsonResponse(['success' => true]);
    }
}