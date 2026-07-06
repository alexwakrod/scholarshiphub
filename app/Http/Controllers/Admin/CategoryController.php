<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display the category tree management page.
     */
    public function index()
    {
        try {
            $tree = Category::getTree();
            return Inertia::render('Admin/Categories/Index', [
                'tree' => $tree,
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin\CategoryController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/Categories/Index', ['tree' => []]);
        }
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'      => 'required|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'order'     => 'integer|min:0',
            ]);

            $parentId = $validated['parent_id'] ?? null;
            $depth = 0;
            if ($parentId) {
                $parent = Category::findOrFail($parentId);
                $depth = $parent->depth + 1;
            }

            // If order not provided, place at end of siblings
            if (!isset($validated['order'])) {
                $maxOrder = Category::where('parent_id', $parentId)->max('order') ?? 0;
                $validated['order'] = $maxOrder + 1;
            }

            $category = Category::create([
                'name'      => $validated['name'],
                'slug'      => Str::slug($validated['name']),
                'parent_id' => $parentId,
                'order'     => $validated['order'],
                'depth'     => $depth,
            ]);

            Log::info('Category created.', ['category_id' => $category->id]);

            return redirect()->route('admin.categories.index')->with('success', 'Category created.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Admin\CategoryController@store error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Failed to create category.'])->withInput();
        }
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, int $id)
    {
        try {
            $category = Category::findOrFail($id);
            $validated = $request->validate([
                'name'      => 'required|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'order'     => 'integer|min:0',
            ]);

            $category->name = $validated['name'];
            $category->slug = Str::slug($validated['name']);
            $category->order = $validated['order'] ?? $category->order;

            $newParentId = $validated['parent_id'] ?? $category->parent_id;
            $category->parent_id = $newParentId;
            $parent = $newParentId ? Category::find($newParentId) : null;
            $category->depth = $parent ? $parent->depth + 1 : 0;
            $category->save();

            Log::info('Category updated.', ['category_id' => $category->id]);

            return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
        } catch (\Throwable $e) {
            Log::error('Admin\CategoryController@update error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Failed to update category.']);
        }
    }

    /**
     * Delete a category and its children.
     */
    public function destroy(int $id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->children()->delete();
            $category->delete();

            Log::info('Category deleted.', ['category_id' => $id]);

            return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
        } catch (\Throwable $e) {
            Log::error('Admin\CategoryController@destroy error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.categories.index')->with('error', 'Failed to delete category.');
        }
    }

    /**
     * Reorder categories via drag‑and‑drop (sent as array of {id, parent_id, order}).
     */
    public function reorder(Request $request)
    {
        try {
            $request->validate([
                'categories' => 'required|array',
                'categories.*.id' => 'required|exists:categories,id',
                'categories.*.parent_id' => 'nullable|integer',
                'categories.*.order' => 'required|integer|min:0',
            ]);

            foreach ($request->categories as $item) {
                Category::where('id', $item['id'])->update([
                    'parent_id' => $item['parent_id'] ?? null,
                    'order'     => $item['order'],
                ]);
            }

            Log::info('Categories reordered.');

            return response()->json(['message' => 'Order updated.']);
        } catch (\Throwable $e) {
            Log::error('Admin\CategoryController@reorder error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Reorder failed.'], 500);
        }
    }
}