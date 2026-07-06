<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SurveyResponse;

class SurveyController extends Controller
{
    /**
     * Submit a survey response.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'interested'      => 'boolean',
                'want_ai_feedback' => 'boolean',
                'willing_to_pay'  => 'boolean',
                'extra_data'      => 'nullable|array',
            ]);

            $survey = SurveyResponse::create([
                'user_id'          => $request->user()?->id,
                'interested'       => $validated['interested'] ?? null,
                'want_ai_feedback' => $validated['want_ai_feedback'] ?? null,
                'willing_to_pay'   => $validated['willing_to_pay'] ?? null,
                'extra_data'       => $validated['extra_data'] ?? null,
            ]);

            Log::info('Survey response submitted.', ['survey_id' => $survey->id]);

            return response()->json(['message' => 'Thank you for your feedback!'], 201);
        } catch (\Throwable $e) {
            Log::error('SurveyController error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Submission failed.'], 500);
        }
    }
}