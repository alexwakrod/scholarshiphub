<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Scholarship;
use App\Models\User;
use App\Models\AiReview;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\SurveyResponse;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = Cache::remember('landing_data', config('scholarship.cache_ttls.landing_stats', 3600), function () {
                return $this->fetchLandingData();
            });
        } catch (\Throwable $e) {
            Log::warning('Landing cache failed, fetching fresh data: ' . $e->getMessage());
            $data = $this->fetchLandingData();
        }

        return Inertia::render('Landing', $data);
    }

    private function fetchLandingData(): array
    {
        $activeScholarships = Scholarship::where('status', 'active')->count();
        $registeredStudents  = User::where('role', 'student')->count();
        $documentsReviewed   = AiReview::count();
        $faqs                = Faq::published()->get();
        $testimonials        = Testimonial::approved()->latest()->take(3)->get();
        $surveyStats         = SurveyResponse::getStats();
        $surveyRespondents   = SurveyResponse::count();

        return [
            'activeScholarships' => $activeScholarships,
            'registeredStudents' => $registeredStudents,
            'documentsReviewed'  => $documentsReviewed,
            'faqs'               => $faqs,
            'testimonials'       => $testimonials,
            'surveyStats'        => $surveyStats,
            'surveyRespondents'  => $surveyRespondents,
        ];
    }
}