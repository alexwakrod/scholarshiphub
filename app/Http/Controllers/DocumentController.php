<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Document;
use App\Enums\DocumentType;
use App\Services\AiTextExtractor;

class DocumentController extends Controller
{
    /**
     * List all documents for the authenticated user.
     */
    public function index()
    {
        try {
            $documents = Document::where('user_id', Auth::id())
                ->with('latestReview')
                ->orderBy('file_type')
                ->orderByDesc('version_number')
                ->get()
                ->groupBy('file_type')
                ->map(function ($group) {
                    return [
                        'file_type' => $group->first()->file_type,
                        'versions' => $group->map(function ($doc) {
                            $review = $doc->latestReview;
                            return [
                                'id' => $doc->id,
                                'file_name' => $doc->file_name,
                                'version_number' => $doc->version_number,
                                'created_at' => $doc->created_at->toISOString(),
                                'quality_score' => $review?->quality_score,
                                'ats_score' => $review?->ats_score,
                                'competitiveness_score' => $review?->competitiveness_score,
                            ];
                        })->values(),
                    ];
                })->values();

            return Inertia::render('Documents/Index', [
                'documents' => $documents,
                'documentTypes' => array_column(DocumentType::cases(), 'value'),
            ]);
        } catch (\Throwable $e) {
            Log::error('DocumentController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Documents/Index', ['documents' => [], 'documentTypes' => []]);
        }
    }

    /**
     * Show a specific document with its review details.
     */
    public function show(int $id)
    {
        try {
            $document = Document::where('user_id', Auth::id())->findOrFail($id);
            $review = $document->latestReview;

            return Inertia::render('Documents/Show', [
                'document' => $document,
                'review' => $review ? [
                    'quality_score' => $review->quality_score,
                    'ats_score' => $review->ats_score,
                    'competitiveness_score' => $review->competitiveness_score,
                    'strengths' => $review->strengths,
                    'weaknesses' => $review->weaknesses,
                    'suggestions' => $review->suggestions,
                    'grammar_issues' => $review->grammar_issues,
                ] : null,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Document not found: ' . $id);
            return redirect()->route('documents.index')->with('error', 'Document not found.');
        } catch (\Throwable $e) {
            Log::error('DocumentController@show error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('documents.index')->with('error', 'An error occurred.');
        }
    }

    /**
     * Store a new document upload.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'file' => [
                    'required',
                    'file',
                    'max:' . config('scholarship.upload.max_file_size', 10240),
                    'mimetypes:' . implode(',', config('scholarship.upload.allowed_mime_types', [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'text/plain',
                    ])),
                ],
                'file_type' => 'required|in:cv,personal_statement,other',
            ]);

            $user = Auth::user();
            $file = $request->file('file');
            $fileType = $request->input('file_type');

            // Determine next version number
            $latest = Document::where('user_id', $user->id)
                ->where('file_type', $fileType)
                ->max('version_number') ?? 0;
            $version = $latest + 1;

            // Store file
            $path = $file->store('documents/' . $user->id, 'local');
            $originalName = $file->getClientOriginalName();

            // Extract text
            $extracted = '';
            try {
                $extractor = app(AiTextExtractor::class);
                $extracted = $extractor->extract($file->getPathname(), $file->getClientOriginalExtension());
            } catch (\Throwable $extractError) {
                Log::warning('Text extraction failed: ' . $extractError->getMessage());
            }

            $document = Document::create([
                'user_id' => $user->id,
                'file_type' => $fileType,
                'file_path' => $path,
                'file_name' => $originalName,
                'text_extracted' => $extracted,
                'version_number' => $version,
            ]);

            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['id' => $document->id, 'message' => 'Document uploaded.'], 201);
            }
            Log::info('Document uploaded.', ['document_id' => $document->id, 'user_id' => $user->id]);
            return redirect()->route('documents.index')->with('success', 'Document uploaded.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('DocumentController@store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['file' => 'Upload failed.'])->withInput();
        }
    }

    /**
     * Delete a document.
     */
    public function destroy(int $id)
    {
        try {
            $document = Document::where('user_id', Auth::id())->findOrFail($id);
            Storage::disk('local')->delete($document->file_path);
            $document->delete();

            Log::info('Document deleted.', ['document_id' => $id, 'user_id' => Auth::id()]);

            return redirect()->route('documents.index')->with('success', 'Document deleted.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Document not found for deletion: ' . $id);
            return redirect()->route('documents.index')->with('error', 'Document not found.');
        } catch (\Throwable $e) {
            Log::error('DocumentController@destroy error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('documents.index')->with('error', 'Deletion failed.');
        }
    }
}