<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\TwoFactorAuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AiReviewController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\PathwayController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\ScholarshipController as AdminScholarshipController;
use App\Http\Controllers\Admin\ImportController as AdminImportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Admin\FeatureFlagController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\UserExportController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ScheduledTaskController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\Admin\MenuPreferencesController;
use App\Http\Controllers\Admin\FileManagerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomReportController;
use App\Http\Controllers\Admin\SharedDashboardController;
use App\Http\Controllers\Admin\DashboardLayoutController;
use App\Http\Controllers\Admin\AnnotationController;
use App\Http\Controllers\Public\SharedDashboardController as PublicSharedDashboardController;
use App\Http\Controllers\Api\MatchStatusController;
use App\Http\Controllers\Api\MatchBreakdownController;
use App\Http\Controllers\Api\ActiveJobsController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\TopMatchesController;
use App\Http\Controllers\Api\ScholarshipListController;
use App\Http\Controllers\Api\DocumentReviewStatusController;
use App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // Password reset
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');

    // Socialite redirects
    Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
        ->where('provider', 'github|google')
        ->name('socialite.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
        ->where('provider', 'github|google')
        ->name('socialite.callback');

    // 2FA challenge (guest because user is logged out before challenge)
    Route::get('/auth/2fa/challenge', [TwoFactorAuthController::class, 'challenge'])->name('auth.2fa.challenge');
    Route::post('/auth/2fa/verify', [TwoFactorAuthController::class, 'verifyChallenge'])->name('auth.2fa.verify');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes – EXCLUDED from profile completion check
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Profile (always accessible)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/complete', fn () => inertia('Profile/Complete'))->name('profile.complete');

    // 2FA setup / status / disable (should be reachable even with incomplete profile)
    Route::get('/auth/2fa/setup', [TwoFactorAuthController::class, 'setup'])->name('auth.2fa.setup');
    Route::post('/auth/2fa/enable', [TwoFactorAuthController::class, 'enable'])->name('auth.2fa.enable');
    Route::post('/auth/2fa/disable', [TwoFactorAuthController::class, 'disable'])->name('auth.2fa.disable');
    Route::get('/auth/2fa/status', [TwoFactorAuthController::class, 'status'])->name('auth.2fa.status');

    // Notifications (users should be able to see notifications regardless of profile completion)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::put('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
     ->name('notifications.markAllRead');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/email/send-code', [SettingsController::class, 'sendVerificationCode'])
        ->name('settings.email.send-code');
    Route::post('/settings/email/verify', [SettingsController::class, 'verifyEmail'])
        ->name('settings.email.verify');
    Route::post('/settings/deletion/schedule', [SettingsController::class, 'scheduleDeletion'])
        ->name('settings.deletion.schedule');
    Route::post('/settings/deletion/cancel', [SettingsController::class, 'cancelDeletion'])
        ->name('settings.deletion.cancel');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes – REQUIRE profile completion
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Scholarships
    Route::get('/scholarships', [ScholarshipController::class, 'index'])->name('scholarships.index');
    Route::get('/scholarships/{id}', [ScholarshipController::class, 'show'])->name('scholarships.show');

    // Documents
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{id}', [DocumentController::class, 'show'])->name('documents.show');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::post('/documents/{id}/review', [AiReviewController::class, 'requestReview'])->name('documents.review');
    Route::put('/documents/{document}/review/toggle', [App\Http\Controllers\Api\ReviewToggleController::class, 'toggle'])
    ->name('documents.review.toggle');
    Route::get('/documents/{id}/review-status', [DocumentReviewStatusController::class, 'show'])
    ->name('api.documents.review-status');
    Route::post('/documents/{document}/stream-review', \App\Http\Controllers\Api\DocumentReviewStreamController::class)
    ->name('documents.stream-review');

    // Applications
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
    Route::put('/applications/{id}', [ApplicationController::class, 'update'])->name('applications.update');

    // Bookmarks
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/bookmarks/{scholarshipId}', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');

    // Pathway
    Route::get('/pathway', [PathwayController::class, 'index'])->name('pathway.index');
    Route::get('/test-pathway-job', function () {
        $user = Auth::user();
        \App\Jobs\GeneratePathwayJob::dispatch($user);
        Log::info('Test pathway job dispatched manually.');
        return 'Job dispatched. Check queue worker.';
    });
    Route::post('/pathway/generate', [PathwayController::class, 'generate'])->name('pathway.generate');
});

