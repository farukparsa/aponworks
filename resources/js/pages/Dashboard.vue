<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

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

const page = usePage();
const user = page.props.auth.user;

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
                            <!-- USER INFO -->
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

                            <!-- PROFILE / SETTINGS -->
                            <DropdownMenuItem :as-child="true">
                                <Link
                                    :href="edit()"
                                    class="w-full cursor-pointer rounded-xl px-3 py-2.5"
                                >
                                    <span class="mr-2">👤</span>
                                    Profile & Settings
                                </Link>
                            </DropdownMenuItem>

                            <!-- LANGUAGE -->
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

                                    <span
                                        class="text-xs text-slate-400"
                                    >
                                        English
                                    </span>
                                </div>
                            </DropdownMenuItem>

                            <DropdownMenuSeparator />

                            <!-- LOGOUT -->
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
                    <textarea
                        rows="5"
                        placeholder="Write your Diary Here or Add Task and Notes ..."
                        class="w-full resize-none border-0 bg-transparent text-base text-slate-800 outline-none placeholder:text-slate-400"
                    ></textarea>

                    <div
                        class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4"
                    >
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="rounded-xl bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200"
                            >
                                📝 Note
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
                            type="button"
                            class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
                        >
                            Save
                        </button>
                    </div>
                </section>

                <!-- SCHEDULE -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <!-- TODAY -->
                    <section
                        class="rounded-3xl bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <p
                                class="text-sm font-semibold text-slate-900"
                            >
                                Today
                            </p>

                            <span class="text-xs text-slate-400">
                                0 items
                            </span>
                        </div>

                        <p class="mt-3 text-sm text-slate-500">
                            Nothing scheduled yet.
                        </p>
                    </section>

                    <!-- TOMORROW -->
                    <section
                        class="rounded-3xl bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <p
                                class="text-sm font-semibold text-slate-900"
                            >
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
                            <p
                                class="text-sm font-semibold text-slate-900"
                            >
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
                            <p
                                class="text-sm font-semibold text-slate-900"
                            >
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

                        <p
                            class="text-sm font-semibold text-slate-900"
                        >
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
                    <p
                        class="text-sm font-semibold text-slate-900"
                    >
                        Recent Memories
                    </p>

                    <p class="mt-3 text-sm text-slate-500">
                        Your recent saved memories will appear here.
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