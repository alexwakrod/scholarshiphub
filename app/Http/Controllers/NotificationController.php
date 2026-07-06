<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Return unread notification count as JSON.
     */
    public function unreadCount()
    {
        try {
            $count = Notification::where('user_id', Auth::id())->unread()->count();
            return response()->json(['count' => $count]);
        } catch (\Throwable $e) {
            Log::error('NotificationController@unreadCount error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['count' => 0], 500);
        }
    }

    /**
     * List notifications for the user (Inertia page).
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->integer('per_page', 15);
            $notifications = Notification::where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->paginate($perPage);

            return Inertia::render('Notifications/Index', [
                'notifications' => $notifications,
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Notifications/Index', ['notifications' => []]);
        }
    }

    /**
     * Mark a notification as read.
     */
    public function markRead(int $id)
    {
        try {
            $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
            $notification->markAsRead();

            return response()->json(['message' => 'Marked as read.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Notification not found for mark read: ' . $id);
            return response()->json(['message' => 'Not found.'], 404);
        } catch (\Throwable $e) {
            Log::error('NotificationController@markRead error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed.'], 500);
        }
    }

    /**
     * Return recent notifications as JSON (used by NotificationBell).
     */
    public function recent(Request $request)
    {
        try {
            $limit = $request->integer('limit', 5);
            $notifications = Notification::where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($notif) {
                    return [
                        'id'         => $notif->id,
                        'type'       => $notif->type,
                        'data'       => $notif->data,
                        'read_at'    => $notif->read_at ? $notif->read_at->toISOString() : null,
                        'created_at' => $notif->created_at->toISOString(),
                    ];
                });

            return response()->json($notifications);
        } catch (\Throwable $e) {
            Log::error('NotificationController@recent error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function markAllRead()
    {
        try {
            Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            Log::info('All notifications marked as read.', ['user_id' => Auth::id()]);
            return response()->json(['message' => 'All notifications marked as read.']);
        } catch (\Throwable $e) {
            Log::error('NotificationController@markAllRead error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed.'], 500);
        }
    }
}