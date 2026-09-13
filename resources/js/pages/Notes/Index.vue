<script setup lang="ts">
import { nextTick, onMounted, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

type Note = {
    id: number;
    title: string | null;
    description: string;
    status: string;
    due_at: string | null;
    created_at: string;
    updated_at: string;
};

const props = defineProps<{
    notes: Note[];
}>();

const noteTextarea = ref<HTMLTextAreaElement | null>(null);

const noteForm = useForm({
    type: 'note',
    description: '',
    due_at: '',
});

const editingNoteId = ref<number | null>(null);

const editForm = useForm({
    description: '',
    due_at: '',
});

const focusCreateNote = async () => {
    await nextTick();

    noteTextarea.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
    });

    noteTextarea.value?.focus();
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);

    if (params.get('create') === '1') {
        focusCreateNote();
    }
});

const addNote = () => {
    noteForm.post('/memories', {
        preserveScroll: true,

        onSuccess: () => {
            noteForm.reset('description', 'due_at');
        },
    });
};

const startEditing = (note: Note) => {
    editingNoteId.value = note.id;

    editForm.description = note.description;
    editForm.due_at = note.due_at
        ? note.due_at.substring(0, 10)
        : '';
};

const cancelEditing = () => {
    editingNoteId.value = null;

    editForm.reset();
    editForm.clearErrors();
};

