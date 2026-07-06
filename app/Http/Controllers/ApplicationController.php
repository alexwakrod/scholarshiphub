<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\Application;
use App\Enums\ApplicationStatus;

class ApplicationController extends Controller
{
    /**
     * Display the user's applications in a Kanban board.
     */
    public function index()
    {
        try {
            $applications = Application::with('scholarship')
                ->where('user_id', Auth::id())
                ->get()
                ->groupBy('status')
                ->map(function ($group, $status) {
                    return [
                        'status' => $status,
                        'items' => $group->map(function ($app) {
                            return [
                                'id' => $app->id,
                                'scholarship_id' => $app->scholarship_id,
                                'title' => $app->scholarship->title,
                                'deadline' => $app->scholarship->deadline->toDateString(),
                                'status' => $app->status->value,
                                'match_score' => $this->getMatchScore($app->scholarship_id),
                                'checklist' => $app->checklist,
                                'notes' => $app->notes,
                            ];
                        })->values(),
                    ];
                })->values();

            return Inertia::render('Applications/Index', [
                'columns' => $applications,
                'statuses' => array_column(ApplicationStatus::cases(), 'value'),
            ]);
        } catch (\Throwable $e) {
            Log::error('ApplicationController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Applications/Index', ['columns' => [], 'statuses' => []]);
        }
    }

    /**
     * Create a new application for a scholarship.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'scholarship_id' => 'required|exists:scholarships,id',
            ]);

            // Check if already exists
            $existing = Application::where('user_id', Auth::id())
                ->where('scholarship_id', $validated['scholarship_id'])
                ->first();
            if ($existing) {
                return response()->json(['message' => 'Application already exists.'], 409);
            }

            $application = Application::create([
                'user_id' => Auth::id(),
                'scholarship_id' => $validated['scholarship_id'],
                'status' => ApplicationStatus::Interested,
                'checklist' => [],
                'notes' => null,
            ]);

            Log::info('Application created.', ['application_id' => $application->id, 'user_id' => Auth::id()]);

            return redirect()->route('applications.index')->with('success', 'Application started.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('ApplicationController@store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to create application.');
        }
    }

    /**
     * Update an existing application (status, checklist, notes).
     */
    public function update(Request $request, int $id)
    {
        try {
            $application = Application::where('user_id', Auth::id())->findOrFail($id);

            $validated = $request->validate([
                'status' => 'sometimes|in:' . implode(',', array_column(ApplicationStatus::cases(), 'value')),
                'checklist' => 'sometimes|array',
                'notes' => 'nullable|string',
                'submitted_at' => 'nullable|date',
            ]);

            if (isset($validated['status'])) {
                $application->status = ApplicationStatus::from($validated['status']);
            }
            if (isset($validated['checklist'])) {
                $application->checklist = $validated['checklist'];
            }
            if (isset($validated['notes'])) {
                $application->notes = $validated['notes'];
            }
            if (isset($validated['submitted_at'])) {
                $application->submitted_at = $validated['submitted_at'];
            }

            $application->save();

            Log::info('Application updated.', ['application_id' => $application->id]);

            return response()->json(['message' => 'Application updated.']);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Application not found for update: ' . $id);
            return response()->json(['message' => 'Not found.'], 404);
        } catch (\Throwable $e) {
            Log::error('ApplicationController@update error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Update failed.'], 500);
        }
    }

    /**
     * Retrieve match score for a scholarship (helper).
     */
    private function getMatchScore(int $scholarshipId): ?float
    {
        try {
            $profile = Auth::user()?->studentProfile;
            if (!$profile || !$profile->profile_completed) return null;
            return \App\Models\MatchScore::where('student_profile_id', $profile->id)
                ->where('scholarship_id', $scholarshipId)
                ->value('overall_score');
        } catch (\Throwable $e) {
            Log::error('getMatchScore error: ' . $e->getMessage());
            return null;
        }
    }
}