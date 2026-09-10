<?php

namespace App\Http\Controllers;

use App\Models\Memory;
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
                'status',
                'created_at',
            ]);

        $todayMemories = $request->user()
            ->memories()
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_at')
            ->whereDate('due_at', now()->toDateString())
            ->orderBy('due_at')
            ->get([
                'id',
                'type',
                'title',
                'description',
                'status',
                'due_at',
            ]);

        $tomorrowMemories = $request->user()
            ->memories()
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_at')
            ->whereDate('due_at', now()->addDay()->toDateString())
            ->orderBy('due_at')
            ->get([
                'id',
                'type',
                'title',
                'description',
                'status',
                'due_at',
            ]);

        $dayAfterTomorrowMemories = $request->user()
            ->memories()
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_at')
            ->whereDate('due_at', now()->addDays(2)->toDateString())
            ->orderBy('due_at')
            ->get([
                'id',
                'type',
                'title',
                'description',
                'status',
                'due_at',
            ]);

        $upcomingMemories = $request->user()
            ->memories()
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_at')
            ->whereDate('due_at', '>', now()->addDays(2)->toDateString())
            ->orderBy('due_at')
            ->get([
                'id',
                'type',
                'title',
                'description',
                'status',
                'due_at',
            ]);

        $completedMemories = $request->user()
            ->memories()
            ->where('type', 'task')
            ->where('status', 'completed')
            ->latest('updated_at')
            ->get([
                'id',
                'type',
                'title',
                'description',
                'status',
                'due_at',
                'updated_at',
            ]);

        $trashedMemories = Memory::onlyTrashed()
            ->where('user_id', $request->user()->id)
            ->latest('deleted_at')
            ->get([
                'id',
                'type',
                'title',
                'description',
                'status',
                'due_at',
                'expiry_at',
                'deleted_at',
            ]);

        $customDays = max(1, (int) $request->integer('days', 7));
        $customType = $request->string('type')->toString();

        if (! in_array($customType, ['all', 'task', 'note', 'diary', 'expiry'], true)) {
            $customType = 'all';
        }

        $customScheduleQuery = $request->user()
            ->memories()
            ->where('status', '!=', 'completed');

        if ($customType === 'expiry') {
            $customScheduleQuery
                ->whereNotNull('expiry_at')
                ->whereDate('expiry_at', '>=', now()->toDateString())
                ->whereDate('expiry_at', '<=', now()->addDays($customDays)->toDateString())
                ->orderBy('expiry_at');
        } else {
            $customScheduleQuery
                ->whereNotNull('due_at')
                ->whereDate('due_at', '>=', now()->toDateString())
                ->whereDate('due_at', '<=', now()->addDays($customDays)->toDateString());

            if ($customType !== 'all') {
                $customScheduleQuery->where('type', $customType);
            }

            $customScheduleQuery->orderBy('due_at');
        }

        $customScheduleMemories = $customScheduleQuery->get([
            'id',
            'type',
            'title',
            'description',
            'status',
            'due_at',
            'expiry_at',
        ]);

        return Inertia::render('Dashboard', [
            'recentMemories' => $recentMemories,
            'todayMemories' => $todayMemories,
            'tomorrowMemories' => $tomorrowMemories,
            'dayAfterTomorrowMemories' => $dayAfterTomorrowMemories,
            'upcomingMemories' => $upcomingMemories,
            'completedMemories' => $completedMemories,
            'trashedMemories' => $trashedMemories,
            'customScheduleMemories' => $customScheduleMemories,
            'customDays' => $customDays,
            'customType' => $customType,
        ]);
    }
}