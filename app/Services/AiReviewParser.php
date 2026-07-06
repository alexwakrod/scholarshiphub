<?php

namespace App\Services;

class AiReviewParser
{
    /**
     * Parse the raw AI response into the structured review array.
     * This is a static copy of the parseResponse + helper methods from AiReviewJob.
     */
    public static function parse(string $response): array
    {
        // 1. Strip markdown fences
        $response = preg_replace('/^```(?:json)?\s*\n?/m', '', $response);
        $response = preg_replace('/\n?```$/m', '', $response);
        $response = trim($response);

        // 2. Try to locate the first JSON object or array
        $jsonStart = strpos($response, '{');
        $arrayStart = strpos($response, '[');
        if ($jsonStart === false && $arrayStart === false) {
            throw new \Exception('No JSON structure found in AI response: ' . substr($response, 0, 200));
        }

        $start = ($jsonStart === false) ? $arrayStart : ($arrayStart === false ? $jsonStart : min($jsonStart, $arrayStart));
        $end   = strrpos($response, $jsonStart !== false ? '}' : ']');

        if ($end === false) {
            throw new \Exception('Unterminated JSON in AI response: ' . substr($response, 0, 200));
        }

        $jsonString = substr($response, $start, $end - $start + 1);
        $parsed = json_decode($jsonString, true);

        if (is_array($parsed)) {
            return self::sanitize($parsed);
        }

        // 3. Fallback: regex extraction
        \Illuminate\Support\Facades\Log::warning('AiReviewParser: could not decode JSON, attempting regex extraction.');
        $extracted = [
            'quality_score'         => self::extractFloat($response, 'quality_score'),
            'ats_score'             => self::extractFloat($response, 'ats_score'),
            'competitiveness_score' => self::extractFloat($response, 'competitiveness_score'),
            'strengths'             => self::extractArray($response, 'strengths'),
            'weaknesses'            => self::extractArray($response, 'weaknesses'),
            'suggestions'           => self::extractArray($response, 'suggestions'),
            'grammar_issues'        => self::extractGrammar($response),
        ];

        if ($extracted['quality_score'] !== null) {
            return self::sanitize($extracted);
        }

        throw new \Exception('Unable to extract valid JSON or key fields from AI response: ' . substr($response, 0, 300));
    }

    private static function extractFloat(string $response, string $key): ?float
    {
        if (preg_match('/"' . $key . '"\s*:\s*([0-9]+(?:\.[0-9]+)?)/i', $response, $m)) {
            return (float) $m[1];
        }
        return null;
    }

    private static function extractArray(string $response, string $key): array
    {
        if (preg_match('/"' . $key . '"\s*:\s*\[(.*?)\]/s', $response, $m)) {
            $content = $m[1];
            $items = [];
            if (preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"/', $content, $matches)) {
                $items = $matches[1];
                $items = array_map(fn($s) => str_replace(['\"', '\\\\'], ['"', '\\'], $s), $items);
            }
            return $items;
        }
        return [];
    }

    private static function extractGrammar(string $response): array
    {
        $issues = [];
        if (preg_match_all('/\{[^}]*"text"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"[^}]*"correction"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"[^}]*\}/s', $response, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $issues[] = [
                    'text'       => str_replace(['\"', '\\\\'], ['"', '\\'], $m[1]),
                    'correction' => str_replace(['\"', '\\\\'], ['"', '\\'], $m[2]),
                ];
            }
        }
        if (empty($issues) && preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"\s*(?:→|->)\s*"((?:[^"\\\\]|\\\\.)*)"/u', $response, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $issues[] = [
                    'text'       => $m[1],
                    'correction' => $m[2],
                ];
            }
        }
        return $issues;
    }

    private static function sanitize(array $data): array
    {
        return [
            'quality_score'         => isset($data['quality_score']) ? max(1, min(10, (float) $data['quality_score'])) : 5.0,
            'ats_score'             => isset($data['ats_score']) ? max(1, min(10, (float) $data['ats_score'])) : 5.0,
            'competitiveness_score' => isset($data['competitiveness_score']) ? max(1, min(10, (float) $data['competitiveness_score'])) : 5.0,
            'strengths'             => is_array($data['strengths'] ?? null) ? array_values($data['strengths']) : [],
            'weaknesses'            => is_array($data['weaknesses'] ?? null) ? array_values($data['weaknesses']) : [],
            'suggestions'           => is_array($data['suggestions'] ?? null) ? array_values($data['suggestions']) : [],
            'grammar_issues'        => self::normalizeGrammar($data['grammar_issues'] ?? []),
        ];
    }

    private static function normalizeGrammar(array $issues): array
    {
        return array_map(function ($item) {
            if (is_string($item)) {
                return ['text' => $item, 'correction' => ''];
            }
            return [
                'text'       => $item['text'] ?? '',
                'correction' => $item['correction'] ?? '',
            ];
        }, array_values($issues));
    }
}