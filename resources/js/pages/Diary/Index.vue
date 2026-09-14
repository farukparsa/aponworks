<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

type DiaryEntry = {
    id: number;
    title: string | null;
    description: string;
    status: string;
    due_at: string | null;
    created_at: string;
    updated_at: string;
};

type DiaryStyle =
    | 'classic'
    | 'minimal'
    | 'dark'
    | 'timeline'
    | 'calendar'
    | 'paper';

const props = defineProps<{
    entries: DiaryEntry[];
}>();

const diaryTextarea = ref<HTMLTextAreaElement | null>(null);

const diaryForm = useForm({
    type: 'diary',
    description: '',
    due_at: '',
});

const editingEntryId = ref<number | null>(null);

const editForm = useForm({
    description: '',
    due_at: '',
});

const selectedStyle = ref<DiaryStyle>('classic');

const styles: {
    id: DiaryStyle;
    name: string;
    icon: string;
    description: string;
}[] = [
    {
        id: 'classic',
        name: 'Classic Leather',
        icon: '📖',
        description: 'Warm personal diary',
    },
    {
        id: 'minimal',
        name: 'Minimal',
        icon: '✨',
        description: 'Clean modern writing',
    },
    {
        id: 'dark',
        name: 'Dark Diary',
        icon: '🌙',
        description: 'Comfortable night view',
    },
    {
        id: 'timeline',
        name: 'Timeline',
        icon: '🕒',
        description: 'Chronological journal',
    },
    {
        id: 'calendar',
        name: 'Calendar',
        icon: '📅',
        description: 'Date-focused view',
    },
    {
        id: 'paper',
        name: 'Notebook',
        icon: '📓',
        description: 'Lined paper feeling',
    },
];

const orderedEntries = computed(() => {
    return [...props.entries].sort((a, b) => {
        const dateA = a.due_at ?? a.created_at;
        const dateB = b.due_at ?? b.created_at;

        return new Date(dateB).getTime() - new Date(dateA).getTime();
    });
});

const entryCount = computed(() => props.entries.length);

const focusCreateDiary = async () => {
    await nextTick();

    diaryTextarea.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
    });

    diaryTextarea.value?.focus();
};

const selectStyle = (style: DiaryStyle) => {
    selectedStyle.value = style;

    localStorage.setItem('aponworks_diary_style', style);
};

onMounted(() => {
    const savedStyle = localStorage.getItem(
        'aponworks_diary_style',
    ) as DiaryStyle | null;

    if (
        savedStyle &&
        styles.some((style) => style.id === savedStyle)
    ) {
        selectedStyle.value = savedStyle;
    }

    const params = new URLSearchParams(window.location.search);

    if (params.get('create') === '1') {
        focusCreateDiary();
    }
});

const addEntry = () => {
    diaryForm.post('/memories', {
        preserveScroll: true,

        onSuccess: () => {
            diaryForm.reset('description', 'due_at');

            focusCreateDiary();
        },
    });
};

const startEditing = (entry: DiaryEntry) => {
    editingEntryId.value = entry.id;

    editForm.description = entry.description;

    editForm.due_at = entry.due_at
        ? entry.due_at.substring(0, 10)
        : '';
};

const cancelEditing = () => {
    editingEntryId.value = null;

    editForm.reset();
    editForm.clearErrors();
};

