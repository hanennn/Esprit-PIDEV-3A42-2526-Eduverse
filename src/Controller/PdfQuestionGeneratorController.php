<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class PdfQuestionGeneratorController extends AbstractController
{
    #[Route('/formateur/pdf/generate-questions', name: 'formateur_generate_questions_from_pdf', methods: ['POST'])]
    public function generateFromPdf(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $file = $request->files->get('pdf_file');
        if (!$file) {
            return $this->json(['ok' => false, 'message' => 'Aucun PDF envoyé.'], 400);
        }

        $projectDir = $this->getParameter('kernel.project_dir');
        $aiDir = $projectDir . DIRECTORY_SEPARATOR . 'ai';

        $uploadDir = $aiDir . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $pdfName = uniqid('cours_', true) . '.pdf';
        $file->move($uploadDir, $pdfName);
        $pdfPath = $uploadDir . DIRECTORY_SEPARATOR . $pdfName;

        $python = $aiDir . DIRECTORY_SEPARATOR . 'venv' . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe';
        $script = $aiDir . DIRECTORY_SEPARATOR . 'pdf_to_questions_cli.py';

        if (!file_exists($python)) {
            return $this->json(['ok' => false, 'message' => 'python.exe introuvable: ' . $python], 500);
        }
        if (!file_exists($script)) {
            return $this->json(['ok' => false, 'message' => 'Script introuvable: ' . $script], 500);
        }
        if (!file_exists($aiDir . DIRECTORY_SEPARATOR . 'level_clf.joblib')) {
            return $this->json(['ok' => false, 'message' => 'Model introuvable: ai/level_clf.joblib'], 500);
        }

        $process = new Process([$python, $script, $pdfPath], $aiDir);
        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            return $this->json([
                'ok' => false,
                'message' => 'Erreur Python: ' . ($process->getErrorOutput() ?: $process->getOutput())
            ], 500);
        }

        $out = trim($process->getOutput());
        $out = mb_convert_encoding($out, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        $data = json_decode($out, true);

        if (!is_array($data)) {
            return $this->json(['ok' => false, 'message' => 'JSON invalide renvoyé par Python', 'raw' => $out], 500);
        }

        return $this->json($data);
    }
}