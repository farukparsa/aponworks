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
    occurred_at: string | null;
    expiry_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
};

const page = usePage();

const memory = computed<Memory>(() => {
    return page.props.memory as Memory;
});

const itemTitle = computed(() => {
    if (memory.value.type === 'task') {
        return 'Deleted Task';
    }

    if (memory.value.type === 'diary') {
        return 'Deleted Diary';
    }

    return 'Deleted Note';
});

const backLink = computed(() => {
    if (memory.value.type === 'task') {
        return '/memories?view=deleted-tasks';
    }

    if (memory.value.type === 'diary') {
        return '/memories?view=deleted-diary';
    }

    return '/memories?view=deleted-notes';
});

const restoreMemory = () => {
    router.patch(`/memories/${memory.value.id}/restore`);
};

const permanentlyDeleteMemory = () => {
    const confirmed = window.confirm(
        'Permanently delete this item? This cannot be undone.',
    );

    if (!confirmed) {
        return;
    }

    router.delete(`/memories/${memory.value.id}/permanent`);
};

const formatDate = (date: string | null) => {
    if (!date) {
        return '';
    }

    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head :title="itemTitle" />

    <div class="min-h-screen bg-slate-50 p-4 md:p-6">
        <div class="mx-auto max-w-5xl">
            <!-- HEADER -->
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4"
            >
                <div>
                    <Link
                        :href="backLink"
                        class="text-sm font-medium text-slate-500 hover:text-slate-900"
                    >
                        ← Back to BIN
                    </Link>

                    <h1 class="mt-3 text-2xl font-bold text-slate-900">
                        {{ itemTitle }}
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Review this deleted item before restoring or
                        permanently deleting it.
                    </p>
                </div>

                <!-- RESTORE ON RIGHT -->
                <button
                    type="button"
                    @click="restoreMemory"
                    class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                >
                    ↩ Restore
                </button>
            </div>

            <!-- DELETED ITEM -->
            <section class="rounded-3xl bg-white p-6 shadow-sm">
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-5"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-600"
                        >
                            {{ memory.type }}
                        </span>

                        <span
                            v-if="memory.status"
                            class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs text-slate-500"
                        >
                            {{ memory.status }}
                        </span>
                    </div>

                    <span
                        v-if="memory.deleted_at"
                        class="text-xs text-slate-400"
                    >
                        Deleted {{ formatDate(memory.deleted_at) }}
                    </span>
                </div>

                <div class="py-6">
                    <h2
                        v-if="memory.title"
                        class="text-xl font-semibold text-slate-900"
                    >
                        {{ memory.title }}
                    </h2>

                    <p
                        class="mt-3 whitespace-pre-wrap text-base leading-7 text-slate-700"
                    >
                        {{ memory.description }}
                    </p>
                </div>

                <!-- DETAILS -->
                <div
                    v-if="
                        memory.due_at ||
                        memory.occurred_at ||
                        memory.expiry_at
                    "
                    class="grid gap-3 border-t border-slate-100 py-5 sm:grid-cols-3"
                >
                    <div
                        v-if="memory.due_at"
                        class="rounded-2xl bg-slate-50 p-4"
                    >
                        <p class="text-xs font-medium text-slate-400">
                            Due Date
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ formatDate(memory.due_at) }}
                        </p>
                    </div>

                    <div
                        v-if="memory.occurred_at"
                        class="rounded-2xl bg-slate-50 p-4"
                    >
                        <p class="text-xs font-medium text-slate-400">
                            Date
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ formatDate(memory.occurred_at) }}
                        </p>
                    </div>

                    <div
                        v-if="memory.expiry_at"
                        class="rounded-2xl bg-slate-50 p-4"
                    >
                        <p class="text-xs font-medium text-slate-400">
                            Expiry
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ formatDate(memory.expiry_at) }}
                        </p>
                    </div>
                </div>

                <!-- PERMANENT DELETE -->
                <div
                    class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-5"
                >
                    <p class="max-w-xl text-xs leading-5 text-slate-400">
                        Permanent deletion cannot be undone. Use this only
                        when you are sure you no longer need this item.
                    </p>

                    <button
                        type="button"
                        @click="permanentlyDeleteMemory"
                        class="rounded-xl px-4 py-2.5 text-sm font-semibold text-red-600 ring-1 ring-red-200 hover:bg-red-50"
                    >
                        Delete Permanently
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>