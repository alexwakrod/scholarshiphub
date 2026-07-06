<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ChartAnnotation;

class AnnotationController extends Controller
{
    public function index(Request $request)
    {
        $chartId = $request->input('chart_id');
        $annotations = ChartAnnotation::where('chart_id', $chartId)
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($annotations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'chart_id'   => 'required|string|max:255',
            'position_x' => 'required|numeric|min:0|max:100',
            'position_y' => 'required|numeric|min:0|max:100',
            'note'       => 'required|string|max:1000',
        ]);

        $annotation = ChartAnnotation::create([
            'user_id'    => Auth::id(),
            'chart_id'   => $validated['chart_id'],
            'position_x' => $validated['position_x'],
            'position_y' => $validated['position_y'],
            'note'       => $validated['note'],
        ]);

        Log::info('Chart annotation created.', ['annotation_id' => $annotation->id]);

        return response()->json($annotation, 201);
    }

    public function update(Request $request, int $id)
    {
        $annotation = ChartAnnotation::where('user_id', Auth::id())->findOrFail($id);
        $annotation->update($request->validate([
            'note' => 'required|string|max:1000',
        ]));

        return response()->json(['message' => 'Updated.']);
    }

    public function destroy(int $id)
    {
        $annotation = ChartAnnotation::where('user_id', Auth::id())->findOrFail($id);
        $annotation->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}