<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiNodeController extends Controller
{
    public function generateNode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:2000',
        ]);

        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-1.5-pro');

        $instruction = "Return only JSON with keys: type, label, config. User prompt: {$validated['prompt']}";

        $response = Http::post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
            ['contents' => [['parts' => [['text' => $instruction]]]]]
        );

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '{}');
        $decoded = json_decode($text, true);

        if (!is_array($decoded)) {
            return response()->json(['message' => 'Unable to parse AI response.'], 422);
        }

        return response()->json([
            'node' => [
                'type' => $decoded['type'] ?? 'custom',
                'data' => [
                    'label' => $decoded['label'] ?? 'AI Node',
                    'config' => $decoded['config'] ?? new \stdClass(),
                ],
            ],
        ]);
    }
}