/*
|--------------------------------------------------------------------------
| Internal API routes (session‑based, prefixed with /api)
|--------------------------------------------------------------------------
*/
Route::prefix('api')->group(function () {
    Route::post('/testimonials', \App\Http\Controllers\Api\TestimonialController::class);
    Route::post('/survey', [\App\Http\Controllers\Api\SurveyController::class, 'store']);
    Route::get('/landing-data', \App\Http\Controllers\Api\LandingDataController::class);
    Route::post('/check-email', [\App\Http\Controllers\Api\EmailCheckController::class, 'check']);
});

Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/matches-status', MatchStatusController::class);
    Route::get('/matches/breakdown/{scholarshipId}', MatchBreakdownController::class);
    Route::get('/jobs/active', ActiveJobsController::class);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/top-matches', TopMatchesController::class);
    Route::get('/stats/live', StatsController::class);
    Route::get('/scholarships', ScholarshipListController::class);
    Route::post('/scholarships/ai-filter', [App\Http\Controllers\Api\AiFilterController::class, 'apply']);
    Route::get('/upcoming-deadlines', [App\Http\Controllers\Api\UpcomingDeadlinesController::class, 'index']);
    Route::get('/documents/{id}/review-status', [\App\Http\Controllers\Api\DocumentReviewStatusController::class, 'show'])
        ->name('api.documents.review-status');
    Route::get('/filter-options', [App\Http\Controllers\Api\FilterOptionsController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    
    // Admin Dashboard (YESSS BOI)
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Scholarships
    Route::get('/scholarships', [AdminScholarshipController::class, 'index'])->name('scholarships.index');
    Route::get('/scholarships/{id}/edit', [AdminScholarshipController::class, 'edit'])->name('scholarships.edit');
    Route::put('/scholarships/{id}', [AdminScholarshipController::class, 'update'])->name('scholarships.update');
    Route::delete('/scholarships/{id}', [AdminScholarshipController::class, 'destroy'])->name('scholarships.destroy');
    Route::post('/scholarships/{id}/parse', [AdminScholarshipController::class, 'triggerParse'])->name('scholarships.parse');
    Route::post('/scholarships/{id}/duplicate', [AdminScholarshipController::class, 'duplicate'])->name('scholarships.duplicate');
    Route::get('/scholarships/{id}/diff', [AdminScholarshipController::class, 'diff'])->name('scholarships.diff');
    Route::put('/scholarships/batch-edit', [AdminScholarshipController::class, 'batchEdit'])->name('scholarships.batch-edit');

    // Import
    Route::get('/import', [AdminImportController::class, 'index'])->name('import.index');
    Route::post('/import/preview', [AdminImportController::class, 'preview'])->name('import.preview');
    Route::post('/import/start', [AdminImportController::class, 'start'])->name('import.start');
    Route::get('/import/status/{jobId}', [AdminImportController::class, 'status'])->name('import.status');

    // AI Scrape Scholarships
    Route::post('/scrape', function () {
        \App\Jobs\ScrapeScholarshipsJob::dispatch();
        return back()->with('success', 'Scholarship scraping started.');
    })->name('scrape');

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/export', [UserExportController::class, 'export'])->name('users.export');

    // Analytics
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/annotations', [AnnotationController::class, 'index']);
    Route::post('/analytics/annotations', [AnnotationController::class, 'store']);
    Route::put('/analytics/annotations/{id}', [AnnotationController::class, 'update']);
    Route::delete('/analytics/annotations/{id}', [AnnotationController::class, 'destroy']);

    // Export
    Route::get('/export', [ExportController::class, 'export'])->name('export');

    // FAQs
    Route::get('/faqs', [AdminFaqController::class, 'index'])->name('faqs.index');
    Route::post('/faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
    Route::put('/faqs/{id}', [AdminFaqController::class, 'update'])->name('faqs.update');
    Route::delete('/faqs/{id}', [AdminFaqController::class, 'destroy'])->name('faqs.destroy');

    // Impersonate
    Route::post('/impersonate/{userId}', [ImpersonationController::class, 'start'])->name('impersonate.start');
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

    // Testimonials
    Route::get('/testimonials', [AdminTestimonialController::class, 'index'])->name('testimonials.index');
    Route::post('/testimonials/{id}/approve', [AdminTestimonialController::class, 'approve'])->name('testimonials.approve');
    Route::delete('/testimonials/{id}', [AdminTestimonialController::class, 'destroy'])->name('testimonials.destroy');

    // Feature flags
    Route::get('/feature-flags', [FeatureFlagController::class, 'index'])->name('feature-flags.index');
    Route::post('/feature-flags/toggle', [FeatureFlagController::class, 'toggle'])->name('feature-flags.toggle');

    // Notification templates
    Route::get('/notification-templates', [NotificationTemplateController::class, 'index'])->name('notification-templates.index');
    Route::get('/notification-templates/create', [NotificationTemplateController::class, 'edit'])->name('notification-templates.create');
    Route::get('/notification-templates/{id}/edit', [NotificationTemplateController::class, 'edit'])->name('notification-templates.edit');
    Route::post('/notification-templates/save/{id?}', [NotificationTemplateController::class, 'save'])->name('notification-templates.save');
    Route::post('/notification-templates/preview', [NotificationTemplateController::class, 'preview'])->name('notification-templates.preview');
    Route::post('/notification-templates/test-send', [NotificationTemplateController::class, 'testSend'])->name('notification-templates.test-send');
    Route::delete('/notification-templates/{id}', [NotificationTemplateController::class, 'destroy'])->name('notification-templates.destroy');

    // Scheduled tasks
    Route::get('/scheduled-tasks', [ScheduledTaskController::class, 'index'])->name('scheduled-tasks.index');
    Route::post('/scheduled-tasks/run', [ScheduledTaskController::class, 'run'])->name('scheduled-tasks.run');

    // Audit logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/recent', [AuditLogController::class, 'recent'])->name('audit-logs.recent');

    // File manager
    Route::get('/file-manager', [FileManagerController::class, 'index'])->name('file-manager.index');
    Route::delete('/file-manager/{id}', [FileManagerController::class, 'destroy'])->name('file-manager.destroy');
    Route::get('/file-manager/{id}/download', [FileManagerController::class, 'download'])->name('file-manager.download');
    Route::get('/file-manager/batch-download', [FileManagerController::class, 'batchDownload'])->name('file-manager.batch-download');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

    // Reports
    Route::get('/reports', [CustomReportController::class, 'index'])->name('reports.index');
    Route::post('/reports', [CustomReportController::class, 'store'])->name('reports.store');
    Route::put('/reports/{id}', [CustomReportController::class, 'update'])->name('reports.update');
    Route::delete('/reports/{id}', [CustomReportController::class, 'destroy'])->name('reports.destroy');
    Route::post('/reports/preview', [CustomReportController::class, 'preview'])->name('reports.preview');

    // Shared dashboards
    Route::get('/shared-dashboards', [SharedDashboardController::class, 'index'])->name('shared-dashboards.index');
    Route::post('/shared-dashboards', [SharedDashboardController::class, 'store'])->name('shared-dashboards.store');
    Route::put('/shared-dashboards/{id}', [SharedDashboardController::class, 'update'])->name('shared-dashboards.update');
    Route::delete('/shared-dashboards/{id}', [SharedDashboardController::class, 'destroy'])->name('shared-dashboards.destroy');

    // Menu preferences
    Route::get('/menu/preferences', [MenuPreferencesController::class, 'show'])->name('menu.preferences');
    Route::put('/menu/preferences', [MenuPreferencesController::class, 'update']);

    // Dashboard layout
    Route::get('/dashboard/layout', [DashboardLayoutController::class, 'index']);
    Route::put('/dashboard/layout', [DashboardLayoutController::class, 'save']);

    // Activity heatmap
    Route::get('/activity-heatmap', \App\Http\Controllers\Admin\ActivityHeatmapController::class);
});

// Public shared dashboard (no auth)
Route::get('/dashboard/shared/{token}', [PublicSharedDashboardController::class, 'show'])->name('public.shared-dashboard');