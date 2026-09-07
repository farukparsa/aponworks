<script setup lang="ts">
import { computed } from 'vue';
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

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'APONWORKS',
                href: '/dashboard',
            },
        ],
    },
});

type RecentMemory = {
    id: number;
    type: string;
    title: string | null;
    description: string;
    created_at: string;
};

type ScheduledMemory = {
    id: number;
    type: string;
    title: string | null;
    description: string;
    due_at: string;
};

const page = usePage();

const user = page.props.auth.user;

const recentMemories = computed<RecentMemory[]>(() => {
    return (page.props.recentMemories as RecentMemory[] | undefined) ?? [];
});

const todayMemories = computed<ScheduledMemory[]>(() => {
    return (page.props.todayMemories as ScheduledMemory[] | undefined) ?? [];
});

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
};

const saveMemory = () => {
    memoryForm.post('/memories', {
        preserveScroll: true,
        onSuccess: () => {
            memoryForm.reset('description', 'due_at');
        },
    });
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

                <nav class="space-y-2 text-sm">
                    <a
                        href="#"
                        class="block rounded-xl bg-slate-100 px-4 py-3 font-medium text-slate-900"
                    >
                        Home
                    </a>

                    <a
                        href="#"
                        class="block rounded-xl px-4 py-3 text-slate-600 hover:bg-slate-100"
                    >
                        Memories
                    </a>

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

            <!-- MAIN CONTENT -->
            <main class="min-w-0">
                <!-- TOP BAR -->
                <div class="mb-6 flex items-center gap-3">
                    <!-- ASK APON -->
                    <div class="relative flex-1">
                        <input
                            type="text"
                            placeholder="Ask APON anything..."
                            class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 pr-12 text-sm shadow-sm outline-none transition focus:border-slate-400"
                        />

                        <button
                            type="button"
                            class="absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100"
                            title="Ask APON"
                        >
                            ✨
                        </button>
                    </div>

                    <!-- NOTIFICATIONS -->
                    <button
                        type="button"
                        class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-lg shadow-sm hover:bg-slate-100"
                        title="Notifications"
                    >
                        🔔

                        <span
                            class="absolute right-2 top-2 h-2 w-2 rounded-full bg-slate-900"
                        ></span>
                    </button>

                    <!-- USER MENU -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <button
                                type="button"
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-sm font-bold text-white shadow-sm hover:bg-slate-800"
                                title="Account"
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
                                    <span class="mr-2">👤</span>
                                    Profile & Settings
                                </Link>
                            </DropdownMenuItem>

                            <DropdownMenuItem
                                class="cursor-pointer rounded-xl px-3 py-2.5"
                            >
                                <div
                                    class="flex w-full items-center justify-between"
                                >
                                    <div>
                                        <span class="mr-2">🌐</span>
                                        Language
                                    </div>

                                    <span class="text-xs text-slate-400">
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
                                    <span class="mr-2">↪</span>
                                    Log out
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <!-- GREETING -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-900">
                        {{ greeting }}, {{ user?.name || 'there' }}!
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        What would you like APON to remember today?
                    </p>
                </div>

                <!-- DIARY / TASK / NOTE -->
                <section
                    class="mb-6 rounded-3xl bg-white p-5 shadow-sm"
                >
                    <form @submit.prevent="saveMemory">
                        <textarea
                            v-model="memoryForm.description"
                            rows="5"
                            placeholder="Write your Diary Here or Add Task and Notes ..."
                            class="w-full resize-none border-0 bg-transparent text-base text-slate-800 outline-none placeholder:text-slate-400"
                        ></textarea>

                        <p
                            v-if="memoryForm.errors.description"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ memoryForm.errors.description }}
                        </p>

                        <!-- DUE DATE -->
                        <div class="mt-4">
                            <label
                                for="memory-due-date"
                                class="mb-2 block text-xs font-medium text-slate-500"
                            >
                                Due Date
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

                            <p
                                v-if="memoryForm.errors.due_at"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ memoryForm.errors.due_at }}
                            </p>
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
                                    @click="selectMemoryType('note')"
                                    :class="[
                                        'rounded-xl px-3 py-2 text-sm transition',
                                        memoryForm.type === 'note'
                                            ? 'bg-slate-900 text-white'
                                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                                    ]"
                                >
                                    📝 Note
                                </button>

                                <button
                                    type="button"
                                    @click="selectMemoryType('task')"
                                    :class="[
                                        'rounded-xl px-3 py-2 text-sm transition',
                                        memoryForm.type === 'task'
                                            ? 'bg-slate-900 text-white'
                                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                                    ]"
                                >
                                    ✅ Task
                                </button>

                                <button
                                    type="button"
                                    @click="selectMemoryType('diary')"
                                    :class="[
                                        'rounded-xl px-3 py-2 text-sm transition',
                                        memoryForm.type === 'diary'
                                            ? 'bg-slate-900 text-white'
                                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                                    ]"
                                >
                                    📖 Diary
                                </button>

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
                                {{
                                    memoryForm.processing
                                        ? 'Saving...'
                                        : 'Save'
                                }}
                            </button>
                        </div>
                    </form>
                </section>

                <!-- SCHEDULE -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <!-- TODAY -->
                    <section
                        class="rounded-3xl bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                Today
                            </p>

                            <span class="text-xs text-slate-400">
                                {{ todayMemories.length }}
                                {{
                                    todayMemories.length === 1
                                        ? 'item'
                                        : 'items'
                                }}
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
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        {{ memory.type }}
                                    </span>
                                </div>

                                <p
                                    class="mt-2 text-sm leading-5 text-slate-700"
                                >
                                    {{ memory.description }}
                                </p>
                            </div>
                        </div>

                        <p
                            v-else
                            class="mt-3 text-sm text-slate-500"
                        >
                            Nothing scheduled yet.
                        </p>
                    </section>

                    <!-- TOMORROW -->
                    <section
                        class="rounded-3xl bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                Tomorrow
                            </p>

                            <span class="text-xs text-slate-400">
                                0 items
                            </span>
                        </div>

                        <p class="mt-3 text-sm text-slate-500">
                            Nothing scheduled yet.
                        </p>
                    </section>

                    <!-- DAY AFTER TOMORROW -->
                    <section
                        class="rounded-3xl bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                Day After Tomorrow
                            </p>

                            <span class="text-xs text-slate-400">
                                0 items
                            </span>
                        </div>

                        <p class="mt-3 text-sm text-slate-500">
                            Nothing scheduled yet.
                        </p>
                    </section>

                    <!-- UPCOMING -->
                    <section
                        class="rounded-3xl bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">
                                Upcoming
                            </p>

                            <span class="text-xs text-slate-400">
                                0 items
                            </span>
                        </div>

                        <p class="mt-3 text-sm text-slate-500">
                            No upcoming items.
                        </p>
                    </section>
                </div>
            </main>

            <!-- RIGHT SIDEBAR -->
            <aside class="hidden space-y-6 lg:block">
                <!-- APON INSIGHTS -->
                <section
                    class="rounded-3xl bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center gap-2">
                        <span>✨</span>

                        <p class="text-sm font-semibold text-slate-900">
                            Anything I should know?
                        </p>
                    </div>

                    <p
                        class="mt-3 text-sm leading-6 text-slate-500"
                    >
                        APON will show important reminders, open loops
                        and useful insights here.
                    </p>
                </section>

                <!-- RECENT MEMORIES -->
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

                <!-- FUTURE AD -->
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