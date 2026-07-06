<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Scholarship;
use App\Models\AiReview;
use App\Models\MatchScore;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            return Inertia::render('Admin/Dashboard', [
                'totalUsers'      => User::count(),
                'totalStudents'   => User::where('role', 'student')->count(),
                'totalAdmins'     => User::where('role', 'admin')->count(),
                'activeScholarships' => Scholarship::where('status', 'active')->count(),
                'expiredScholarships' => Scholarship::where('status', 'expired')->count(),
                'totalAiReviews'  => AiReview::count(),
                'totalApplications' => \App\Models\Application::count(),
                'averageMatchScore' => round(MatchScore::avg('overall_score') ?? 0, 1),
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin\DashboardController@index error: ' . $e->getMessage());
            return Inertia::render('Admin/Dashboard', [])->with('error', 'Failed to load admin dashboard.');
        }
    }
}