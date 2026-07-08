<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        try {
            $user = $request->user();
            $userData = null;
            $profileData = null;
            $impersonating = session()->has('impersonator_id');
            $impersonatedName = null;

            if ($user) {
                $userData = [
                    'id'              => $user->id,
                    'name'            => $user->name,
                    'email'           => $user->email,
                    'role'            => $user->role->value,
                    'avatar_url'      => $user->avatar_url,
                    'locale'          => $user->locale,
                ];

                if ($user->studentProfile) {
                    $profileData = $user->studentProfile->toArray();
                }
            }

            if ($impersonating) {
                $impersonator = User::find(session('impersonator_id'));
                $impersonatedName = optional($impersonator)->name;
            }

            $shared = array_merge(parent::share($request), [
                'ziggy' => function () use ($request) {
                    return array_merge((new Ziggy)->toArray(), [
                        'location' => $request->url(),
                    ]);
                },
                'auth' => [
                    'user'    => $userData,
                    'profile' => $profileData,
                ],
                'bookmarkedIds' => $user ? \App\Models\Bookmark::where('user_id', $user->id)->pluck('scholarship_id')->toArray() : [],
                'flash' => [
                    'success' => fn () => $request->session()->get('success'),
                    'error'   => fn () => $request->session()->get('error'),
                ],
                'featureFlags' => Cache::get('feature_flags', []),
                'locale'       => $user?->locale ?? config('app.locale'),
            ]);

            return $shared;
        } catch (\Throwable $e) {
            report($e);
            return array_merge(parent::share($request), [
                'error' => 'Failed to load shared data.',
            ]);
        }
    }
}