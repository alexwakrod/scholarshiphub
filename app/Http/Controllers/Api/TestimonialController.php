<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $validated = $request->validate([
                'name_display' => 'required|string|max:255',
                'grade'        => 'nullable|string|max:50',
                'quote'        => 'required|string|max:2000',
            ]);

            $testimonial = Testimonial::create([
                'user_id'      => $request->user()?->id,   // null if guest
                'name_display' => $validated['name_display'],
                'grade'        => $validated['grade'] ?? null,
                'quote'        => $validated['quote'],
                'is_approved'  => false,
            ]);

            Log::info('Testimonial submitted via API.', ['testimonial_id' => $testimonial->id]);

            // Invalidate landing cache so new testimonial appears after admin approval
            \Illuminate\Support\Facades\Cache::forget('landing_data');
            \Illuminate\Support\Facades\Cache::forget('api_landing_stats');

            return response()->json(['message' => 'Testimonial submitted for review.'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Api\TestimonialController error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Submission failed.'], 500);
        }
    }
}