<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AssistantController extends AbstractController
{
    #[Route('/assistant', name: 'app_assistant', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('assistant/index.html.twig');
    }

    #[Route('/assistant/ask', name: 'app_assistant_ask', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function ask(Request $request, HttpClientInterface $client): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $message = trim((string) ($payload['message'] ?? ''));

        if ($message === '') {
            return new JsonResponse(['error' => 'Message is required.'], 400);
        }

        $apiKey = $_ENV['GROQ_API_KEY'] ?? '';
        $model = $_ENV['GROQ_MODEL'] ?? 'llama-3.1-8b-instant';

        if ($apiKey === '') {
            return new JsonResponse(['error' => 'Assistant API key is not configured.'], 500);
        }

        try {
            $response = $client->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'temperature' => 0.4,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an education assistant for Eduverse. Answer questions clearly and concisely, and keep answers focused on learning and education.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return new JsonResponse(['error' => 'API returned status ' . $statusCode], $statusCode);
            }

            $data = $response->toArray(false);
            if (isset($data['error'])) {
                return new JsonResponse(['error' => $data['error']['message'] ?? 'API error'], 400);
            }

            $reply = $data['choices'][0]['message']['content'] ?? null;

            if (!$reply) {
                return new JsonResponse(['error' => 'No response from assistant.'], 502);
            }

            return new JsonResponse(['reply' => $reply]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Error: ' . $e->getMessage()], 502);
        }
    }
}
