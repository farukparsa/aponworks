<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

type Memory = {
    id: number;
    type: 'task' | 'note' | 'diary';
    title: string | null;
    description: string;
    status: string;
    due_at: string | null;
    deleted_at: string | null;
    created_at: string;
    updated_at: string;
};

const page = usePage();

const memories = computed<Memory[]>(() => {
    return (page.props.memories as Memory[]) ?? [];
});

const currentView = computed<string>(() => {
    return (page.props.currentView as string) ?? 'all';
});

const isDeletedView = computed(() => {
    return [
        'deleted-tasks',
        'deleted-diary',
        'deleted-notes',
    ].includes(currentView.value);
});

const pageTitle = computed(() => {
    switch (currentView.value) {
        case 'completed':
            return 'Completed Tasks';
        case 'deleted-tasks':
            return 'Deleted Tasks';
        case 'deleted-diary':
            return 'Deleted Diary';
        case 'deleted-notes':
            return 'Deleted Notes';
        default:
            return 'All Memories';
    }
});

const moveToBin = (memoryId: number) => {
    const confirmed = window.confirm('Move this item to BIN?');

    if (!confirmed) {
        return;
    }

    router.delete(`/memories/${memoryId}`);
};

const reopenTask = (memoryId: number) => {
    router.patch(`/memories/${memoryId}/reopen`);
};

