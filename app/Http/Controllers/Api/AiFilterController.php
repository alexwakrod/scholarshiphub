<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\QwenCloudService;
use App\Services\FireworksAiClient;

class AiFilterController extends Controller
{
    public function apply(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $profile = $user->studentProfile;
        if (!$profile) {
            return response()->json(['message' => 'Student profile not found.'], 400);
        }

        $currentFilters = $request->input('current_filters', []);

        // Build student context
        $studentContext = [
            'academic_level' => $profile->academic_level ?? 'N/A',
            'major'          => $profile->major ?? 'N/A',
            'gpa'            => $profile->gpa ?? 'N/A',
            'country'        => $profile->country ?? 'N/A',
            'english'        => $profile->english_proficiency ?? 'N/A',
            'interests'      => $profile->interests ?? [],
            'languages'      => $profile->languages ?? [],
        ];

        $studentContextJson = json_encode($studentContext, JSON_PRETTY_PRINT);
        $currentFiltersJson = json_encode($currentFilters, JSON_PRETTY_PRINT);

        $prompt = <<<EOT
You are an intelligent scholarship filter recommender. The student has the following profile, and the current filters are set. Suggest improved filter values that would yield the best scholarship matches for this student.

Available filter keys and what they do:
- "search": free text search on title and description of scholarships (use words the student is likely to search for, like the scholarship name or keywords)
- "category": provider name (organization offering the scholarship, e.g., "DAAD", "Chevening", "Erasmus"). This is NOT the student's major.
- "country": host country where the study takes place (e.g., "Germany", "United Kingdom")
- "amount_min": minimum scholarship amount in USD (number)
- "amount_max": maximum scholarship amount in USD (number)
- "deadline_before": show only scholarships with deadline before this date (YYYY-MM-DD)
- "min_match": minimum match score percentage (0-100)
- "sort": ordering of results, one of "deadline", "amount", "match_score"

Return ONLY a valid JSON object with:
- "recommended_filters": an object containing ONLY the keys you want to change, from the list above. Do not include keys that are not in the list.
- "reasoning": a short (1-2 sentence) explanation of why these changes were made.

Student Profile:
{$studentContextJson}

Current Filters:
{$currentFiltersJson}
EOT;

        try {
            $qwen = app(QwenCloudService::class);
            $response = $qwen->sendChatRequestRotating('', $prompt, 300, 0.2);

            if (!$response) {
                $fireworks = app(FireworksAiClient::class);
                $response = $fireworks->complete($prompt, ['max_tokens' => 300, 'temperature' => 0.2]);
            }

            if (!$response) {
                return response()->json(['message' => 'AI service unavailable.'], 503);
            }

            $jsonStart = strpos($response, '{');
            $jsonEnd   = strrpos($response, '}');
            if ($jsonStart !== false && $jsonEnd !== false) {
                $json = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
                $data = json_decode($json, true);
                if (is_array($data)) {
                    return response()->json([
                        'recommended_filters' => $data['recommended_filters'] ?? [],
                        'reasoning'           => $data['reasoning'] ?? 'Filters optimized for your profile.',
                    ]);
                }
            }

            return response()->json([
                'recommended_filters' => [],
                'reasoning'           => 'Could not parse AI response. Please try again.',
            ]);

        } catch (\Throwable $e) {
            Log::error('AiFilterController error: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error.'], 500);
        }
    }
}