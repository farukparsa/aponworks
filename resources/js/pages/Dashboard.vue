<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

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
</script>

<template>
    <Head title="APONWORKS" />

    <div class="min-h-screen bg-slate-50 p-4 md:p-6">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[220px_1fr_300px]">
            <!-- Left Sidebar -->
            <aside class="hidden rounded-3xl bg-white p-5 shadow-sm lg:block">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-slate-900">APONWORKS</h1>
                    <p class="mt-1 text-sm text-slate-500">Your Personal AI Secretary</p>
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

                <div class="mt-10 rounded-2xl bg-slate-100 p-4">
                    <p class="text-xs font-medium text-slate-500">Storage Used</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800">0 MB</p>
                    <div class="mt-3 h-2 rounded-full bg-slate-200">
                        <div class="h-2 w-[8%] rounded-full bg-slate-700"></div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-dashed border-slate-200 p-4">
                    <p class="text-xs text-slate-400">Sponsored</p>
                    <p class="mt-2 text-sm text-slate-600">
                        Future advertisement space
                    </p>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="min-w-0">
                <div class="mb-6">
                    <input
                        type="text"
                        placeholder="Ask APON anything..."
                        class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm shadow-sm outline-none transition focus:border-slate-400"
                    />
                </div>

                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-900">
                        Good morning, John!
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        What would you like APON to remember today?
                    </p>
                </div>

                <section class="mb-6 rounded-3xl bg-white p-5 shadow-sm">
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
                                class="rounded-xl bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200"
                            >
                                Note
                            </button>
                            <button
                                class="rounded-xl bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200"
                            >
                                File
                            </button>
                            <button
                                class="rounded-xl bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200"
                            >
                                Photo
                            </button>
                            <button
                                class="rounded-xl bg-slate-100 px-3 py-2 text-sm text-slate-600 hover:bg-slate-200"
                            >
                                Voice
                            </button>
                        </div>

                        <button
                            class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
                        >
                            Save
                        </button>
                    </div>
                </section>

                <div class="grid gap-4 sm:grid-cols-2">
                    <section class="rounded-3xl bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">Today</p>
                        <p class="mt-2 text-sm text-slate-500">
                            Nothing scheduled yet.
                        </p>
                    </section>

                    <section class="rounded-3xl bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">Tomorrow</p>
                        <p class="mt-2 text-sm text-slate-500">
                            Nothing scheduled yet.
                        </p>
                    </section>

                    <section class="rounded-3xl bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">
                            Day After Tomorrow
                        </p>
                        <p class="mt-2 text-sm text-slate-500">
                            Nothing scheduled yet.
                        </p>
                    </section>

                    <section class="rounded-3xl bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">Upcoming</p>
                        <p class="mt-2 text-sm text-slate-500">
                            No upcoming items.
                        </p>
                    </section>
                </div>
            </main>

            <!-- Right Sidebar -->
            <aside class="hidden space-y-6 lg:block">
                <section class="rounded-3xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">
                        Anything I should know?
                    </p>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        APON will show important reminders, open loops and useful
                        insights here.
                    </p>
                </section>

                <section class="rounded-3xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">
                        Recent Memories
                    </p>
                    <p class="mt-3 text-sm text-slate-500">
                        Your recent saved memories will appear here.
                    </p>
                </section>

                <section
                    class="rounded-3xl border border-dashed border-slate-200 bg-white p-5"
                >
                    <p class="text-xs text-slate-400">Sponsored</p>
                    <p class="mt-2 text-sm text-slate-500">
                        Future relevant offer space
                    </p>
                </section>
            </aside>
        </div>
    </div>
</template>