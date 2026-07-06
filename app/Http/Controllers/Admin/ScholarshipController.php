<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Scholarship;
use App\Jobs\ParseScholarshipEligibilityJob;

class ScholarshipController extends Controller
{
    /**
     * List all scholarships with search and pagination.
     */
    public function index(Request $request)
    {
        try {
            $query = Scholarship::query();

            if ($search = $request->input('search')) {
                $query->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$search]);
            }
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            $sortField = $request->input('sort', 'created_at');
            $sortDir = $request->input('direction', 'desc');
            $allowed = ['id', 'title', 'provider', 'country', 'deadline', 'status', 'created_at'];
            if (in_array($sortField, $allowed)) {
                $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $perPage = $request->input('per_page', 20);
            $scholarships = $query->paginate($perPage)->withQueryString();

            return Inertia::render('Admin/Scholarships/Index', [
                'scholarships' => $scholarships,
                'filters'      => $request->only(['search', 'status', 'sort', 'direction', 'per_page']),
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin\ScholarshipController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/Scholarships/Index', ['scholarships' => [], 'filters' => []]);
        }
    }

    /**
     * Edit form for a scholarship.
     */
    public function edit(int $id)
    {
        try {
            $scholarship = Scholarship::findOrFail($id);
            return Inertia::render('Admin/Scholarships/Edit', ['scholarship' => $scholarship]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.scholarships.index')->with('error', 'Scholarship not found.');
        } catch (\Throwable $e) {
            Log::error('Admin\ScholarshipController@edit error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('admin.scholarships.index')->with('error', 'Failed to load scholarship.');
        }
    }

    /**
     * Update scholarship details.
     */
    public function update(Request $request, int $id)
    {
        try {
            $scholarship = Scholarship::findOrFail($id);
            $validated = $request->validate([
                'title'       => 'required|string|max:500',
                'description' => 'required|string',
                'provider'    => 'required|string|max:255',
                'country'     => 'required|string|max:100',
                'amount'      => 'nullable|numeric|min:0',
                'deadline'    => 'required|date',
                'source_url'  => 'nullable|url',
                'status'      => 'required|in:active,expired',
                'parsed_requirements' => 'nullable|json',
            ]);

            if (isset($validated['parsed_requirements']) && is_string($validated['parsed_requirements'])) {
                $validated['parsed_requirements'] = json_decode($validated['parsed_requirements'], true);
            }

            $scholarship->update($validated);

            Log::info('Admin updated scholarship.', ['scholarship_id' => $id, 'user_id' => $request->user()?->id]);

            return redirect()->route('admin.scholarships.index')->with('success', 'Scholarship updated.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Admin\ScholarshipController@update error: ' . $e->getMessage(), ['id' => $id]);
            return back()->withErrors(['error' => 'Update failed.'])->withInput();
        }
    }

    /**
     * Delete a scholarship.
     */
    public function destroy(int $id)
    {
        try {
            $scholarship = Scholarship::findOrFail($id);
            $scholarship->delete();
            Log::info('Admin deleted scholarship.', ['scholarship_id' => $id]);
            return redirect()->route('admin.scholarships.index')->with('success', 'Scholarship deleted.');
        } catch (\Throwable $e) {
            Log::error('Admin\ScholarshipController@destroy error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('admin.scholarships.index')->with('error', 'Deletion failed.');
        }
    }

    /**
     * Trigger AI parsing for a scholarship.
     */
    public function triggerParse(int $id)
    {
        try {
            $scholarship = Scholarship::findOrFail($id);
            ParseScholarshipEligibilityJob::dispatch($scholarship);
            Log::info('AI parse triggered by admin.', ['scholarship_id' => $id]);
            return back()->with('success', 'AI parsing started.');
        } catch (\Throwable $e) {
            Log::error('Admin\ScholarshipController@triggerParse error: ' . $e->getMessage(), ['id' => $id]);
            return back()->with('error', 'Failed to trigger parsing.');
        }
    }

    /**
     * Batch edit selected scholarships.
     */
    public function batchEdit(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids'    => 'required|array|min:1',
                'ids.*'  => 'integer|exists:scholarships,id',
                'fields' => 'required|array',
                'fields.status'   => 'sometimes|in:active,expired',
                'fields.provider' => 'sometimes|string|max:255',
                'fields.country'  => 'sometimes|string|max:100',
            ]);

            $updateData = [];
            if (isset($validated['fields']['status'])) {
                $updateData['status'] = $validated['fields']['status'];
            }
            if (isset($validated['fields']['provider'])) {
                $updateData['provider'] = $validated['fields']['provider'];
            }
            if (isset($validated['fields']['country'])) {
                $updateData['country'] = $validated['fields']['country'];
            }

            if (empty($updateData)) {
                return response()->json(['message' => 'No fields to update.'], 422);
            }

            Scholarship::whereIn('id', $validated['ids'])->update($updateData);

            Log::info('Admin batch edited scholarships.', [
                'ids'    => $validated['ids'],
                'fields' => $updateData,
                'user_id' => $request->user()?->id,
            ]);

            return response()->json(['message' => 'Scholarships updated.']);
        } catch (\Throwable $e) {
            Log::error('Admin\ScholarshipController@batchEdit error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Batch edit failed.'], 500);
        }
    }

    /**
     * Duplicate a scholarship.
     */
    public function duplicate(int $id)
    {
        try {
            $original = Scholarship::findOrFail($id);
            $duplicate = $original->replicate();
            $duplicate->title = $original->title . ' (copy)';
            $duplicate->save();

            Log::info('Admin duplicated scholarship.', [
                'original_id' => $original->id,
                'duplicate_id' => $duplicate->id,
                'user_id' => request()->user()?->id,
            ]);

            return response()->json(['message' => 'Scholarship duplicated.', 'id' => $duplicate->id]);
        } catch (\Throwable $e) {
            Log::error('Admin\ScholarshipController@duplicate error: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['message' => 'Duplication failed.'], 500);
        }
    }

    /**
     * Return the last change diff for a scholarship from the activity log.
     */
    public function diff(int $id)
    {
        try {
            $scholarship = Scholarship::findOrFail($id);
            $log = \App\Models\ActivityLog::where('subject_type', Scholarship::class)
                ->where('subject_id', $id)
                ->where('action', 'updated')
                ->whereNotNull('properties')
                ->latest('created_at')
                ->first();

            if (!$log || empty($log->properties['old']) || empty($log->properties['new'])) {
                return response()->json(['message' => 'No changes found.'], 404);
            }

            return response()->json([
                'old' => $log->properties['old'],
                'new' => $log->properties['new'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Scholarship diff error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed.'], 500);
        }
    }
}