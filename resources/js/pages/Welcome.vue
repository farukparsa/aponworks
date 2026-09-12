<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

const submitLogin = () => {
    loginForm.post(login().url, {
        onFinish: () => loginForm.reset('password'),
    });
};
</script>

<template>
    <Head title="APONWORKS - Your Personal AI Secretary" />

    <div class="min-h-screen bg-[#f8f5ef] text-slate-900">
        <!-- TOP NAVIGATION -->
        <header
            class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/95 backdrop-blur"
        >
            <div
                class="mx-auto flex max-w-[1500px] items-center justify-between px-5 py-4 lg:px-10"
            >
                <!-- Logo -->
                <Link href="/" class="shrink-0">
                    <div class="text-2xl font-black tracking-tight text-[#103b69] sm:text-3xl">
                        APON<span class="font-semibold">WORKS</span>
                    </div>

                    <div class="mt-[-2px] text-[10px] text-slate-500 sm:text-xs">
                        Your Personal AI Secretary
                    </div>
                </Link>

                <!-- Desktop Menu -->
                <nav class="hidden items-center gap-8 text-sm font-medium lg:flex">
                    <a href="#home" class="hover:text-[#0e4a7d]">
                        Home
                    </a>

                    <a href="#features" class="hover:text-[#0e4a7d]">
                        Features
                    </a>

                    <a href="#how-it-works" class="hover:text-[#0e4a7d]">
                        How It Works
                    </a>

                    <a href="#about" class="hover:text-[#0e4a7d]">
                        About
                    </a>
                </nav>

                <!-- Login / Dashboard -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <template v-if="$page.props.auth?.user">
                        <Link
                            :href="dashboard()"
                            class="rounded-full bg-[#0d3b67] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#082f55]"
                        >
                            Dashboard
                        </Link>
                    </template>

                    <template v-else>
                        <Link
                            :href="login()"
                            class="hidden rounded-full px-4 py-2 text-sm font-semibold text-[#0d3b67] sm:block"
                        >
                            Log in
                        </Link>

                        <Link
                            :href="register()"
                            class="rounded-full bg-[#0d3b67] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#082f55]"
                        >
                            Get Started
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- HERO -->
        <main id="home">
            <section
                class="relative overflow-hidden border-b border-slate-200"
            >
                <!-- Soft background decoration -->
                <div
                    class="absolute -left-32 top-20 h-96 w-96 rounded-full bg-green-100/50 blur-3xl"
                ></div>

                <div
                    class="absolute right-0 top-0 h-[500px] w-[500px] rounded-full bg-orange-100/60 blur-3xl"
                ></div>

                <div
                    class="relative mx-auto grid max-w-[1500px] gap-10 px-5 py-10 lg:grid-cols-[1.15fr_0.85fr] lg:px-10 lg:py-16"
                >
                    <!-- LEFT -->
                    <div class="flex flex-col justify-center">
                        <div
                            class="mb-6 inline-flex w-fit items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 shadow-sm"
                        >
                            Organize
                            <span class="mx-2 text-slate-300">•</span>
                            Remember
                            <span class="mx-2 text-slate-300">•</span>
                            Plan
                            <span class="mx-2 text-slate-300">•</span>
                            Achieve
                        </div>

                        <h1
                            class="max-w-3xl text-4xl font-black leading-[1.05] tracking-tight text-[#092f59] sm:text-5xl lg:text-7xl"
                        >
                            Your Life,
                            <br />
                            Better Organized
                        </h1>

                        <p
                            class="mt-6 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg"
                        >
                            APONWORKS is your personal AI secretary for your
                            tasks, documents, notes, diary, reminders and
                            important information — all in one place.
                        </p>

                        <!-- Feature icons -->
                        <div
                            id="features"
                            class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4"
                        >
                            <div
                                class="rounded-2xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200"
                            >
                                <div
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl"
                                >
                                    ✓
                                </div>

                                <div class="mt-3 text-sm font-bold">
                                    Tasks
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    & Reminders
                                </div>
                            </div>

                            <div
                                class="rounded-2xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200"
                            >
                                <div
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-xl"
                                >
                                    📄
                                </div>

                                <div class="mt-3 text-sm font-bold">
                                    Documents
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    Expiry Alerts
                                </div>
                            </div>

                            <div
                                class="rounded-2xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200"
                            >
                                <div
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-pink-100 text-xl"
                                >
                                    📝
                                </div>

                                <div class="mt-3 text-sm font-bold">
                                    Notes
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    & Ideas
                                </div>
                            </div>

                            <div
                                class="rounded-2xl bg-white p-4 text-center shadow-sm ring-1 ring-slate-200"
                            >
                                <div
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-xl"
                                >
                                    📖
                                </div>

                                <div class="mt-3 text-sm font-bold">
                                    Personal
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    Diary
                                </div>
                            </div>
                        </div>

                        <!-- Mini dashboard visual -->
                        <div
                            id="how-it-works"
                            class="mt-8 overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-xl"
                        >
                            <div
                                class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
                            >
                                <div>
                                    <div class="text-sm font-black text-[#123b64]">
                                        APONWORKS
                                    </div>

                                    <div class="mt-1 text-xs text-slate-400">
                                        Your day at a glance
                                    </div>
                                </div>

                                <div
                                    class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-500"
                                >
                                    Personal Secretary
                                </div>
                            </div>

                            <div
                                class="grid gap-4 p-5 sm:grid-cols-[170px_1fr]"
                            >
                                <div
                                    class="hidden rounded-2xl bg-[#f7f8fa] p-4 sm:block"
                                >
                                    <div
                                        class="rounded-xl bg-white px-3 py-2 text-sm font-semibold text-[#0d3b67] shadow-sm"
                                    >
                                        🏠 Home
                                    </div>

                                    <div
                                        class="mt-2 px-3 py-2 text-sm text-slate-500"
                                    >
                                        ✅ Tasks
                                    </div>

                                    <div
                                        class="px-3 py-2 text-sm text-slate-500"
                                    >
                                        📝 Notes
                                    </div>

                                    <div
                                        class="px-3 py-2 text-sm text-slate-500"
                                    >
                                        📖 Diary
                                    </div>

                                    <div
                                        class="px-3 py-2 text-sm text-slate-500"
                                    >
                                        📄 Documents
                                    </div>

                                    <div
                                        class="px-3 py-2 text-sm text-slate-500"
                                    >
                                        👥 People
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xl font-bold">
                                        Good Morning
                                    </div>

                                    <div class="mt-1 text-sm text-slate-500">
                                        Here's what needs your attention.
                                    </div>

                                    <div
                                        class="mt-5 rounded-2xl border border-slate-200 p-4"
                                    >
                                        <div class="text-sm font-bold">
                                            Today
                                        </div>

                                        <div
                                            class="mt-3 flex items-center gap-3 rounded-xl bg-slate-50 p-3"
                                        >
                                            <div
                                                class="h-5 w-5 rounded-md border-2 border-slate-300"
                                            ></div>

                                            <div class="text-sm">
                                                Review vessel documents
                                            </div>
                                        </div>

                                        <div
                                            class="mt-2 flex items-center gap-3 rounded-xl bg-slate-50 p-3"
                                        >
                                            <div
                                                class="h-5 w-5 rounded-md border-2 border-slate-300"
                                            ></div>

                                            <div class="text-sm">
                                                Call bank manager
                                            </div>
                                        </div>

                                        <div
                                            class="mt-2 flex items-center gap-3 rounded-xl bg-slate-50 p-3"
                                        >
                                            <div
                                                class="h-5 w-5 rounded-md border-2 border-slate-300"
                                            ></div>

                                            <div class="text-sm">
                                                Update important notes
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT LOGIN -->
                    <div
                        class="flex items-start justify-center lg:justify-end"
                    >
                        <div
                            class="w-full max-w-lg rounded-[30px] bg-white p-6 shadow-[0_25px_70px_rgba(15,23,42,0.14)] ring-1 ring-slate-200 sm:p-9"
                        >
                            <div class="text-center">
                                <h2
                                    class="text-3xl font-black tracking-tight text-[#0b315d]"
                                >
                                    Welcome Back
                                </h2>

                                <p class="mt-2 text-sm text-slate-500">
                                    Login to your APONWORKS account
                                </p>
                            </div>

                            <template v-if="$page.props.auth?.user">
                                <div
                                    class="mt-8 rounded-2xl bg-green-50 p-5 text-center"
                                >
                                    <div
                                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl"
                                    >
                                        ✓
                                    </div>

                                    <div class="mt-3 font-bold">
                                        You are already logged in
                                    </div>

                                    <Link
                                        :href="dashboard()"
                                        class="mt-5 block rounded-xl bg-[#0d3b67] px-5 py-3.5 font-semibold text-white"
                                    >
                                        Open Dashboard
                                    </Link>
                                </div>
                            </template>

                            <form
                                v-else
                                class="mt-8"
                                @submit.prevent="submitLogin"
                            >
                                <!-- Future account options -->
                                <div
                                    class="mb-6 grid grid-cols-3 rounded-xl bg-slate-100 p-1"
                                >
                                    <button
                                        type="button"
                                        disabled
                                        class="rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-400"
                                        title="Phone login will be added later"
                                    >
                                        Phone
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded-lg bg-[#0d3b67] px-3 py-2.5 text-sm font-semibold text-white shadow-sm"
                                    >
                                        Email
                                    </button>

                                    <button
                                        type="button"
                                        disabled
                                        class="rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-400"
                                        title="Google login will be added later"
                                    >
                                        Google
                                    </button>
                                </div>

                                <div>
                                    <label
                                        for="email"
                                        class="text-sm font-bold text-slate-800"
                                    >
                                        Email
                                    </label>

                                    <input
                                        id="email"
                                        v-model="loginForm.email"
                                        type="email"
                                        autocomplete="email"
                                        placeholder="Enter your email"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-sm outline-none transition focus:border-[#0d3b67] focus:ring-2 focus:ring-blue-100"
                                    />

                                    <div
                                        v-if="loginForm.errors.email"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ loginForm.errors.email }}
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <label
                                            for="password"
                                            class="text-sm font-bold text-slate-800"
                                        >
                                            Password
                                        </label>

                                        <Link
                                            href="/forgot-password"
                                            class="text-xs font-semibold text-blue-600 hover:underline"
                                        >
                                            Forgot password?
                                        </Link>
                                    </div>

                                    <input
                                        id="password"
                                        v-model="loginForm.password"
                                        type="password"
                                        autocomplete="current-password"
                                        placeholder="Enter your password"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-sm outline-none transition focus:border-[#0d3b67] focus:ring-2 focus:ring-blue-100"
                                    />

                                    <div
                                        v-if="loginForm.errors.password"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ loginForm.errors.password }}
                                    </div>
                                </div>

                                <label
                                    class="mt-4 flex cursor-pointer items-center gap-2 text-sm text-slate-600"
                                >
                                    <input
                                        v-model="loginForm.remember"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300"
                                    />

                                    Remember me
                                </label>

                                <button
                                    type="submit"
                                    :disabled="loginForm.processing"
                                    class="mt-6 w-full rounded-xl bg-[#0d3b67] px-5 py-4 font-bold text-white shadow-lg transition hover:bg-[#082f55] disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    {{
                                        loginForm.processing
                                            ? 'Logging in...'
                                            : 'Login'
                                    }}
                                </button>

                                <div
                                    class="my-6 flex items-center gap-3 text-xs text-slate-400"
                                >
                                    <div class="h-px flex-1 bg-slate-200"></div>
                                    <span>or</span>
                                    <div class="h-px flex-1 bg-slate-200"></div>
                                </div>

                                <div class="text-center text-sm text-slate-500">
                                    Don't have an account?

                                    <Link
                                        :href="register()"
                                        class="font-bold text-blue-600 hover:underline"
                                    >
                                        Sign Up
                                    </Link>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <!-- LOWER FEATURES -->
            <section
                id="about"
                class="bg-white"
            >
                <div
                    class="mx-auto grid max-w-[1500px] gap-5 px-5 py-12 sm:grid-cols-2 lg:grid-cols-4 lg:px-10"
                >
                    <div class="rounded-2xl p-5">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-xl"
                        >
                            🛡️
                        </div>

                        <h3 class="mt-4 font-bold">
                            Your Data, Your Control
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Designed around private personal information and
                            user-controlled storage.
                        </p>
                    </div>

                    <div class="rounded-2xl p-5">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 text-xl"
                        >
                            📱
                        </div>

                        <h3 class="mt-4 font-bold">
                            Access Anywhere
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Built for mobile first, while remaining easy to use
                            on tablet and desktop.
                        </p>
                    </div>

                    <div class="rounded-2xl p-5">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl"
                        >
                            🌐
                        </div>

                        <h3 class="mt-4 font-bold">
                            Bangla & English
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            APONWORKS is being designed for multilingual and
                            mixed-language use.
                        </p>
                    </div>

                    <div class="rounded-2xl p-5">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-xl"
                        >
                            ✨
                        </div>

                        <h3 class="mt-4 font-bold">
                            All in One Place
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Tasks, notes, diary, documents, reminders and
                            personal memory in one connected system.
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <!-- FOOTER -->
        <footer class="border-t border-slate-200 bg-white">
            <div
                class="mx-auto flex max-w-[1500px] flex-col gap-4 px-5 py-7 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between lg:px-10"
            >
                <div>
                    <span class="font-black text-[#0d3b67]">
                        APONWORKS
                    </span>

                    <span class="mx-2 text-slate-300">|</span>

                    Your Personal AI Secretary
                </div>

                <div class="flex flex-wrap gap-5">
                    <span>Privacy</span>
                    <span>Terms</span>
                    <span>Support</span>
                    <span>© 2026 APONWORKS</span>
                </div>
            </div>
        </footer>
    </div>
</template>