const saveEdit = (entry: DiaryEntry) => {
    editForm.patch(`/memories/${entry.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            editingEntryId.value = null;
            editForm.reset();
        },
    });
};

const deleteEntry = (entry: DiaryEntry) => {
    const confirmed = window.confirm(
        'Move this diary entry to BIN?',
    );

    if (!confirmed) {
        return;
    }

    router.delete(`/memories/${entry.id}`, {
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
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const entryDate = (entry: DiaryEntry) => {
    if (entry.due_at) {
        return formatDate(entry.due_at);
    }

    return new Date(entry.created_at).toLocaleDateString(
        undefined,
        {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        },
    );
};

const entryTime = (entry: DiaryEntry) => {
    return new Date(entry.created_at).toLocaleTimeString(
        undefined,
        {
            hour: 'numeric',
            minute: '2-digit',
        },
    );
};

const pageClasses = computed(() => {
    if (selectedStyle.value === 'dark') {
        return 'bg-slate-950';
    }

    if (selectedStyle.value === 'classic') {
        return 'bg-[#eee7db]';
    }

    if (selectedStyle.value === 'paper') {
        return 'bg-[#f2eee4]';
    }

    return 'bg-[#f8f7f4]';
});

const cardClasses = computed(() => {
    if (selectedStyle.value === 'dark') {
        return 'bg-slate-900 text-slate-100 border-slate-800';
    }

    if (selectedStyle.value === 'classic') {
        return 'bg-[#fffaf0] text-stone-800 border-[#e6d7bd]';
    }

    if (selectedStyle.value === 'paper') {
        return 'diary-paper bg-[#fffef9] text-slate-800 border-[#e9e3d7]';
    }

    return 'bg-white text-slate-800 border-slate-100';
});

const mutedTextClasses = computed(() => {
    return selectedStyle.value === 'dark'
        ? 'text-slate-400'
        : 'text-slate-500';
});

const headingClasses = computed(() => {
    return selectedStyle.value === 'dark'
        ? 'text-white'
        : 'text-slate-900';
});
</script>

<template>
    <Head title="Diary - APONWORKS" />

    <div
        :class="[
            'min-h-screen p-4 transition-colors duration-300 md:p-6',
            pageClasses,
        ]"
    >
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
                        <h1
                            class="text-2xl font-bold text-slate-900"
                        >
                            APONWORKS
                        </h1>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
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
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                📝 Add Note
                            </Link>

                            <Link
                                href="/diary?create=1"
                                class="block rounded-xl bg-slate-900 px-3 py-2 text-white"
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
                                class="block rounded-xl bg-slate-100 px-3 py-2 font-semibold text-slate-900"
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
                    <p
                        class="text-xs font-medium text-slate-500"
                    >
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

                    <p
                        class="mt-2 text-sm text-slate-500"
                    >
                        Future advertisement space
                    </p>
                </div>
            </aside>

            <!-- MAIN DIARY WORKSPACE -->
            <main class="min-w-0">
                <!-- HEADER -->
                <section
                    :class="[
                        'mb-6 overflow-hidden rounded-[2rem] border p-6 shadow-sm md:p-8',
                        cardClasses,
                    ]"
                >
                    <div
                        class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <div
                                class="mb-3 inline-flex rounded-full bg-slate-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide"
                                :class="mutedTextClasses"
                            >
                                Private Journal
                            </div>

                            <h2
                                :class="[
                                    'text-3xl font-bold tracking-tight md:text-4xl',
                                    headingClasses,
                                ]"
                            >
                                My Diary
                            </h2>

                            <p
                                :class="[
                                    'mt-2 max-w-2xl text-sm leading-6',
                                    mutedTextClasses,
                                ]"
                            >
                                A private place for your personal
                                thoughts, daily records and memories.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl bg-slate-500/10 px-4 py-3"
                        >
                            <p
                                class="text-xs"
                                :class="mutedTextClasses"
                            >
                                Diary Entries
                            </p>

                            <p
                                :class="[
                                    'mt-1 text-2xl font-bold',
                                    headingClasses,
                                ]"
                            >
                                {{ entryCount }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- VIEW / STYLE CHANGER -->
                <section
                    :class="[
                        'mb-6 rounded-[2rem] border p-5 shadow-sm md:p-6',
                        cardClasses,
                    ]"
                >
                    <div
                        class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <h3
                                :class="[
                                    'text-lg font-semibold',
                                    headingClasses,
                                ]"
                            >
                                Diary View
                            </h3>

                            <p
                                :class="[
                                    'mt-1 text-xs',
                                    mutedTextClasses,
                                ]"
                            >
                                Change how your diary looks. Your
                                entries stay the same.
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <button
                            v-for="style in styles"
                            :key="style.id"
                            type="button"
                            @click="selectStyle(style.id)"
                            :class="[
                                'rounded-2xl border p-3 text-left transition',
                                selectedStyle === style.id
                                    ? 'border-slate-700 bg-slate-900 text-white'
                                    : selectedStyle === 'dark'
                                      ? 'border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700'
                                      : 'border-slate-200 bg-white/70 text-slate-700 hover:bg-slate-50',
                            ]"
                        >
                            <div
                                class="flex items-start gap-3"
                            >
                                <span class="text-xl">
                                    {{ style.icon }}
                                </span>

                                <div>
                                    <p
                                        class="text-sm font-semibold"
                                    >
                                        {{ style.name }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs opacity-60"
                                    >
                                        {{ style.description }}
                                    </p>
                                </div>
                            </div>
                        </button>
                    </div>
                </section>

                <!-- WRITE DIARY -->
                <section
                    :class="[
                        'mb-6 rounded-[2rem] border p-5 shadow-sm md:p-6',
                        cardClasses,
                    ]"
                >
                    <form @submit.prevent="addEntry">
                        <div
                            class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                        >
                            <div>
                                <h3
                                    :class="[
                                        'text-lg font-semibold',
                                        headingClasses,
                                    ]"
                                >
                                    Write Diary
                                </h3>

                                <p
                                    :class="[
                                        'mt-1 text-xs',
                                        mutedTextClasses,
                                    ]"
                                >
                                    What would you like to remember
                                    about today?
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-500/10 px-3 py-1 text-xs font-medium"
                                :class="mutedTextClasses"
                            >
                                🔒 Private
                            </span>
                        </div>

                        <textarea
                            ref="diaryTextarea"
                            v-model="diaryForm.description"
                            rows="7"
                            placeholder="Write your diary here..."
                            :class="[
                                'mt-5 w-full resize-none rounded-2xl border px-4 py-4 text-sm leading-7 outline-none transition',
                                selectedStyle === 'dark'
                                    ? 'border-slate-700 bg-slate-800 text-slate-100 placeholder:text-slate-500 focus:border-slate-500'
                                    : 'border-slate-200 bg-white/80 text-slate-800 placeholder:text-slate-400 focus:border-slate-400',
                            ]"
                        ></textarea>

                        <p
                            v-if="diaryForm.errors.description"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ diaryForm.errors.description }}
                        </p>

                        <div
                            class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
                        >
                            <div>
                                <label
                                    class="mb-2 block text-xs font-medium"
                                    :class="mutedTextClasses"
                                >
                                    Diary Date
                                    <span class="opacity-60">
                                        (optional)
                                    </span>
                                </label>

                                <input
                                    v-model="diaryForm.due_at"
                                    type="date"
                                    :class="[
                                        'rounded-xl border px-4 py-2.5 text-sm outline-none',
                                        selectedStyle === 'dark'
                                            ? 'border-slate-700 bg-slate-800 text-slate-200'
                                            : 'border-slate-200 bg-white text-slate-700',
                                    ]"
                                />
                            </div>

                            <div
                                class="flex flex-wrap items-center gap-2"
                            >
                                <button
                                    type="button"
                                    class="rounded-xl bg-slate-500/10 px-3 py-2.5 text-sm"
                                    :class="mutedTextClasses"
                                >
                                    📎 File
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl bg-slate-500/10 px-3 py-2.5 text-sm"
                                    :class="mutedTextClasses"
                                >
                                    📷 Photo
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl bg-slate-500/10 px-3 py-2.5 text-sm"
                                    :class="mutedTextClasses"
                                >
                                    🎤 Voice
                                </button>

                                <button
                                    type="submit"
                                    :disabled="
                                        diaryForm.processing ||
                                        !diaryForm.description.trim()
                                    "
                                    class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        diaryForm.processing
                                            ? 'Saving...'
                                            : 'Save Diary'
                                    }}
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- EMPTY DIARY -->
                <section
                    v-if="!orderedEntries.length"
                    :class="[
                        'rounded-[2rem] border p-10 text-center shadow-sm',
                        cardClasses,
                    ]"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-500/10 text-3xl"
                    >
                        📖
                    </div>

                    <h3
                        :class="[
                            'mt-4 text-lg font-semibold',
                            headingClasses,
                        ]"
                    >
                        Your diary is ready
                    </h3>

                    <p
                        :class="[
                            'mx-auto mt-2 max-w-md text-sm leading-6',
                            mutedTextClasses,
                        ]"
                    >
                        Write your first diary entry above.
                        APONWORKS will keep your entries together
                        chronologically.
                    </p>
                </section>

                <!-- DIARY ENTRIES -->
                <section
                    v-else
                    :class="[
                        'rounded-[2rem] border p-5 shadow-sm md:p-6',
                        cardClasses,
                    ]"
                >
                    <div
                        class="mb-6 flex items-center justify-between"
                    >
                        <div>
                            <h3
                                :class="[
                                    'text-lg font-bold',
                                    headingClasses,
                                ]"
                            >
                                Diary Entries
                            </h3>

                            <p
                                :class="[
                                    'mt-1 text-xs',
                                    mutedTextClasses,
                                ]"
                            >
                                Your personal journal history
                            </p>
                        </div>

                        <span
                            class="rounded-full bg-slate-500/10 px-3 py-1 text-xs font-semibold"
                            :class="mutedTextClasses"
                        >
                            {{ entryCount }}
                        </span>
                    </div>

                    <div
                        :class="[
                            selectedStyle === 'timeline'
                                ? 'relative space-y-5 border-l-2 border-slate-300 pl-6'
                                : 'space-y-4',
                        ]"
                    >
                        <article
                            v-for="entry in orderedEntries"
                            :key="entry.id"
                            :class="[
                                'relative rounded-2xl border p-5 transition',
                                selectedStyle === 'dark'
                                    ? 'border-slate-700 bg-slate-800'
                                    : selectedStyle === 'classic'
                                      ? 'border-[#e3d2b3] bg-[#fffdf7]'
                                      : selectedStyle === 'paper'
                                        ? 'diary-paper border-slate-200 bg-[#fffef9]'
                                        : 'border-slate-100 bg-white/70',
                            ]"
                        >
                            <span
                                v-if="
                                    selectedStyle === 'timeline'
                                "
                                class="absolute -left-[33px] top-7 h-4 w-4 rounded-full border-4 border-white bg-slate-700"
                            ></span>

                            <template
                                v-if="
                                    editingEntryId !== entry.id
                                "
                            >
                                <div
                                    class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                                >
                                    <div
                                        class="min-w-0 flex-1"
                                    >
                                        <div
                                            class="mb-3 flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="rounded-full bg-slate-500/10 px-3 py-1 text-xs font-semibold"
                                                :class="
                                                    mutedTextClasses
                                                "
                                            >
                                                {{
                                                    entryDate(
                                                        entry,
                                                    )
                                                }}
                                            </span>

                                            <span
                                                class="text-xs"
                                                :class="
                                                    mutedTextClasses
                                                "
                                            >
                                                {{
                                                    entryTime(
                                                        entry,
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <p
                                            :class="[
                                                'whitespace-pre-wrap text-sm leading-7',
                                                selectedStyle ===
                                                'dark'
                                                    ? 'text-slate-200'
                                                    : 'text-slate-700',
                                            ]"
                                        >
                                            {{
                                                entry.description
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="flex shrink-0 gap-2"
                                    >
                                        <button
                                            type="button"
                                            @click="
                                                startEditing(
                                                    entry,
                                                )
                                            "
                                            class="rounded-xl bg-slate-500/10 px-3 py-2 text-xs font-semibold"
                                            :class="
                                                mutedTextClasses
                                            "
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            @click="
                                                deleteEntry(
                                                    entry,
                                                )
                                            "
                                            class="rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-600"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template v-else>
                                <form
                                    @submit.prevent="
                                        saveEdit(entry)
                                    "
                                >
                                    <textarea
                                        v-model="
                                            editForm.description
                                        "
                                        rows="6"
                                        :class="[
                                            'w-full resize-none rounded-xl border px-4 py-3 text-sm leading-7 outline-none',
                                            selectedStyle ===
                                            'dark'
                                                ? 'border-slate-700 bg-slate-900 text-slate-100'
                                                : 'border-slate-200 bg-white text-slate-800',
                                        ]"
                                    ></textarea>

                                    <input
                                        v-model="
                                            editForm.due_at
                                        "
                                        type="date"
                                        :class="[
                                            'mt-3 rounded-xl border px-4 py-2.5 text-sm',
                                            selectedStyle ===
                                            'dark'
                                                ? 'border-slate-700 bg-slate-900 text-slate-200'
                                                : 'border-slate-200 bg-white text-slate-700',
                                        ]"
                                    />

                                    <div
                                        class="mt-4 flex gap-2"
                                    >
                                        <button
                                            type="submit"
                                            class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white"
                                        >
                                            Save
                                        </button>

                                        <button
                                            type="button"
                                            @click="
                                                cancelEditing
                                            "
                                            class="rounded-xl bg-slate-500/10 px-4 py-2 text-xs font-semibold"
                                            :class="
                                                mutedTextClasses
                                            "
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </template>
                        </article>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>

<style scoped>
.diary-paper {
    background-image: repeating-linear-gradient(
        to bottom,
        transparent 0,
        transparent 31px,
        rgba(148, 163, 184, 0.18) 32px
    );
}
</style>