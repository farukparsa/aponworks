<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:note,task,diary'],
            'description' => ['required', 'string', 'max:10000'],
        ]);

        Memory::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'description' => $validated['description'],
            'source_type' => 'manual',
            'status' => 'active',
            'user_confirmed' => true,
        ]);

        return back()->with('success', 'Memory saved successfully.');
    }
}