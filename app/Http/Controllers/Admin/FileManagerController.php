<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Document;
use ZipArchive;

class FileManagerController extends Controller
{
    /**
     * List all uploaded documents with pagination and search.
     */
    public function index(Request $request)
    {
        try {
            $query = Document::with('user:id,name,email')
                ->orderBy('created_at', 'desc');

            if ($search = $request->input('search')) {
                $query->where('file_name', 'ILIKE', "%{$search}%");
            }
            if ($type = $request->input('file_type')) {
                $query->where('file_type', $type);
            }

            $documents = $query->paginate(30)->withQueryString();

            return Inertia::render('Admin/FileManager/Index', [
                'documents' => $documents,
                'filters'   => $request->only(['search', 'file_type']),
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin\FileManagerController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/FileManager/Index', ['documents' => [], 'filters' => []]);
        }
    }

    /**
     * Delete a document.
     */
    public function destroy(int $id)
    {
        try {
            $document = Document::findOrFail($id);
            Storage::disk('local')->delete($document->file_path);
            $document->delete();

            Log::info('Admin deleted document.', ['document_id' => $id]);

            return response()->json(['message' => 'File deleted.']);
        } catch (\Throwable $e) {
            Log::error('Admin\FileManagerController@destroy error: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['message' => 'Delete failed.'], 500);
        }
    }

    /**
     * Download a single document.
     */
    public function download(int $id)
    {
        try {
            $document = Document::findOrFail($id);
            if (!Storage::disk('local')->exists($document->file_path)) {
                abort(404, 'File not found on disk.');
            }

            return Storage::disk('local')->download($document->file_path, $document->file_name);
        } catch (\Throwable $e) {
            Log::error('Admin\FileManagerController@download error: ' . $e->getMessage(), ['id' => $id]);
            abort(500, 'Download failed.');
        }
    }

    /**
     * Batch download selected documents as ZIP.
     */
    public function batchDownload(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No files selected.');
        }

        $documents = Document::whereIn('id', $ids)->get();
        if ($documents->isEmpty()) {
            return back()->with('error', 'No valid files found.');
        }

        $zipName = 'documents_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipName);
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Log::error('Cannot create zip file: ' . $zipPath);
            return back()->with('error', 'Failed to create zip.');
        }

        foreach ($documents as $doc) {
            $diskPath = Storage::disk('local')->path($doc->file_path);
            if (file_exists($diskPath)) {
                $zip->addFile($diskPath, $doc->file_name);
            }
        }
        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }
}