<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import { logout } from '@/routes';
import { edit } from '@/routes/profile';

type RecentMemory = {
    id: number;
    type: string;
    title: string | null;
    description: string;
    status?: string;
    created_at: string;
};

type ScheduledMemory = {
    id: number;
    type: string;
    title: string | null;
    description: string;
    status?: string;
    due_at: string | null;
    expiry_at?: string | null;
};

const page = usePage();

const user = page.props.auth.user;

const recentMemories = computed<RecentMemory[]>(() => {
    return (page.props.recentMemories as RecentMemory[] | undefined) ?? [];
});

const todayMemories = computed<ScheduledMemory[]>(() => {
    return (page.props.todayMemories as ScheduledMemory[] | undefined) ?? [];
});

const tomorrowMemories = computed<ScheduledMemory[]>(() => {
    return (page.props.tomorrowMemories as ScheduledMemory[] | undefined) ?? [];
});

const dayAfterTomorrowMemories = computed<ScheduledMemory[]>(() => {
    return (
        (page.props.dayAfterTomorrowMemories as
            | ScheduledMemory[]
            | undefined) ?? []
    );
});

const upcomingMemories = computed<ScheduledMemory[]>(() => {
    return (page.props.upcomingMemories as ScheduledMemory[] | undefined) ?? [];
});

const customScheduleMemories = computed<ScheduledMemory[]>(() => {
    return (
        (page.props.customScheduleMemories as
            | ScheduledMemory[]
            | undefined) ?? []
    );
});

const initialCustomDays = Number(page.props.customDays ?? 7);
const initialCustomType = String(page.props.customType ?? 'all');

const customDays = ref(
    Number.isFinite(initialCustomDays) && initialCustomDays > 0
        ? initialCustomDays
        : 7,
);

const customType = ref(initialCustomType);

const greeting = computed(() => {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Good morning';
    }

    if (hour < 18) {
        return 'Good afternoon';
    }

    return 'Good evening';
});

const handleLogout = () => {
    router.flushAll();
};

const memoryForm = useForm({
    type: 'note',
    description: '',
    due_at: '',
});

const selectMemoryType = (type: 'note' | 'task' | 'diary') => {
    memoryForm.type = type;

    window.setTimeout(() => {
        document
            .getElementById('memory-composer')
            ?.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });

        document.getElementById('memory-description')?.focus();
    }, 50);
};

const composerTitle = computed(() => {
    if (memoryForm.type === 'task') {
        return 'Add Task';
    }

    if (memoryForm.type === 'diary') {
        return 'Write Diary';
    }

    return 'Add Note';
});

const composerPlaceholder = computed(() => {
    if (memoryForm.type === 'task') {
        return 'What do you need to do?';
    }

    if (memoryForm.type === 'diary') {
        return 'Write your diary here...';
    }

    return 'Write your note here...';
});

const saveButtonText = computed(() => {
    if (memoryForm.processing) {
        return 'Saving...';
    }

    if (memoryForm.type === 'task') {
        return 'Save Task';
    }

    if (memoryForm.type === 'diary') {
        return 'Save Diary';
    }

    return 'Save Note';
});

const saveMemory = () => {
    memoryForm.post('/memories', {
        preserveScroll: true,

        onSuccess: () => {
            memoryForm.reset('description', 'due_at');
        },
    });
};