const saveEdit = (note: Note) => {
    editForm.patch(`/memories/${note.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            editingNoteId.value = null;
            editForm.reset();
        },
    });
};

const deleteNote = (note: Note) => {
    const confirmed = window.confirm(
        'Move this note to BIN?',
    );

    if (!confirmed) {
        return;
    }

    router.delete(`/memories/${note.id}`, {
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
    <Head title="Notes - APONWORKS" />

    <div class="min-h-screen bg-[#f8f7f4] p-4 md:p-6">
        <div
            class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[220px_1fr]"
        >
            <!-- LEFT SIDEBAR -->
            <aside
                class="hidden rounded-3xl bg-white p-5 shadow-sm lg:block"
            >
                <div class="mb-8">
                    <Link href="/dashboard" class="block">
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
                                href="/tasks?create=1"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                ✅ Add Task
                            </Link>

                            <Link
                                href="/notes?create=1"
                                class="block rounded-xl bg-slate-900 px-3 py-2 text-white"
                            >
                                📝 Add Note
                            </Link>

                            <Link
                                href="/diary?create=1"
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
                                class="block rounded-xl bg-slate-100 px-3 py-2 font-semibold text-slate-900"
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
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
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

                    <p
                        class="mt-2 text-sm font-semibold text-slate-800"
                    >
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

            <!-- MAIN NOTES WORKSPACE -->
            <main class="min-w-0">
                <!-- HEADER -->
                <section
                    class="mb-6 overflow-hidden rounded-[2rem] bg-white shadow-sm"
                >
                    <div
                        class="flex flex-col gap-5 p-6 md:flex-row md:items-center md:justify-between md:p-8"
                    >
                        <div>
                            <div
                                class="mb-3 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-500"
                            >
                                Personal Notes
                            </div>

                            <h2
                                class="text-3xl font-bold tracking-tight text-slate-900 md:text-4xl"
                            >
                                Notes
                            </h2>

                            <p
                                class="mt-2 max-w-xl text-sm leading-6 text-slate-500"
                            >
                                Save ideas, information, references and anything
                                you want APONWORKS to remember.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-600"
                        >
                            {{ props.notes.length }} Notes
                        </div>
                    </div>
                </section>

                <!-- ADD NOTE -->
                <section
                    class="mb-6 rounded-[2rem] bg-white p-5 shadow-sm md:p-6"
                >
                    <form @submit.prevent="addNote">
                        <div
                            class="flex items-center justify-between gap-3"
                        >
                            <div>
                                <h3
                                    class="text-lg font-semibold text-slate-900"
                                >
                                    Add Note
                                </h3>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    What would you like APONWORKS to remember?
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500"
                            >
                                New
                            </span>
                        </div>

                        <textarea
                            ref="noteTextarea"
                            v-model="noteForm.description"
                            rows="5"
                            placeholder="Write your note here..."
                            class="mt-4 w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white"
                        ></textarea>

                        <p
                            v-if="noteForm.errors.description"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ noteForm.errors.description }}
                        </p>

                        <div
                            class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
                        >
                            <div>
                                <label
                                    class="mb-2 block text-xs font-medium text-slate-500"
                                >
                                    Date
                                    <span class="text-slate-400">
                                        (optional)
                                    </span>
                                </label>

                                <input
                                    v-model="noteForm.due_at"
                                    type="date"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-slate-400"
                                />
                            </div>

                            <div
                                class="flex flex-wrap items-center gap-2"
                            >
                                <button
                                    type="button"
                                    class="rounded-xl bg-slate-100 px-3 py-2.5 text-sm text-slate-600 transition hover:bg-slate-200"
                                >
                                    📎 File
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl bg-slate-100 px-3 py-2.5 text-sm text-slate-600 transition hover:bg-slate-200"
                                >
                                    📷 Photo
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl bg-slate-100 px-3 py-2.5 text-sm text-slate-600 transition hover:bg-slate-200"
                                >
                                    🎤 Voice
                                </button>

                                <button
                                    type="submit"
                                    :disabled="
                                        noteForm.processing ||
                                        !noteForm.description.trim()
                                    "
                                    class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        noteForm.processing
                                            ? 'Saving...'
                                            : '+ Add Note'
                                    }}
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- EMPTY STATE -->
                <section
                    v-if="props.notes.length === 0"
                    class="rounded-[2rem] bg-white p-10 text-center shadow-sm"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl"
                    >
                        📝
                    </div>

                    <h3
                        class="mt-4 text-lg font-semibold text-slate-900"
                    >
                        No notes yet
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500"
                    >
                        Add your first note above. APONWORKS will keep it ready
                        whenever you need it.
                    </p>
                </section>

                <!-- NOTES LIST -->
                <section
                    v-else
                    class="rounded-[2rem] bg-white p-5 shadow-sm md:p-6"
                >
                    <div
                        class="mb-5 flex items-center justify-between"
                    >
                        <div>
                            <h3
                                class="text-lg font-bold text-slate-900"
                            >
                                My Notes
                            </h3>

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                Latest notes appear first
                            </p>
                        </div>

                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500"
                        >
                            {{ props.notes.length }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="note in props.notes"
                            :key="note.id"
                            class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                        >
                            <template
                                v-if="editingNoteId !== note.id"
                            >
                                <div
                                    class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                                >
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="whitespace-pre-wrap text-sm font-medium leading-6 text-slate-800"
                                        >
                                            {{ note.description }}
                                        </p>

                                        <div
                                            class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400"
                                        >
                                            <span>
                                                Saved
                                                {{ formatDate(note.created_at) }}
                                            </span>

                                            <span v-if="note.due_at">
                                                Date
                                                {{ formatDate(note.due_at) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="flex flex-wrap gap-2"
                                    >
                                        <button
                                            type="button"
                                            @click="startEditing(note)"
                                            class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-slate-200"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            @click="deleteNote(note)"
                                            class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-red-600 ring-1 ring-slate-200"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template v-else>
                                <form
                                    @submit.prevent="saveEdit(note)"
                                >
                                    <textarea
                                        v-model="editForm.description"
                                        rows="5"
                                        class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none"
                                    ></textarea>

                                    <input
                                        v-model="editForm.due_at"
                                        type="date"
                                        class="mt-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm"
                                    />

                                    <div class="mt-3 flex gap-2">
                                        <button
                                            type="submit"
                                            class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white"
                                        >
                                            Save
                                        </button>

                                        <button
                                            type="button"
                                            @click="cancelEditing"
                                            class="rounded-xl bg-white px-4 py-2 text-xs font-semibold text-slate-600 ring-1 ring-slate-200"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </template>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>