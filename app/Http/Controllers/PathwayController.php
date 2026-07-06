<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use App\Models\Pathway;
use App\Jobs\GeneratePathwayJob;

class PathwayController extends Controller
{
    public function index()
    {
        try {
            $pathway = Pathway::where('user_id', Auth::id())
                ->latest('generated_at')
                ->first();

            return Inertia::render('Pathway/Index', [
                'pathway' => $pathway ? [
                    'id' => $pathway->id,
                    'generated_at' => $pathway->generated_at->toISOString(),
                    'ai_explanation' => $pathway->ai_explanation,
                    'milestone_sequence' => $pathway->milestone_sequence,
                ] : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('PathwayController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Pathway/Index', ['pathway' => null]);
        }
    }

    public function generate()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                Log::warning('Pathway generate: no authenticated user.');
                return back()->with('error', 'You must be logged in.');
            }

            $profile = $user->studentProfile;
            if (!$profile || !$profile->profile_completed) {
                Log::warning('Pathway generate: profile incomplete for user ' . $user->id);
                return back()->with('error', 'Please complete your profile first.');
            }

            // Rate limiting check
            $key = 'pathway-generation:' . $user->id;
            if (RateLimiter::tooManyAttempts($key, 1)) {
                $seconds = RateLimiter::availableIn($key);
                Log::info('Pathway generate: rate limited for user ' . $user->id . '. Available in ' . $seconds . 's');
                return back()->with('error', "You can only generate a pathway once per hour. Please wait {$seconds} seconds.");
            }

            RateLimiter::hit($key, 3600);

            // Dispatch the job
            GeneratePathwayJob::dispatch($user);
            Log::info('Pathway job dispatched from controller.', ['user_id' => $user->id]);

            return back()->with('success', 'Pathway generation started. It will appear here shortly.');
        } catch (\Throwable $e) {
            Log::error('PathwayController@generate error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to start pathway generation.');
        }
    }
}