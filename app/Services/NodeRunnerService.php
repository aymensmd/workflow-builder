<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NodeRunnerService
{
    public function run(array $node, array $inputData = []): array
    {
        $type = data_get($node, 'type');

        return match ($type) {
            'http' => $this->runHttpNode($node, $inputData),
            'slack' => ['status' => 'success', 'data' => ['message' => 'Slack node placeholder executed.']],
            'ai' => ['status' => 'success', 'data' => ['message' => 'AI node placeholder executed.']],
            'trigger' => ['status' => 'success', 'data' => $inputData],
            default => ['status' => 'failed', 'error' => "Unsupported node type: {$type}"],
        };
    }

    private function runHttpNode(array $node, array $inputData): array
    {
        $config = data_get($node, 'data.config', []);

        $method = strtoupper($config['method'] ?? 'GET');
        $url = $config['url'] ?? null;

        if (!$url) {
            return ['status' => 'failed', 'error' => 'Missing URL in HTTP node config.'];
        }

        $payload = $config['body'] ?? $inputData;

        $response = Http::timeout(20)->send($method, $url, ['json' => $payload]);

        return [
            'status' => $response->successful() ? 'success' : 'failed',
            'data' => $response->json(),
            'http_status' => $response->status(),
        ];
    }
}
