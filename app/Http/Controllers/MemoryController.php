<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemoryController extends Controller
{
    /**
     * All active memories.
     */
    public function index()
    {
        $memories = Memory::query()
            ->where('user_id', auth()->id())
            ->where('status', '!=', 'completed')
            ->latest()
            ->get();

        return Inertia::render('Memories/Index', [
            'memories' => $memories,
            'view' => 'all',
        ]);
    }

    /**
     * Active Tasks workspace.
     */
    public function tasks()
    {
        $tasks = Memory::query()
            ->where('user_id', auth()->id())
            ->where('type', 'task')
            ->where('status', '!=', 'completed')
            ->latest()
            ->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Notes.
     */
    public function notes()
    {
        $memories = Memory::query()
            ->where('user_id', auth()->id())
            ->where('type', 'note')
            ->latest()
            ->get();

        return Inertia::render('Memories/Index', [
            'memories' => $memories,
            'view' => 'notes',
        ]);
    }

    /**
     * Diary.
     */
    public function diary()
    {
        $memories = Memory::query()
            ->where('user_id', auth()->id())
            ->where('type', 'diary')
            ->latest()
            ->get();

        return Inertia::render('Memories/Index', [
            'memories' => $memories,
            'view' => 'diary',
        ]);
    }

    /**
     * Completed Tasks.
     */
    public function completedTasks()
    {
        $tasks = Memory::query()
            ->where('user_id', auth()->id())
            ->where('type', 'task')
            ->where('status', 'completed')
            ->latest('updated_at')
            ->get();

        return Inertia::render('Tasks/Completed', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Deleted Tasks.
     */
    public function deletedTasks()
    {
        $memories = Memory::onlyTrashed()
            ->where('user_id', auth()->id())
            ->where('type', 'task')
            ->latest('deleted_at')
            ->get();

        return Inertia::render('Memories/Index', [
            'memories' => $memories,
            'view' => 'deleted-tasks',
        ]);
    }

    /**
     * Deleted Notes.
     */
    public function deletedNotes()
    {
        $memories = Memory::onlyTrashed()
            ->where('user_id', auth()->id())
            ->where('type', 'note')
            ->latest('deleted_at')
            ->get();

        return Inertia::render('Memories/Index', [
            'memories' => $memories,
            'view' => 'deleted-notes',
        ]);
    }

    /**
     * Deleted Diary.
     */
    public function deletedDiary()
    {
        $memories = Memory::onlyTrashed()
            ->where('user_id', auth()->id())
            ->where('type', 'diary')
            ->latest('deleted_at')
            ->get();

        return Inertia::render('Memories/Index', [
            'memories' => $memories,
            'view' => 'deleted-diary',
        ]);
    }

    /**
     * Create Task, Note or Diary entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:note,task,diary'],
            'description' => ['required', 'string'],
            'due_at' => ['nullable', 'date'],
        ]);

        Memory::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'description' => $validated['description'],
            'due_at' => $validated['due_at'] ?? null,
            'source_type' => 'manual',
            'status' => 'active',
        ]);

        return back();
    }

    /**
     * Edit an active memory.
     */
    public function update(Request $request, Memory $memory)
    {
        abort_unless($memory->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'description' => ['required', 'string'],
            'due_at' => ['nullable', 'date'],
        ]);

        $memory->update([
            'description' => $validated['description'],
            'due_at' => $validated['due_at'] ?? null,
        ]);

        return back();
    }

    /**
     * Complete a Task.
     */
    public function complete(Memory $memory)
    {
        abort_unless($memory->user_id === auth()->id(), 403);
        abort_unless($memory->type === 'task', 404);

        $memory->update([
            'status' => 'completed',
        ]);

        return back();
    }

    /**
     * Reopen a completed Task.
     */
    public function reopen(Memory $memory)
    {
        abort_unless($memory->user_id === auth()->id(), 403);
        abort_unless($memory->type === 'task', 404);

        $memory->update([
            'status' => 'active',
        ]);

        return back();
    }

    /**
     * Move an item to BIN.
     */
    public function destroy(Memory $memory)
    {
        abort_unless($memory->user_id === auth()->id(), 403);

        $memory->delete();

        return back();
    }

    /**
     * Open one deleted item.
     */
    public function showDeleted($memory)
    {
        $memory = Memory::onlyTrashed()
            ->where('user_id', auth()->id())
            ->findOrFail($memory);

        return Inertia::render('Memories/DeletedShow', [
            'memory' => $memory,
        ]);
    }

    /**
     * Restore an item from BIN.
     */
    public function restore($memory)
    {
        $memory = Memory::onlyTrashed()
            ->where('user_id', auth()->id())
            ->findOrFail($memory);

        $memory->restore();

        if ($memory->type === 'task' && $memory->status === 'completed') {
            return redirect('/tasks/completed');
        }

        if ($memory->type === 'task') {
            return redirect('/tasks');
        }

        if ($memory->type === 'note') {
            return redirect('/notes');
        }

        if ($memory->type === 'diary') {
            return redirect('/diary');
        }

        return redirect('/memories');
    }

    /**
     * Permanently delete an item from BIN.
     */
    public function forceDestroy($memory)
    {
        $memory = Memory::onlyTrashed()
            ->where('user_id', auth()->id())
            ->findOrFail($memory);

        $type = $memory->type;

        $memory->forceDelete();

        if ($type === 'task') {
            return redirect('/bin/tasks');
        }

        if ($type === 'note') {
            return redirect('/bin/notes');
        }

        if ($type === 'diary') {
            return redirect('/bin/diary');
        }

        return redirect('/memories');
    }
}