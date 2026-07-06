<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    /**
     * List all testimonials (approved and pending).
     */
    public function index()
    {
        try {
            $testimonials = Testimonial::with('user')
                ->orderByDesc('created_at')
                ->get();
            return Inertia::render('Admin/Testimonials/Index', ['testimonials' => $testimonials]);
        } catch (\Throwable $e) {
            Log::error('Admin\TestimonialController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/Testimonials/Index', ['testimonials' => []]);
        }
    }

    /**
     * Approve a testimonial.
     */
    public function approve(int $id)
    {
        try {
            $testimonial = Testimonial::findOrFail($id);
            $testimonial->approve();
            Log::info('Testimonial approved.', ['testimonial_id' => $id]);
            return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial approved.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Testimonial not found for approval: ' . $id);
            return redirect()->route('admin.testimonials.index')->with('error', 'Testimonial not found.');
        } catch (\Throwable $e) {
            Log::error('Admin\TestimonialController@approve error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.testimonials.index')->with('error', 'Failed to approve testimonial.');
        }
    }

    /**
     * Delete a testimonial.
     */
    public function destroy(int $id)
    {
        try {
            $testimonial = Testimonial::findOrFail($id);
            $testimonial->delete();
            Log::info('Testimonial deleted.', ['testimonial_id' => $id]);
            return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Testimonial not found for deletion: ' . $id);
            return redirect()->route('admin.testimonials.index')->with('error', 'Testimonial not found.');
        } catch (\Throwable $e) {
            Log::error('Admin\TestimonialController@destroy error: ' . $e->getMessage(), ['id' => $id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.testimonials.index')->with('error', 'Failed to delete testimonial.');
        }
    }
}