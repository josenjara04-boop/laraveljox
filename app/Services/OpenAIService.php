<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAIService
{
    private function chat(string $system, string $user): string
    {
        $key = config('services.openai.key');
        if (!$key) {
            throw new RuntimeException('Clé OpenAI non configurée');
        }

        try {
            $response = Http::withToken($key)
                ->timeout(30)
                ->post(rtrim(config('services.openai.base_url'), '/') . '/chat/completions', [
                    'model' => config('services.openai.model'),
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Service OpenAI indisponible');
        }

        if ($response->failed()) {
            throw new RuntimeException('Service OpenAI indisponible (HTTP ' . $response->status() . ')');
        }

        return trim($response->json('choices.0.message.content', ''));
    }

    public function generateSummary(string $title, string $author, int $year): string
    {
        return $this->chat(
            'Tu es un expert en littérature. Tu génères des résumés concis et accrocheurs pour des fiches de bibliothèque. Réponds toujours en français. Maximum 3 phrases.',
            "Génère un résumé pour le livre intitulé \"{$title}\" écrit par {$author}, publié en {$year}."
        );
    }

    public function extractKeywords(string $query): array
    {
        $text = $this->chat(
            'Extrais 2 à 5 mots-clés pertinents de la description. Réponds UNIQUEMENT avec un tableau JSON de chaînes, sans texte autour.',
            $query
        );

        $text = trim(preg_replace('/^```(?:json)?|```$/m', '', $text));
        $keywords = json_decode($text, true);

        return is_array($keywords) ? array_values(array_filter($keywords, 'is_string')) : [];
    }

    public function model(): string
    {
        return config('services.openai.model');
    }
}
