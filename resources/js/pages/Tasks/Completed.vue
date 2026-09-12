<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

type Task = {
    id: number;
    title: string | null;
    description: string;
    status: string;
    due_at: string | null;
    created_at: string;
    updated_at: string;
};

const props = defineProps<{
    tasks: Task[];
}>();

const reopenTask = (task: Task) => {
    router.patch(
        `/memories/${task.id}/reopen`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const deleteTask = (task: Task) => {
    const confirmed = window.confirm(
        'Move this completed task to BIN?',
    );

    if (!confirmed) {
        return;
    }

    router.delete(`/memories/${task.id}`, {
        preserveScroll: true,
    });
};

const formatDate = (value: string | null) => {
    if (!value) {
        return '';
    }

    const dateValue = value.substring(0, 10);
    const [year, month, day] = dateValue.split('-');

    return new Date(
        Number(year),
        Number(month) - 1,
        Number(day),
    ).toLocaleDateString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Completed Tasks - APONWORKS" />

    <div class="min-h-screen bg-[#f8f7f4] p-4 md:p-6">
        <div
            class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[220px_1fr]"
        >
            <!-- LEFT SIDEBAR -->
            <aside
                class="hidden rounded-3xl bg-white p-5 shadow-sm lg:block"
            >
                <div class="mb-8">
                    <Link
                        href="/dashboard"
                        class="block"
                    >
                        <h1 class="text-2xl font-bold text-slate-900">
                            APONWORKS
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Your Personal AI Secretary
                        </p>
                    </Link>
                </div>

                <nav class="space-y-3 text-sm">
                    <Link
                        href="/dashboard"
                        class="block rounded-xl px-4 py-3 text-slate-600 hover:bg-slate-100"
                    >
                        Home
                    </Link>

                    <!-- CREATE -->
                    <div
                        class="rounded-2xl border border-slate-100 p-2"
                    >
                        <div
                            class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            Create
                        </div>

                        <div class="space-y-1">
                            <Link
                                href="/tasks"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                ✅ Add Task
                            </Link>

                            <Link
                                href="/dashboard"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                📝 Add Note
                            </Link>

                            <Link
                                href="/dashboard"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                📖 Write Diary
                            </Link>
                        </div>
                    </div>

                    <!-- VIEW -->
                    <div
                        class="rounded-2xl border border-slate-100 p-2"
                    >
                        <div
                            class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            View
                        </div>

                        <div class="space-y-1">
                            <Link
                                href="/memories"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                📋 All
                            </Link>

                            <Link
                                href="/tasks"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                ✅ Tasks
                            </Link>

                            <Link
                                href="/notes"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                📝 Notes
                            </Link>

                            <Link
                                href="/diary"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                📖 Diary
                            </Link>

                            <Link
                                href="/tasks/completed"
                                class="block rounded-xl bg-slate-100 px-3 py-2 font-semibold text-slate-900"
                            >
                                ✓ Completed Tasks
                            </Link>
                        </div>
                    </div>

                    <!-- BIN -->
                    <div
                        class="rounded-2xl border border-slate-100 p-2"
                    >
                        <div
                            class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            🗑 BIN
                        </div>

                        <div class="space-y-1">
                            <Link
                                href="/bin/tasks"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                Deleted Tasks
                            </Link>

                            <Link
                                href="/bin/notes"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                Deleted Notes
                            </Link>

                            <Link
                                href="/bin/diary"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                Deleted Diary
                            </Link>
                        </div>
                    </div>

                    <a
                        href="#"
                        class="block rounded-xl px-4 py-3 text-slate-600 hover:bg-slate-100"
                    >
                        Documents
                    </a>

                    <a
                        href="#"
                        class="block rounded-xl px-4 py-3 text-slate-600 hover:bg-slate-100"
                    >
                        People
                    </a>
                </nav>

                <div
                    class="mt-10 rounded-2xl bg-slate-100 p-4"
                >
                    <p class="text-xs font-medium text-slate-500">
                        Storage Used
                    </p>

                    <p class="mt-2 text-sm font-semibold text-slate-800">
                        0 MB
                    </p>
                </div>

                <div
                    class="mt-4 rounded-2xl border border-dashed border-slate-200 p-4"
                >
                    <p class="text-xs text-slate-400">
                        Sponsored
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        Future advertisement space
                    </p>
                </div>
            </aside>

            <!-- MAIN -->
            <main class="min-w-0">
                <!-- HEADER -->
                <section
                    class="mb-6 rounded-[2rem] bg-white p-6 shadow-sm md:p-8"
                >
                    <div
                        class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <div
                                class="mb-3 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-500"
                            >
                                Task History
                            </div>

                            <h2
                                class="text-3xl font-bold tracking-tight text-slate-900 md:text-4xl"
                            >
                                Completed Tasks
                            </h2>

                            <p
                                class="mt-2 max-w-xl text-sm leading-6 text-slate-500"
                            >
                                Tasks you have already completed are kept here.
                            </p>
                        </div>

                        <Link
                            href="/tasks"
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800"
                        >
                            ← Back to Tasks
                        </Link>
                    </div>
                </section>

                <!-- EMPTY -->
                <section
                    v-if="props.tasks.length === 0"
                    class="rounded-[2rem] bg-white p-10 text-center shadow-sm"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl"
                    >
                        ✓
                    </div>

                    <h3
                        class="mt-4 text-lg font-semibold text-slate-900"
                    >
                        No completed tasks yet
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500"
                    >
                        When you complete a task, it will appear here.
                    </p>

                    <Link
                        href="/tasks"
                        class="mt-5 inline-flex rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white"
                    >
                        View Active Tasks
                    </Link>
                </section>

                <!-- COMPLETED TASKS -->
                <section
                    v-else
                    class="rounded-[2rem] bg-white p-5 shadow-sm md:p-6"
                >
                    <div
                        class="mb-5 flex items-center justify-between gap-3"
                    >
                        <div>
                            <h3
                                class="text-lg font-bold text-slate-900"
                            >
                                Completed
                            </h3>

                            <p class="mt-1 text-xs text-slate-400">
                                Your finished tasks
                            </p>
                        </div>

                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500"
                        >
                            {{ props.tasks.length }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        <article
                            v-for="task in props.tasks"
                            :key="task.id"
                            class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                        >
                            <div
                                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                            >
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs text-white"
                                        >
                                            ✓
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="text-sm font-medium leading-6 text-slate-500 line-through"
                                            >
                                                {{ task.description }}
                                            </p>

                                            <div
                                                class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400"
                                            >
                                                <span>
                                                    Completed
                                                    {{
                                                        formatDate(
                                                            task.updated_at,
                                                        )
                                                    }}
                                                </span>

                                                <span v-if="task.due_at">
                                                    Due
                                                    {{
                                                        formatDate(
                                                            task.due_at,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        @click="reopenTask(task)"
                                        class="rounded-xl bg-white px-4 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-100"
                                    >
                                        ↶ Reopen
                                    </button>

                                    <button
                                        type="button"
                                        @click="deleteTask(task)"
                                        class="rounded-xl bg-white px-4 py-2 text-xs font-semibold text-red-600 ring-1 ring-slate-200 transition hover:bg-red-50"
                                    >
                                        Move to BIN
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>