const formatDate = (date: string | null) => {
    if (!date) {
        return '';
    }

    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head :title="pageTitle" />

    <div class="min-h-screen bg-slate-50">
        <div class="flex min-h-screen">
            <!-- LEFT SIDEBAR -->
            <aside
                class="hidden w-64 shrink-0 border-r border-slate-200 bg-white p-5 md:block"
            >
                <div class="mb-8">
                    <div class="text-xl font-bold text-slate-900">
                        APONWORKS
                    </div>

                    <div class="mt-1 text-xs text-slate-400">
                        Personal AI Secretary
                    </div>
                </div>

                <nav class="space-y-2">
                    <Link
                        href="/dashboard"
                        class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50"
                    >
                        🏠 Dashboard
                    </Link>

                    <div class="pt-3">
                        <p
                            class="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            Memories
                        </p>

                        <Link
                            href="/memories?view=all"
                            class="block rounded-xl px-3 py-2.5 text-sm font-medium"
                            :class="
                                currentView === 'all'
                                    ? 'bg-slate-900 text-white'
                                    : 'text-slate-600 hover:bg-slate-50'
                            "
                        >
                            🧠 All Memories
                        </Link>

                        <Link
                            href="/memories?view=completed"
                            class="block rounded-xl px-3 py-2.5 text-sm font-medium"
                            :class="
                                currentView === 'completed'
                                    ? 'bg-slate-900 text-white'
                                    : 'text-slate-600 hover:bg-slate-50'
                            "
                        >
                            ✅ Completed Tasks
                        </Link>

                        <div class="mt-4">
                            <p
                                class="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                BIN
                            </p>

                            <Link
                                href="/memories?view=deleted-tasks"
                                class="block rounded-xl px-3 py-2 text-sm"
                                :class="
                                    currentView === 'deleted-tasks'
                                        ? 'bg-slate-100 font-semibold text-slate-900'
                                        : 'text-slate-500 hover:bg-slate-50'
                                "
                            >
                                Deleted Tasks
                            </Link>

                            <Link
                                href="/memories?view=deleted-diary"
                                class="block rounded-xl px-3 py-2 text-sm"
                                :class="
                                    currentView === 'deleted-diary'
                                        ? 'bg-slate-100 font-semibold text-slate-900'
                                        : 'text-slate-500 hover:bg-slate-50'
                                "
                            >
                                Deleted Diary
                            </Link>

                            <Link
                                href="/memories?view=deleted-notes"
                                class="block rounded-xl px-3 py-2 text-sm"
                                :class="
                                    currentView === 'deleted-notes'
                                        ? 'bg-slate-100 font-semibold text-slate-900'
                                        : 'text-slate-500 hover:bg-slate-50'
                                "
                            >
                                Deleted Notes
                            </Link>
                        </div>
                    </div>
                </nav>
            </aside>

            <!-- MAIN CONTENT -->
            <main class="min-w-0 flex-1 p-4 md:p-8">
                <div class="mx-auto max-w-5xl">
                    <!-- MOBILE TOP -->
                    <div class="mb-5 md:hidden">
                        <Link
                            href="/dashboard"
                            class="text-sm font-medium text-slate-500"
                        >
                            ← Dashboard
                        </Link>
                    </div>

                    <!-- HEADER -->
                    <div
                        class="mb-6 flex flex-wrap items-center justify-between gap-3"
                    >
                        <div>
                            <h1
                                class="text-2xl font-bold text-slate-900 md:text-3xl"
                            >
                                {{ pageTitle }}
                            </h1>

                            <p class="mt-1 text-sm text-slate-500">
                                Manage your saved APONWORKS memories.
                            </p>
                        </div>

                        <Link
                            href="/dashboard"
                            class="rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50"
                        >
                            Dashboard
                        </Link>
                    </div>

                    <!-- MOBILE MEMORY NAVIGATION -->
                    <div
                        class="mb-5 flex gap-2 overflow-x-auto pb-2 md:hidden"
                    >
                        <Link
                            href="/memories?view=all"
                            class="whitespace-nowrap rounded-xl bg-white px-3 py-2 text-sm ring-1 ring-slate-200"
                        >
                            All
                        </Link>

                        <Link
                            href="/memories?view=completed"
                            class="whitespace-nowrap rounded-xl bg-white px-3 py-2 text-sm ring-1 ring-slate-200"
                        >
                            Completed
                        </Link>

                        <Link
                            href="/memories?view=deleted-tasks"
                            class="whitespace-nowrap rounded-xl bg-white px-3 py-2 text-sm ring-1 ring-slate-200"
                        >
                            BIN Tasks
                        </Link>

                        <Link
                            href="/memories?view=deleted-diary"
                            class="whitespace-nowrap rounded-xl bg-white px-3 py-2 text-sm ring-1 ring-slate-200"
                        >
                            BIN Diary
                        </Link>

                        <Link
                            href="/memories?view=deleted-notes"
                            class="whitespace-nowrap rounded-xl bg-white px-3 py-2 text-sm ring-1 ring-slate-200"
                        >
                            BIN Notes
                        </Link>
                    </div>

                    <!-- EMPTY -->
                    <div
                        v-if="memories.length === 0"
                        class="rounded-3xl bg-white p-10 text-center shadow-sm"
                    >
                        <div class="text-4xl">
                            {{
                                isDeletedView
                                    ? '🗑️'
                                    : currentView === 'completed'
                                      ? '✅'
                                      : '🧠'
                            }}
                        </div>

                        <h2
                            class="mt-4 text-lg font-semibold text-slate-800"
                        >
                            No items here
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            {{
                                isDeletedView
                                    ? 'There are no deleted items in this category.'
                                    : currentView === 'completed'
                                      ? 'You have no completed tasks yet.'
                                      : 'Your saved memories will appear here.'
                            }}
                        </p>
                    </div>

                    <!-- ITEMS -->
                    <div v-else class="space-y-3">
                        <article
                            v-for="memory in memories"
                            :key="memory.id"
                            class="rounded-2xl bg-white p-5 shadow-sm"
                        >
                            <div
                                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="mb-3 flex flex-wrap items-center gap-2"
                                    >
                                        <span
                                            class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold uppercase text-slate-600"
                                        >
                                            {{ memory.type }}
                                        </span>

                                        <span
                                            v-if="
                                                currentView === 'completed'
                                            "
                                            class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs text-slate-500"
                                        >
                                            Completed
                                        </span>

                                        <span
                                            v-if="
                                                isDeletedView &&
                                                memory.deleted_at
                                            "
                                            class="text-xs text-slate-400"
                                        >
                                            Deleted
                                            {{
                                                formatDate(memory.deleted_at)
                                            }}
                                        </span>
                                    </div>

                                    <h2
                                        v-if="memory.title"
                                        class="mb-2 font-semibold text-slate-900"
                                    >
                                        {{ memory.title }}
                                    </h2>

                                    <p
                                        class="whitespace-pre-wrap break-words text-sm leading-6 text-slate-700"
                                    >
                                        {{ memory.description }}
                                    </p>

                                    <p
                                        v-if="memory.due_at"
                                        class="mt-3 text-xs text-slate-400"
                                    >
                                        Due:
                                        {{ formatDate(memory.due_at) }}
                                    </p>
                                </div>

                                <!-- ACTIONS -->
                                <div
                                    class="flex shrink-0 flex-wrap items-center gap-2"
                                >
                                    <!-- DELETED ITEM: OPEN ONLY -->
                                    <Link
                                        v-if="isDeletedView"
                                        :href="`/memories/deleted/${memory.id}`"
                                        class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
                                    >
                                        Open →
                                    </Link>

                                    <!-- COMPLETED TASK -->
                                    <template
                                        v-else-if="
                                            currentView === 'completed'
                                        "
                                    >
                                        <button
                                            type="button"
                                            @click="
                                                reopenTask(memory.id)
                                            "
                                            class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200"
                                        >
                                            ↩ Reopen
                                        </button>

                                        <button
                                            type="button"
                                            @click="
                                                moveToBin(memory.id)
                                            "
                                            class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-500 ring-1 ring-slate-200 hover:bg-slate-50"
                                        >
                                            🗑 Move to BIN
                                        </button>
                                    </template>

                                    <!-- ACTIVE MEMORY -->
                                    <button
                                        v-else
                                        type="button"
                                        @click="
                                            moveToBin(memory.id)
                                        "
                                        class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-500 ring-1 ring-slate-200 hover:bg-slate-50"
                                    >
                                        🗑 Move to BIN
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>