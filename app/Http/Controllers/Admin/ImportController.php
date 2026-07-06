<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\ImportJob;
use App\Jobs\ImportScholarshipsJob;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    /**
     * Show import interface with history.
     */
    public function index()
    {
        try {
            $imports = ImportJob::orderByDesc('created_at')->limit(5)->get();
            return Inertia::render('Admin/Import/Wizard', [
                'imports' => $imports,
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin\ImportController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/Import/Wizard', ['imports' => []]);
        }
    }
    /**
     * Upload CSV and return preview data (first 5 rows + headers).
     */
    public function preview(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            $path = $request->file('file')->store('imports', 'local');
            $fullPath = Storage::disk('local')->path($path);
            $handle = fopen($fullPath, 'r');
            if (!$handle) {
                return response()->json(['message' => 'Cannot read file.'], 422);
            }

            $headers = fgetcsv($handle);
            if (!$headers) {
                fclose($handle);
                return response()->json(['message' => 'CSV file is empty.'], 422);
            }

            $rows = [];
            $rowCount = 0;
            while (($row = fgetcsv($handle)) !== false && $rowCount < 5) {
                $assoc = [];
                foreach ($headers as $i => $h) {
                    $assoc[$h] = $row[$i] ?? '';
                }
                $rows[] = $assoc;
                $rowCount++;
            }
            fclose($handle);

            // Delete temp file (we'll re-upload for actual import)
            Storage::disk('local')->delete($path);

            return response()->json([
                'headers' => $headers,
                'rows'    => $rows,
            ]);
        } catch (\Throwable $e) {
            Log::error('ImportController@preview error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Preview failed.'], 500);
        }
    }

    /**
     * Start the import with column mapping.
     */
    public function start(Request $request)
    {
        try {
            $request->validate([
                'file'    => 'required|file|mimes:csv,txt|max:10240',
                'mapping' => 'required|json',
            ]);

            $file = $request->file('file');
            $path = $file->store('imports', 'local');
            $mapping = json_decode($request->mapping, true);

            // Validate that all required target fields are mapped
            $requiredTargets = ['title', 'description', 'provider', 'country', 'deadline'];
            foreach ($requiredTargets as $target) {
                if (empty($mapping[$target])) {
                    return response()->json(['message' => "Field '{$target}' must be mapped."], 422);
                }
            }

            $importJob = ImportJob::create([
                'user_id'        => Auth::id(),
                'file_path'      => $path,
                'status'         => 'pending',
                'column_mapping' => $mapping,
            ]);

            ImportScholarshipsJob::dispatch($importJob->id)->delay(now()->addSeconds(2));

            Log::info('Import job created with mapping.', [
                'import_job_id' => $importJob->id,
                'user_id'       => Auth::id(),
            ]);

            return response()->json([
                'jobId'   => $importJob->id,
                'message' => 'Import started.',
            ]);
        } catch (\Throwable $e) {
            Log::error('ImportController@start error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to start import.'], 500);
        }
    }

    /**
     * Check import job status and progress.
     */
    public function status(int $jobId)
    {
        try {
            $importJob = ImportJob::findOrFail($jobId);

            return response()->json([
                'id'             => $importJob->id,
                'status'         => $importJob->status,
                'total_rows'     => $importJob->total_rows,
                'processed_rows' => $importJob->processed_rows,
                'failed_rows'    => $importJob->failed_rows,
                'progress'       => $importJob->total_rows > 0
                    ? round(($importJob->processed_rows / $importJob->total_rows) * 100, 2)
                    : 0,
                'error_messages' => $importJob->error_messages,
                'started_at'     => $importJob->started_at,
                'finished_at'    => $importJob->finished_at,
                'created_at'     => $importJob->created_at->toDateTimeString(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Import job not found: ' . $jobId);
            return response()->json(['message' => 'Import job not found.'], 404);
        } catch (\Throwable $e) {
            Log::error('ImportController@status error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Failed to retrieve status.'], 500);
        }
    }
}