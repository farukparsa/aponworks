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
            'due_at' => ['nullable', 'date'],
        ]);

        Memory::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'description' => $validated['description'],
            'due_at' => $validated['due_at'] ?? null,
            'source_type' => 'manual',
            'status' => 'active',
            'user_confirmed' => true,
        ]);

        return back()->with('success', 'Memory saved successfully.');
    }

    public function complete(Request $request, Memory $memory): RedirectResponse
    {
        abort_unless($memory->user_id === $request->user()->id, 403);

        $memory->update([
            'status' => 'completed',
        ]);

        return back()->with('success', 'Task completed.');
    }

    public function reopen(Request $request, Memory $memory): RedirectResponse
    {
        abort_unless($memory->user_id === $request->user()->id, 403);

        $memory->update([
            'status' => 'active',
        ]);

        return back()->with('success', 'Task reopened.');
    }

    public function destroy(Request $request, Memory $memory): RedirectResponse
    {
        abort_unless($memory->user_id === $request->user()->id, 403);

        $memory->delete();

        return back()->with('success', 'Memory moved to Trash.');
    }
}