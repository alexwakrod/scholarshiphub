<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LogFacade;

class ActivityLogObserver
{
    /**
     * Record 'created' events.
     */
    public function created($model): void
    {
        try {
            if (Auth::check()) {
                ActivityLog::log(
                    Auth::id(),
                    'created',
                    get_class($model),
                    $model->id,
                    $model->toArray(),
                    'Created ' . class_basename($model)
                );
            }
        } catch (\Throwable $e) {
            LogFacade::error('ActivityLogObserver@created error: ' . $e->getMessage());
        }
    }

    /**
     * Record 'updated' events, including a diff of changed attributes.
     */
    public function updated($model): void
    {
        try {
            if (Auth::check()) {
                $original = $model->getOriginal();
                $changes = $model->getChanges();
                // Remove timestamps from diff to reduce noise
                unset($original['updated_at'], $changes['updated_at']);
                $diff = array_intersect_key($changes, $original);
                $properties = [
                    'old' => array_intersect_key($original, $diff),
                    'new' => $diff,
                ];
                ActivityLog::log(
                    Auth::id(),
                    'updated',
                    get_class($model),
                    $model->id,
                    $properties,
                    'Updated ' . class_basename($model)
                );
            }
        } catch (\Throwable $e) {
            LogFacade::error('ActivityLogObserver@updated error: ' . $e->getMessage());
        }
    }

    /**
     * Record 'deleted' events.
     */
    public function deleted($model): void
    {
        try {
            if (Auth::check()) {
                ActivityLog::log(
                    Auth::id(),
                    'deleted',
                    get_class($model),
                    $model->id,
                    $model->toArray(),
                    'Deleted ' . class_basename($model)
                );
            }
        } catch (\Throwable $e) {
            LogFacade::error('ActivityLogObserver@deleted error: ' . $e->getMessage());
        }
    }
}