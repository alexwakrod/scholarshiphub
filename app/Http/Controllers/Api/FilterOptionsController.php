<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Models\Scholarship;

class FilterOptionsController extends Controller
{
    public function index()
    {
        $data = Cache::remember('api_filter_options', 3600, function () {
            return [
                'countries'  => Scholarship::active()->distinct()->orderBy('country')->pluck('country')->values(),
                'categories' => Scholarship::active()->distinct()->orderBy('provider')->pluck('provider')->values(),
            ];
        });

        return response()->json($data);
    }
}