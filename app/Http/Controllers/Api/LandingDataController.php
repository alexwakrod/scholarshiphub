<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Scholarship;
use App\Models\User;
use App\Models\AiReview;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\SurveyResponse;

class LandingDataController extends Controller
{
    /**
     * Return the same live data the landing page needs (public).
     */
    public function __invoke(Request $request)
    {
        try {
            $data = Cache::remember('api_landing_data', 60, function () {
                return [
                    'activeScholarships' => Scholarship::where('status', 'active')->count(),
                    'registeredStudents' => User::where('role', 'student')->count(),
                    'documentsReviewed'  => AiReview::count(),
                    'faqs'               => Faq::published()->select('id', 'question', 'answer')->get(),
                    'testimonials'       => Testimonial::approved()->latest()->take(3)->select('id', 'quote', 'name_display', 'grade')->get(),
                    'surveyStats'        => SurveyResponse::getStats(),
                    'surveyRespondents'  => SurveyResponse::count(),
                ];
            });

            return response()->json($data);
        } catch (\Throwable $e) {
            Log::error('LandingDataController error: ' . $e->getMessage());
            return response()->json([
                'activeScholarships' => 0,
                'registeredStudents' => 0,
                'documentsReviewed'  => 0,
                'faqs'               => [],
                'testimonials'       => [],
                'surveyStats'        => [],
                'surveyRespondents'  => 0,
            ]);
        }
    }
}