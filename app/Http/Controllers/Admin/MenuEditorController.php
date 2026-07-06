<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuEditorController extends Controller
{
    /**
     * Show the menu customization page.
     */
    public function index()
    {
        return Inertia::render('Admin/MenuEditor/Index');
    }
}