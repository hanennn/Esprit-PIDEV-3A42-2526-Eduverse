<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class InterviewAnalyseService
{
    private string $microserviceUrl;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        string $interviewMicroserviceUrl = 'http://localhost:8001'
    ) {
        $this->microserviceUrl = rtrim($interviewMicroserviceUrl, '/');
    }

    /**
     * @return array<string, mixed>
     */
    public function analyser(string $cheminAudio): array
    {
        $url = $this->microserviceUrl . '/analyser-interview';

        $this->logger->info('Sending audio to AI microservice', [
            'url' => $url,
            'file' => basename($cheminAudio),
        ]);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'body' => [
                    'audio' => fopen($cheminAudio, 'r'),
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 180,
            ]);

            $data = $response->toArray();

            $this->logger->info('AI analysis completed', [
                'has_transcription' => !empty($data['transcription']),
                'emotions' => array_keys($data['scores_emotions'] ?? []),
            ]);

            return $data;

        } catch (\Exception $e) {
            $this->logger->error('AI microservice call failed', [
                'error' => $e->getMessage(),
                'url' => $url,
            ]);
            throw $e;
        }
    }

    public function isAvailable(): bool
    {
        try {
            $response = $this->httpClient->request('GET', $this->microserviceUrl . '/', [
                'timeout' => 5,
            ]);
            return $response->getStatusCode() === 200;
        } catch (\Exception) {
            return false;
        }
    }
}
