<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Faq;

class FaqController extends Controller
{
    /**
     * List all FAQs.
     */
    public function index()
    {
        try {
            $faqs = Faq::orderBy('order')->get();
            return Inertia::render('Admin/Faqs/Index', ['faqs' => $faqs]);
        } catch (\Throwable $e) {
            Log::error('Admin\FaqController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/Faqs/Index', ['faqs' => []]);
        }
    }

    /**
     * Store a new FAQ.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string',
                'answer' => 'required|string',
                'order' => 'required|integer|min:0',
                'is_published' => 'boolean',
            ]);

            $faq = Faq::create($validated);
            Log::info('FAQ created.', ['faq_id' => $faq->id]);

            return redirect()->route('admin.faqs.index')->with('success', 'FAQ added.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Admin\FaqController@store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Failed to create FAQ.'])->withInput();
        }
    }

    /**
     * Update an existing FAQ.
     */
    public function update(Request $request, int $id)
    {
        try {
            $faq = Faq::findOrFail($id);
            $validated = $request->validate([
                'question' => 'required|string',
                'answer' => 'required|string',
                'order' => 'required|integer|min:0',
                'is_published' => 'boolean',
            ]);

            $faq->update($validated);
            Log::info('FAQ updated.', ['faq_id' => $faq->id]);

            return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('FAQ not found for update: ' . $id);
            return redirect()->route('admin.faqs.index')->with('error', 'FAQ not found.');
        } catch (\Throwable $e) {
            Log::error('Admin\FaqController@update error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Failed to update FAQ.'])->withInput();
        }
    }

    /**
     * Delete an FAQ.
     */
    public function destroy(int $id)
    {
        try {
            $faq = Faq::findOrFail($id);
            $faq->delete();
            Log::info('FAQ deleted.', ['faq_id' => $id]);
            return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('FAQ not found for deletion: ' . $id);
            return redirect()->route('admin.faqs.index')->with('error', 'FAQ not found.');
        } catch (\Throwable $e) {
            Log::error('Admin\FaqController@destroy error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.faqs.index')->with('error', 'Failed to delete FAQ.');
        }
    }
}