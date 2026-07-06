<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use App\Models\StudentProfile;
use App\Jobs\RecomputeMatchesJob;
use App\Services\ProfileCompletionService;

class ProfileController extends Controller
{
    public function edit()
    {
        try {
            $user = Auth::user();
            $profile = $user->studentProfile;
            return Inertia::render('Profile/Edit', [
                'profile' => $profile,
                'user' => $user->only('id', 'name', 'email', 'avatar_url'),
            ]);
        } catch (\Throwable $e) {
            Log::error('ProfileController@edit error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Profile/Edit', ['profile' => null, 'user' => Auth::user()->only('id', 'name', 'email', 'avatar_url')]);
        }
    }

    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            Log::info('Profile update request.', ['user_id' => $user->id, 'input' => $request->all()]);

            $request->validate([
                'full_name' => 'required|string|max:255',
                'academic_level' => 'nullable|string',
                'major' => 'nullable|string|max:255',
                'gpa' => 'nullable|numeric|min:0|max:4.00',
                'date_of_birth' => 'nullable|date',
                'country' => 'nullable|string|max:100',
                'demographics' => 'nullable|json',
                'interests' => 'nullable|json',
                'english_proficiency' => 'nullable|string|max:50',
                'languages' => 'nullable|json',
                'bio' => 'nullable|string|max:2000',
                'avatar' => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar_url = $path;
                $user->save();
            }

            $profileData = $request->only([
                'full_name', 'academic_level', 'major', 'gpa', 'date_of_birth',
                'country', 'demographics', 'interests', 'english_proficiency', 'languages', 'bio',
            ]);
            foreach (['demographics', 'interests', 'languages'] as $field) {
                if (isset($profileData[$field]) && is_string($profileData[$field])) {
                    $profileData[$field] = json_decode($profileData[$field], true);
                }
            }

            Log::info('Profile data after decode:', $profileData);

            $profile = $user->studentProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );

            // Recalculate completion using the service (updated below)
            $completionService = app(ProfileCompletionService::class);
            $profile->profile_completed = $completionService->isComplete($profile);
            $profile->save();

            Log::info('Profile updated.', ['user_id' => $user->id, 'profile_completed' => $profile->profile_completed]);

            if ($profile->profile_completed) {
                RecomputeMatchesJob::dispatch($profile->id);
            }

            Cache::forget('landing_data');

            return redirect()->route('profile.edit')->with('success', 'Profile updated.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Profile update validation error:', $e->errors());
            throw $e;
        } catch (\Throwable $e) {
            Log::error('ProfileController@update error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'user_id' => Auth::id()]);
            return back()->withErrors(['error' => 'Failed to update profile.'])->withInput();
        }
    }
}