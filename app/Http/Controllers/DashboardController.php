<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $recentMemories = $request->user()
            ->memories()
            ->latest()
            ->take(5)
            ->get([
                'id',
                'type',
                'title',
                'description',
                'created_at',
            ]);

        return Inertia::render('Dashboard', [
            'recentMemories' => $recentMemories,
        ]);
    }
}