const completeMemory = (memoryId: number) => {
    router.patch(
        `/memories/${memoryId}/complete`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const applyCustomSchedule = () => {
    let days = Number(customDays.value);

    if (!Number.isFinite(days) || days < 1) {
        days = 1;
    }

    days = Math.floor(days);

    customDays.value = days;

    router.get(
        '/dashboard',
        {
            days,
            type: customType.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const formatDate = (date: string | null | undefined) => {
    if (!date) {
        return '';
    }

    return new Date(date).toLocaleDateString();
};

const customMemoryDate = (memory: ScheduledMemory) => {
    if (customType.value === 'expiry') {
        return formatDate(memory.expiry_at);
    }

    return formatDate(memory.due_at);
};

const customMemoryLabel = (memory: ScheduledMemory) => {
    if (customType.value === 'expiry') {
        return 'expiry';
    }

    return memory.type;
};
</script>

<template>
    <Head title="APONWORKS" />

    <div class="min-h-screen bg-slate-50 p-4 md:p-6">
        <div
            class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[220px_1fr_300px]"
        >
            <!-- LEFT SIDEBAR -->
            <aside
                class="hidden rounded-3xl bg-white p-5 shadow-sm lg:block"
            >
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-slate-900">
                        APONWORKS
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Your Personal AI Secretary
                    </p>
                </div>

                <nav class="space-y-3 text-sm">
                    <!-- HOME -->
                    <Link
                        href="/dashboard"
                        class="block rounded-xl bg-slate-100 px-4 py-3 font-medium text-slate-900"
                    >
                        Home
                    </Link>

                    <!-- CREATE -->
                    <div class="rounded-2xl border border-slate-100 p-2">
                        <div
                            class="rounded-xl px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Create
                        </div>

                        <div class="mt-1 space-y-1">
                            <button
                                type="button"
                                @click="selectMemoryType('task')"
                                class="block w-full rounded-xl px-3 py-2 text-left transition"
                                :class="
                                    memoryForm.type === 'task'
                                        ? 'bg-slate-900 font-medium text-white'
                                        : 'text-slate-600 hover:bg-slate-100'
                                "
                            >
                                ✅ Add Task
                            </button>

                            <button
                                type="button"
                                @click="selectMemoryType('note')"
                                class="block w-full rounded-xl px-3 py-2 text-left transition"
                                :class="
                                    memoryForm.type === 'note'
                                        ? 'bg-slate-900 font-medium text-white'
                                        : 'text-slate-600 hover:bg-slate-100'
                                "
                            >
                                📝 Add Note
                            </button>

                            <button
                                type="button"
                                @click="selectMemoryType('diary')"
                                class="block w-full rounded-xl px-3 py-2 text-left transition"
                                :class="
                                    memoryForm.type === 'diary'
                                        ? 'bg-slate-900 font-medium text-white'
                                        : 'text-slate-600 hover:bg-slate-100'
                                "
                            >
                                📖 Write Diary
                            </button>
                        </div>
                    </div>

                    <!-- VIEW -->
                    <div class="rounded-2xl border border-slate-100 p-2">
                        <div
                            class="rounded-xl px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            View
                        </div>

                        <div class="mt-1 space-y-1">
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
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                ✓ Completed Tasks
                            </Link>
                        </div>
                    </div>

                    <!-- BIN -->
                    <div class="rounded-2xl border border-slate-100 p-2">
                        <div
                            class="rounded-xl px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            🗑 BIN
                        </div>

                        <div class="mt-1 space-y-1">
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

                <!-- STORAGE -->
                <div class="mt-10 rounded-2xl bg-slate-100 p-4">
                    <p class="text-xs font-medium text-slate-500">
                        Storage Used
                    </p>

                    <p class="mt-2 text-sm font-semibold text-slate-800">
                        0 MB
                    </p>

                    <div class="mt-3 h-2 rounded-full bg-slate-200">
                        <div
                            class="h-2 w-[8%] rounded-full bg-slate-700"
                        ></div>
                    </div>
                </div>

                <!-- FUTURE AD -->
                <div
                    class="mt-4 rounded-2xl border border-dashed border-slate-200 p-4"
                >
                    <p class="text-xs text-slate-400">
                        Sponsored
                    </p>

                    <p class="mt-2 text-sm text-slate-600">
                        Future advertisement space
                    </p>
                </div>
            </aside>

            <!-- MAIN -->
            <main class="min-w-0">
                <!-- TOP BAR -->
                <div class="mb-6 flex items-center gap-3">
                    <div class="relative flex-1">
                        <input
                            type="text"
                            placeholder="Ask APON anything..."
                            class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 pr-12 text-sm shadow-sm outline-none transition focus:border-slate-400"
                        />

                        <button
                            type="button"
                            class="absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100"
                        >
                            ✨
                        </button>
                    </div>

                    <button
                        type="button"
                        class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-lg shadow-sm hover:bg-slate-100"
                    >
                        🔔
                        <span
                            class="absolute right-2 top-2 h-2 w-2 rounded-full bg-slate-900"
                        ></span>
                    </button>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <button
                                type="button"
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-sm font-bold text-white shadow-sm hover:bg-slate-800"
                            >
                                {{
                                    user?.name
                                        ? user.name.charAt(0).toUpperCase()
                                        : 'U'
                                }}
                            </button>
                        </DropdownMenuTrigger>

                        <DropdownMenuContent
                            align="end"
                            class="w-64 rounded-2xl p-2"
                        >
                            <DropdownMenuLabel class="p-3 font-normal">
                                <p
                                    class="truncate text-sm font-semibold text-slate-900"
                                >
                                    {{ user?.name }}
                                </p>

                                <p
                                    class="mt-1 truncate text-xs text-slate-500"
                                >
                                    {{ user?.email }}
                                </p>
                            </DropdownMenuLabel>

                            <DropdownMenuSeparator />

                            <DropdownMenuItem :as-child="true">
                                <Link
                                    :href="edit()"
                                    class="w-full cursor-pointer rounded-xl px-3 py-2.5"
                                >
                                    👤 Profile & Settings
                                </Link>
                            </DropdownMenuItem>

                            <DropdownMenuItem
                                class="cursor-pointer rounded-xl px-3 py-2.5"
                            >
                                <div
                                    class="flex w-full items-center justify-between"
                                >
                                    <div>
                                        🌐 Language
                                    </div>

                                    <span
                                        class="text-xs text-slate-400"
                                    >
                                        English
                                    </span>
                                </div>
                            </DropdownMenuItem>

                            <DropdownMenuSeparator />

                            <DropdownMenuItem :as-child="true">
                                <Link
                                    :href="logout()"
                                    as="button"
                                    data-test="logout-button"
                                    @click="handleLogout"
                                    class="w-full cursor-pointer rounded-xl px-3 py-2.5 text-left text-red-600"
                                >
                                    ↪ Log out
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <!-- GREETING -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-900">
                        {{ greeting }},
                        {{ user?.name || 'there' }}!
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        What would you like APON to remember today?
                    </p>
                </div>

                <!-- COMPOSER -->
                <section
                    id="memory-composer"
                    class="mb-6 rounded-3xl bg-white p-5 shadow-sm"
                >
                    <form @submit.prevent="saveMemory">
                        <div
                            class="mb-4 flex flex-wrap items-center justify-between gap-3"
                        >
                            <div>
                                <h3
                                    class="text-lg font-semibold text-slate-900"
                                >
                                    {{ composerTitle }}
                                </h3>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    Add text, file, photo or voice to this
                                    {{ memoryForm.type }}.
                                </p>
                            </div>

                            <span
                                class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold uppercase text-slate-500"
                            >
                                {{ memoryForm.type }}
                            </span>
                        </div>

                        <textarea
                            id="memory-description"
                            v-model="memoryForm.description"
                            rows="5"
                            :placeholder="composerPlaceholder"
                            class="w-full resize-none border-0 bg-transparent text-base text-slate-800 outline-none placeholder:text-slate-400"
                        ></textarea>

                        <p
                            v-if="memoryForm.errors.description"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ memoryForm.errors.description }}
                        </p>

                        <div class="mt-4">
                            <label
                                for="memory-due-date"
                                class="mb-2 block text-xs font-medium text-slate-500"
                            >
                                {{
                                    memoryForm.type === 'task'
                                        ? 'Due Date'
                                        : 'Date'
                                }}
                                <span class="font-normal text-slate-400">
                                    (optional)
                                </span>
                            </label>

                            <input
                                id="memory-due-date"
                                v-model="memoryForm.due_at"
                                type="date"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-slate-400 sm:w-auto"
                            />
                        </div>

                        <p
                            v-if="memoryForm.recentlySuccessful"
                            class="mt-3 text-sm font-medium text-green-700"
                        >
                            ✓ Saved to APONWORKS
                        </p>

                        <div
                            class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4"
                        >
                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="rounded-xl bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200"
                                >
                                    📎 File
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200"
                                >
                                    📷 Photo
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200"
                                >
                                    🎤 Voice
                                </button>
                            </div>

                            <button
                                type="submit"
                                :disabled="
                                    memoryForm.processing ||
                                    !memoryForm.description.trim()
                                "
                                class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ saveButtonText }}
                            </button>
                        </div>
                    </form>
                </section>

                <!-- SCHEDULE -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <section
                        class="rounded-3xl bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                Today
                            </p>

                            <span class="text-xs text-slate-400">
                                {{ todayMemories.length }} items
                            </span>
                        </div>

                        <div
                            v-if="todayMemories.length"
                            class="mt-4 space-y-3"
                        >
                            <div
                                v-for="memory in todayMemories"
                                :key="memory.id"
                                class="rounded-2xl bg-slate-50 p-3"
                            >
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                >
                                    {{ memory.type }}
                                </span>

                                <p
                                    class="mt-2 text-sm leading-5 text-slate-700"
                                >
                                    {{ memory.description }}
                                </p>

                                <button
                                    v-if="memory.type === 'task'"
                                    type="button"
                                    @click="completeMemory(memory.id)"
                                    class="mt-3 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-100"
                                >
                                    ✓ Complete
                                </button>
                            </div>
                        </div>

                        <p
                            v-else
                            class="mt-3 text-sm text-slate-500"
                        >
                            Nothing scheduled yet.
                        </p>
                    </section>

                    <section
                        class="rounded-3xl bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                Tomorrow
                            </p>

                            <span class="text-xs text-slate-400">
                                {{ tomorrowMemories.length }} items
                            </span>
                        </div>

                        <div
                            v-if="tomorrowMemories.length"
                            class="mt-4 space-y-3"
                        >
                            <div
                                v-for="memory in tomorrowMemories"
                                :key="memory.id"
                                class="rounded-2xl bg-slate-50 p-3"
                            >
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                >
                                    {{ memory.type }}
                                </span>

                                <p
                                    class="mt-2 text-sm leading-5 text-slate-700"
                                >
                                    {{ memory.description }}
                                </p>

                                <button
                                    v-if="memory.type === 'task'"
                                    type="button"
                                    @click="completeMemory(memory.id)"
                                    class="mt-3 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-100"
                                >
                                    ✓ Complete
                                </button>
                            </div>
                        </div>

                        <p
                            v-else
                            class="mt-3 text-sm text-slate-500"
                        >
                            Nothing scheduled yet.
                        </p>
                    </section>

                    <section
                        class="rounded-3xl bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                Day After Tomorrow
                            </p>

                            <span class="text-xs text-slate-400">
                                {{ dayAfterTomorrowMemories.length }} items
                            </span>
                        </div>

                        <div
                            v-if="dayAfterTomorrowMemories.length"
                            class="mt-4 space-y-3"
                        >
                            <div
                                v-for="memory in dayAfterTomorrowMemories"
                                :key="memory.id"
                                class="rounded-2xl bg-slate-50 p-3"
                            >
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                >
                                    {{ memory.type }}
                                </span>

                                <p
                                    class="mt-2 text-sm leading-5 text-slate-700"
                                >
                                    {{ memory.description }}
                                </p>

                                <button
                                    v-if="memory.type === 'task'"
                                    type="button"
                                    @click="completeMemory(memory.id)"
                                    class="mt-3 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-100"
                                >
                                    ✓ Complete
                                </button>
                            </div>
                        </div>

                        <p
                            v-else
                            class="mt-3 text-sm text-slate-500"
                        >
                            Nothing scheduled yet.
                        </p>
                    </section>

                    <section
                        class="rounded-3xl bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                Upcoming
                            </p>

                            <span class="text-xs text-slate-400">
                                {{ upcomingMemories.length }} items
                            </span>
                        </div>

                        <div
                            v-if="upcomingMemories.length"
                            class="mt-4 space-y-3"
                        >
                            <div
                                v-for="memory in upcomingMemories"
                                :key="memory.id"
                                class="rounded-2xl bg-slate-50 p-3"
                            >
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <span
                                        class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        {{ memory.type }}
                                    </span>

                                    <span
                                        class="text-xs text-slate-400"
                                    >
                                        {{ formatDate(memory.due_at) }}
                                    </span>
                                </div>

                                <p
                                    class="mt-2 text-sm leading-5 text-slate-700"
                                >
                                    {{ memory.description }}
                                </p>

                                <button
                                    v-if="memory.type === 'task'"
                                    type="button"
                                    @click="completeMemory(memory.id)"
                                    class="mt-3 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-100"
                                >
                                    ✓ Complete
                                </button>
                            </div>
                        </div>

                        <p
                            v-else
                            class="mt-3 text-sm text-slate-500"
                        >
                            No upcoming items.
                        </p>
                    </section>
                </div>

                <!-- CUSTOM SCHEDULE -->
                <section
                    class="mt-6 rounded-3xl bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h3
                                class="text-base font-semibold text-slate-900"
                            >
                                Custom Schedule
                            </h3>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Show anything coming in the next number of days.
                            </p>
                        </div>

                        <span class="text-xs text-slate-400">
                            {{ customScheduleMemories.length }} items
                        </span>
                    </div>

                    <form
                        class="mt-5 flex flex-col gap-3 rounded-2xl bg-slate-50 p-4 sm:flex-row sm:items-end"
                        @submit.prevent="applyCustomSchedule"
                    >
                        <div class="flex-1">
                            <label
                                for="custom-days"
                                class="mb-2 block text-xs font-medium text-slate-500"
                            >
                                Next number of days
                            </label>

                            <input
                                id="custom-days"
                                v-model.number="customDays"
                                type="number"
                                min="1"
                                step="1"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-slate-400"
                            />
                        </div>

                        <div class="flex-1">
                            <label
                                for="custom-type"
                                class="mb-2 block text-xs font-medium text-slate-500"
                            >
                                Show
                            </label>

                            <select
                                id="custom-type"
                                v-model="customType"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-slate-400"
                            >
                                <option value="all">
                                    All
                                </option>

                                <option value="task">
                                    Tasks only
                                </option>

                                <option value="note">
                                    Notes only
                                </option>

                                <option value="diary">
                                    Diary only
                                </option>

                                <option value="expiry">
                                    Expiry only
                                </option>
                            </select>
                        </div>

                        <button
                            type="submit"
                            class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
                        >
                            Apply
                        </button>
                    </form>

                    <div
                        v-if="customScheduleMemories.length"
                        class="mt-5 space-y-3"
                    >
                        <div
                            v-for="memory in customScheduleMemories"
                            :key="`${customMemoryLabel(memory)}-${memory.id}`"
                            class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-3"
                            >
                                <span
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    {{ customMemoryLabel(memory) }}
                                </span>

                                <span
                                    class="rounded-lg bg-white px-2.5 py-1 text-xs text-slate-500"
                                >
                                    {{ customMemoryDate(memory) }}
                                </span>
                            </div>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-700"
                            >
                                {{ memory.description }}
                            </p>

                            <button
                                v-if="memory.type === 'task'"
                                type="button"
                                @click="completeMemory(memory.id)"
                                class="mt-3 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-100"
                            >
                                ✓ Complete
                            </button>
                        </div>
                    </div>

                    <div
                        v-else
                        class="mt-5 rounded-2xl bg-slate-50 p-5 text-center"
                    >
                        <p class="text-sm text-slate-500">
                            No matching items found.
                        </p>
                    </div>
                </section>
            </main>

            <!-- RIGHT SIDEBAR -->
            <aside class="hidden space-y-6 lg:block">
                <section
                    class="rounded-3xl bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center gap-2">
                        <span>✨</span>

                        <p
                            class="text-sm font-semibold text-slate-900"
                        >
                            Anything I should know?
                        </p>
                    </div>

                    <p
                        class="mt-3 text-sm leading-6 text-slate-500"
                    >
                        APON will show important reminders, open loops and useful
                        insights here.
                    </p>
                </section>

                <section
                    class="rounded-3xl bg-white p-5 shadow-sm"
                >
                    <p class="text-sm font-semibold text-slate-900">
                        Recent Memories
                    </p>

                    <div
                        v-if="recentMemories.length"
                        class="mt-4 space-y-3"
                    >
                        <div
                            v-for="memory in recentMemories"
                            :key="memory.id"
                            class="rounded-2xl bg-slate-50 p-3"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <span
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                >
                                    {{ memory.type }}
                                </span>

                                <span class="text-xs text-slate-400">
                                    {{
                                        new Date(
                                            memory.created_at,
                                        ).toLocaleDateString()
                                    }}
                                </span>
                            </div>

                            <p
                                class="mt-2 line-clamp-3 text-sm leading-5 text-slate-700"
                            >
                                {{ memory.description }}
                            </p>
                        </div>
                    </div>

                    <p
                        v-else
                        class="mt-3 text-sm text-slate-500"
                    >
                        No memories saved yet.
                    </p>
                </section>

                <section
                    class="rounded-3xl border border-dashed border-slate-200 bg-white p-5"
                >
                    <p class="text-xs text-slate-400">
                        Sponsored
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        Future relevant offer space
                    </p>
                </section>
            </aside>
        </div>
    </div>
</template>