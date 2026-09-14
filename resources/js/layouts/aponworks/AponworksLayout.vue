<script setup lang="ts">
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
} from 'vue';
import { Link, router } from '@inertiajs/vue3';

type Props = {
    activePage?: string;
    pageTitle?: string;
    pageSubtitle?: string;
    userName?: string;
    showRightPanel?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    activePage: 'home',
    pageTitle: '',
    pageSubtitle: '',
    userName: 'User',
    showRightPanel: true,
});

const sidebarCollapsed = ref(false);
const createOpen = ref(false);
const profileOpen = ref(false);
const notificationsOpen = ref(false);
const aponPanelOpen = ref(false);
const mobileMenuOpen = ref(false);

const askApon = ref('');

const toastVisible = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'info' | 'warning'>(
    'success',
);

let toastTimer: number | null = null;

const userInitial = computed(() => {
    return props.userName?.trim()?.charAt(0)?.toUpperCase() || 'U';
});

const sidebarWidth = computed(() => {
    return sidebarCollapsed.value
        ? 'lg:grid-cols-[88px_minmax(0,1fr)]'
        : 'lg:grid-cols-[250px_minmax(0,1fr)]';
});

const showToast = (
    message: string,
    type: 'success' | 'info' | 'warning' = 'success',
) => {
    toastMessage.value = message;
    toastType.value = type;
    toastVisible.value = true;

    if (toastTimer) {
        window.clearTimeout(toastTimer);
    }

    toastTimer = window.setTimeout(() => {
        toastVisible.value = false;
    }, 3200);
};

const closeFloatingMenus = () => {
    profileOpen.value = false;
    notificationsOpen.value = false;
};

const submitAskApon = () => {
    const query = askApon.value.trim();

    if (!query) {
        return;
    }

    showToast(
        `APON received: "${query}"`,
        'info',
    );

    askApon.value = '';
};

const createOptions = [
    {
        label: 'Task',
        description: 'Something you need to do',
        icon: '✓',
        href: '/tasks?create=1',
        className: 'from-emerald-50 to-white',
    },
    {
        label: 'Note',
        description: 'Save an idea or information',
        icon: '✎',
        href: '/notes?create=1',
        className: 'from-blue-50 to-white',
    },
    {
        label: 'Diary',
        description: 'Write a private journal entry',
        icon: '◫',
        href: '/diary?create=1',
        className: 'from-amber-50 to-white',
    },
    {
        label: 'Document',
        description: 'Add document or expiry information',
        icon: '▤',
        href: '#',
        className: 'from-violet-50 to-white',
        disabled: true,
    },
];

const createItem = (
    option: (typeof createOptions)[number],
) => {
    if (option.disabled) {
        showToast(
            'Documents will be available soon.',
            'info',
        );

        return;
    }

    createOpen.value = false;

    router.visit(option.href);
};

const primaryNavigation = [
    {
        label: 'Home',
        icon: '⌂',
        href: '/dashboard',
        key: 'home',
    },
    {
        label: 'My Day',
        icon: '◉',
        href: '/dashboard#my-day',
        key: 'my-day',
    },
];

const viewNavigation = [
    {
        label: 'Tasks',
        icon: '✓',
        href: '/tasks',
        key: 'tasks',
    },
    {
        label: 'Notes',
        icon: '✎',
        href: '/notes',
        key: 'notes',
    },
    {
        label: 'Diary',
        icon: '◫',
        href: '/diary',
        key: 'diary',
    },
];

const libraryNavigation = [
    {
        label: 'Documents',
        icon: '▤',
        href: '#',
        key: 'documents',
        comingSoon: true,
    },
    {
        label: 'People',
        icon: '◎',
        href: '#',
        key: 'people',
        comingSoon: true,
    },
];

const binNavigation = [
    {
        label: 'Tasks',
        href: '/bin/tasks',
        key: 'bin-tasks',
    },
    {
        label: 'Notes',
        href: '/bin/notes',
        key: 'bin-notes',
    },
    {
        label: 'Diary',
        href: '/bin/diary',
        key: 'bin-diary',
    },
];

const handleEscape = (event: KeyboardEvent) => {
    if (event.key !== 'Escape') {
        return;
    }

    createOpen.value = false;
    profileOpen.value = false;
    notificationsOpen.value = false;
    aponPanelOpen.value = false;
    mobileMenuOpen.value = false;
};

onMounted(() => {
    window.addEventListener('keydown', handleEscape);

    window.addEventListener(
        'aponworks-toast',
        ((event: CustomEvent) => {
            const detail = event.detail ?? {};

            showToast(
                detail.message ?? 'Saved successfully.',
                detail.type ?? 'success',
            );
        }) as EventListener,
    );
});

onBeforeUnmount(() => {
    window.removeEventListener(
        'keydown',
        handleEscape,
    );

    if (toastTimer) {
        window.clearTimeout(toastTimer);
    }
});
</script>

<template>
    <div
        class="min-h-screen bg-[#f3f5f8] text-slate-900"
        @click.self="closeFloatingMenus"
    >
        <!-- DESKTOP APP -->
        <div
            :class="[
                'mx-auto grid min-h-screen max-w-[1920px] transition-all duration-300',
                sidebarWidth,
            ]"
        >
            <!-- SIDEBAR -->
            <aside
                class="sticky top-0 hidden h-screen border-r border-slate-200/80 bg-white/95 shadow-[8px_0_30px_rgba(15,23,42,0.025)] backdrop-blur-xl lg:flex lg:flex-col"
            >
                <!-- BRAND -->
                <div
                    class="flex h-20 items-center justify-between border-b border-slate-100 px-5"
                >
                    <Link
                        href="/dashboard"
                        class="flex min-w-0 items-center gap-3"
                    >
                        <div
                            class="apon-brand-mark flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black text-white shadow-lg"
                        >
                            A
                        </div>

                        <Transition name="fade">
                            <div
                                v-if="!sidebarCollapsed"
                                class="min-w-0"
                            >
                                <div
                                    class="truncate text-[17px] font-black tracking-tight text-slate-950"
                                >
                                    APONWORKS
                                </div>

                                <div
                                    class="truncate text-[10px] font-medium uppercase tracking-[0.14em] text-slate-400"
                                >
                                    Personal Secretary
                                </div>
                            </div>
                        </Transition>
                    </Link>

                    <button
                        type="button"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-900"
                        @click="
                            sidebarCollapsed =
                                !sidebarCollapsed
                        "
                    >
                        <span
                            class="text-lg transition-transform duration-300"
                            :class="
                                sidebarCollapsed
                                    ? 'rotate-180'
                                    : ''
                            "
                        >
                            ‹
                        </span>
                    </button>
                </div>

                <!-- CREATE -->
                <div class="px-4 pt-5">
                    <button
                        type="button"
                        class="group flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-slate-900/10 transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800 hover:shadow-xl"
                        @click="createOpen = true"
                    >
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-white/15 text-base"
                        >
                            +
                        </span>

                        <Transition name="fade">
                            <span
                                v-if="!sidebarCollapsed"
                            >
                                Create
                            </span>
                        </Transition>
                    </button>
                </div>

                <!-- NAV -->
                <nav
                    class="apon-scrollbar flex-1 overflow-y-auto px-3 py-5"
                >
                    <!-- PRIMARY -->
                    <div class="space-y-1">
                        <Link
                            v-for="item in primaryNavigation"
                            :key="item.key"
                            :href="item.href"
                            :class="[
                                'group flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                activePage === item.key
                                    ? 'bg-slate-100 text-slate-950'
                                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-950',
                                sidebarCollapsed
                                    ? 'justify-center'
                                    : 'gap-3',
                            ]"
                        >
                            <span
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-base"
                                :class="
                                    activePage === item.key
                                        ? 'bg-white shadow-sm'
                                        : ''
                                "
                            >
                                {{ item.icon }}
                            </span>

                            <Transition name="fade">
                                <span
                                    v-if="
                                        !sidebarCollapsed
                                    "
                                >
                                    {{ item.label }}
                                </span>
                            </Transition>
                        </Link>
                    </div>

                    <!-- VIEW -->
                    <div class="mt-6">
                        <Transition name="fade">
                            <p
                                v-if="!sidebarCollapsed"
                                class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400"
                            >
                                View
                            </p>
                        </Transition>

                        <div class="space-y-1">
                            <Link
                                v-for="item in viewNavigation"
                                :key="item.key"
                                :href="item.href"
                                :class="[
                                    'group flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                    activePage ===
                                    item.key
                                        ? 'bg-slate-100 text-slate-950'
                                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-950',
                                    sidebarCollapsed
                                        ? 'justify-center'
                                        : 'gap-3',
                                ]"
                            >
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-[15px]"
                                    :class="
                                        activePage ===
                                        item.key
                                            ? 'bg-white shadow-sm'
                                            : ''
                                    "
                                >
                                    {{ item.icon }}
                                </span>

                                <Transition name="fade">
                                    <span
                                        v-if="
                                            !sidebarCollapsed
                                        "
                                        class="truncate"
                                    >
                                        {{ item.label }}
                                    </span>
                                </Transition>
                            </Link>
                        </div>
                    </div>

                    <!-- LIBRARY -->
                    <div class="mt-6">
                        <Transition name="fade">
                            <p
                                v-if="!sidebarCollapsed"
                                class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400"
                            >
                                Library
                            </p>
                        </Transition>

                        <div class="space-y-1">
                            <button
                                v-for="item in libraryNavigation"
                                :key="item.key"
                                type="button"
                                :class="[
                                    'group flex w-full items-center rounded-xl px-3 py-2.5 text-left text-sm font-medium transition',
                                    activePage ===
                                    item.key
                                        ? 'bg-slate-100 text-slate-950'
                                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-950',
                                    sidebarCollapsed
                                        ? 'justify-center'
                                        : 'gap-3',
                                ]"
                                @click="
                                    item.comingSoon
                                        ? showToast(
                                              `${item.label} is coming soon.`,
                                              'info',
                                          )
                                        : router.visit(
                                              item.href,
                                          )
                                "
                            >
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-base"
                                >
                                    {{ item.icon }}
                                </span>

                                <Transition name="fade">
                                    <div
                                        v-if="
                                            !sidebarCollapsed
                                        "
                                        class="flex min-w-0 flex-1 items-center justify-between"
                                    >
                                        <span>
                                            {{
                                                item.label
                                            }}
                                        </span>

                                        <span
                                            v-if="
                                                item.comingSoon
                                            "
                                            class="rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-slate-400"
                                        >
                                            Soon
                                        </span>
                                    </div>
                                </Transition>
                            </button>
                        </div>
                    </div>

                    <!-- MORE -->
                    <div class="mt-6">
                        <Transition name="fade">
                            <p
                                v-if="!sidebarCollapsed"
                                class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400"
                            >
                                More
                            </p>
                        </Transition>

                        <div v-if="!sidebarCollapsed" class="space-y-1">
                            <Link
                                href="/memories"
                                class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-50 hover:text-slate-950"
                                :class="activePage === 'all' ? 'bg-slate-100 text-slate-950' : ''"
                            >
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg">▦</span>
                                <span>All Items</span>
                            </Link>

                            <Link
                                href="/tasks/completed"
                                class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 transition hover:bg-slate-50 hover:text-slate-950"
                                :class="activePage === 'completed' ? 'bg-slate-100 text-slate-950' : ''"
                            >
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg">✓</span>
                                <span>Completed</span>
                            </Link>

                            <button
                                type="button"
                                class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-500 transition hover:bg-slate-50 hover:text-slate-950"
                                @click="router.visit('/bin/tasks')"
                            >
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg">⌫</span>
                                <span>Bin</span>
                            </button>
                        </div>

                        <button
                            v-else
                            type="button"
                            class="flex w-full justify-center rounded-xl px-3 py-2.5 text-slate-400 transition hover:bg-slate-50 hover:text-slate-900"
                            @click="router.visit('/memories')"
                        >
                            •••
                        </button>
                    </div>
                </nav>

                <!-- STORAGE -->
                <div
                    class="border-t border-slate-100 p-4"
                >
                    <Transition name="fade">
                        <div
                            v-if="!sidebarCollapsed"
                            class="rounded-2xl bg-slate-50 p-4"
                        >
                            <div
                                class="flex items-center justify-between"
                            >
                                <span
                                    class="text-xs font-semibold text-slate-500"
                                >
                                    Storage
                                </span>

                                <span
                                    class="text-xs font-bold text-slate-700"
                                >
                                    0 MB
                                </span>
                            </div>

                            <div
                                class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-200"
                            >
                                <div
                                    class="h-full w-[2%] rounded-full bg-slate-800"
                                ></div>
                            </div>

                            <p
                                class="mt-2 text-[10px] text-slate-400"
                            >
                                Device-first storage
                            </p>
                        </div>
                    </Transition>
                </div>
            </aside>

            <!-- CONTENT SIDE -->
            <div
                class="min-w-0 lg:min-h-screen"
            >
                <!-- TOP BAR -->
                <header
                    class="sticky top-0 z-30 border-b border-slate-200/70 bg-[#f3f5f8]/80 backdrop-blur-2xl"
                >
                    <div
                        class="flex h-20 items-center gap-3 px-4 md:px-6 xl:px-8"
                    >
                        <!-- MOBILE MENU -->
                        <button
                            type="button"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm ring-1 ring-slate-200 lg:hidden"
                            @click="
                                mobileMenuOpen = true
                            "
                        >
                            ☰
                        </button>

                        <!-- SEARCH / ASK APON -->
                        <form
                            class="relative max-w-2xl flex-1"
                            @submit.prevent="submitAskApon"
                        >
                            <span
                                class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-base text-slate-400"
                            >
                                ✦
                            </span>

                            <input
                                v-model="askApon"
                                type="text"
                                placeholder="Ask APON anything..."
                                class="h-11 w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-16 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-slate-300 focus:ring-4 focus:ring-slate-200/50"
                            />

                            <button
                                type="submit"
                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-xl bg-slate-950 px-3 py-1.5 text-[11px] font-bold text-white transition hover:bg-slate-800"
                            >
                                Ask
                            </button>
                        </form>

                        <div
                            class="ml-auto flex items-center gap-2"
                        >
                            <!-- APON BUTTON -->
                            <button
                                type="button"
                                class="apon-assistant-button hidden h-10 items-center gap-2 rounded-xl px-3 text-xs font-bold shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:flex"
                                @click="
                                    aponPanelOpen = true
                                "
                            >
                                <span>✦</span>
                                <span>APON</span>
                            </button>

                            <!-- NOTIFICATION -->
                            <div class="relative">
                                <button
                                    type="button"
                                    class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:-translate-y-0.5 hover:text-slate-900 hover:shadow-md"
                                    @click="
                                        notificationsOpen =
                                            !notificationsOpen;
                                        profileOpen = false;
                                    "
                                >
                                    ♢

                                    <span
                                        class="absolute right-2 top-2 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"
                                    ></span>
                                </button>

                                <Transition
                                    name="popup"
                                >
                                    <div
                                        v-if="
                                            notificationsOpen
                                        "
                                        class="absolute right-0 top-12 z-50 w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
                                    >
                                        <div
                                            class="border-b border-slate-100 px-4 py-3"
                                        >
                                            <p
                                                class="text-sm font-bold text-slate-900"
                                            >
                                                Notifications
                                            </p>
                                        </div>

                                        <div
                                            class="p-4"
                                        >
                                            <div
                                                class="rounded-xl bg-slate-50 p-4"
                                            >
                                                <p
                                                    class="text-sm font-semibold text-slate-700"
                                                >
                                                    You're
                                                    all caught
                                                    up.
                                                </p>

                                                <p
                                                    class="mt-1 text-xs leading-5 text-slate-400"
                                                >
                                                    Important
                                                    reminders
                                                    will
                                                    appear
                                                    here.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <!-- PROFILE -->
                            <div class="relative">
                                <button
                                    type="button"
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 text-xs font-black text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5"
                                    @click="
                                        profileOpen =
                                            !profileOpen;
                                        notificationsOpen =
                                            false;
                                    "
                                >
                                    {{ userInitial }}
                                </button>

                                <Transition
                                    name="popup"
                                >
                                    <div
                                        v-if="profileOpen"
                                        class="absolute right-0 top-12 z-50 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl"
                                    >
                                        <div
                                            class="border-b border-slate-100 px-3 py-3"
                                        >
                                            <p
                                                class="truncate text-sm font-bold text-slate-900"
                                            >
                                                {{
                                                    userName
                                                }}
                                            </p>

                                            <p
                                                class="mt-0.5 text-xs text-slate-400"
                                            >
                                                APONWORKS
                                                account
                                            </p>
                                        </div>

                                        <Link
                                            href="/settings/profile"
                                            class="mt-2 block rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-slate-50"
                                        >
                                            Settings
                                        </Link>

                                        <Link
                                            href="/logout"
                                            method="post"
                                            as="button"
                                            class="block w-full rounded-xl px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50"
                                        >
                                            Log out
                                        </Link>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- PAGE BODY -->
                <main
                    class="px-4 pb-28 pt-6 md:px-6 md:pt-8 lg:pb-10 xl:px-8"
                >
                    <!-- PAGE TITLE -->
                    <div
                        v-if="
                            pageTitle ||
                            pageSubtitle
                        "
                        class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <h1
                                v-if="pageTitle"
                                class="text-2xl font-black tracking-tight text-slate-950 md:text-3xl"
                            >
                                {{ pageTitle }}
                            </h1>

                            <p
                                v-if="pageSubtitle"
                                class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-500"
                            >
                                {{ pageSubtitle }}
                            </p>
                        </div>

                        <slot name="page-actions" />
                    </div>

                    <slot />
                </main>
            </div>
        </div>

        <!-- MOBILE BOTTOM NAV -->
        <nav
            class="fixed bottom-0 left-0 right-0 z-40 border-t border-slate-200 bg-white/95 px-3 pb-[max(10px,env(safe-area-inset-bottom))] pt-2 backdrop-blur-xl lg:hidden"
        >
            <div
                class="mx-auto flex max-w-md items-end justify-around"
            >
                <Link
                    href="/dashboard"
                    class="flex min-w-[58px] flex-col items-center gap-1 rounded-xl px-3 py-2 text-[10px] font-semibold"
                    :class="
                        activePage === 'home'
                            ? 'text-slate-950'
                            : 'text-slate-400'
                    "
                >
                    <span class="text-lg">⌂</span>
                    Home
                </Link>

                <Link
                    href="/tasks"
                    class="flex min-w-[58px] flex-col items-center gap-1 rounded-xl px-3 py-2 text-[10px] font-semibold"
                    :class="
                        activePage === 'tasks'
                            ? 'text-slate-950'
                            : 'text-slate-400'
                    "
                >
                    <span class="text-lg">✓</span>
                    Tasks
                </Link>

                <button
                    type="button"
                    class="-mt-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-950 text-2xl font-light text-white shadow-xl shadow-slate-900/20 transition active:scale-95"
                    @click="createOpen = true"
                >
                    +
                </button>

                <Link
                    href="/notes"
                    class="flex min-w-[58px] flex-col items-center gap-1 rounded-xl px-3 py-2 text-[10px] font-semibold"
                    :class="
                        activePage === 'notes'
                            ? 'text-slate-950'
                            : 'text-slate-400'
                    "
                >
                    <span class="text-lg">✎</span>
                    Notes
                </Link>

                <button
                    type="button"
                    class="flex min-w-[58px] flex-col items-center gap-1 rounded-xl px-3 py-2 text-[10px] font-semibold text-slate-400"
                    @click="aponPanelOpen = true"
                >
                    <span class="text-lg">✦</span>
                    APON
                </button>
            </div>
        </nav>

        <!-- CREATE MODAL -->
        <Transition name="modal">
            <div
                v-if="createOpen"
                class="fixed inset-0 z-[80] flex items-end justify-center bg-slate-950/30 p-0 backdrop-blur-sm sm:items-center sm:p-6"
                @click.self="createOpen = false"
            >
                <div
                    class="w-full max-w-xl rounded-t-[2rem] bg-white p-5 shadow-2xl sm:rounded-[2rem] sm:p-6"
                >
                    <div
                        class="mb-5 flex items-start justify-between gap-4"
                    >
                        <div>
                            <div
                                class="mb-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500"
                            >
                                Quick Create
                            </div>

                            <h2
                                class="text-xl font-black text-slate-950"
                            >
                                What would you like
                                to create?
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Choose an APONWORKS
                                workspace.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900"
                            @click="createOpen = false"
                        >
                            ×
                        </button>
                    </div>

                    <div
                        class="grid gap-3 sm:grid-cols-2"
                    >
                        <button
                            v-for="option in createOptions"
                            :key="option.label"
                            type="button"
                            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br p-4 text-left transition duration-200 hover:-translate-y-1 hover:border-slate-300 hover:shadow-xl"
                            :class="
                                option.className
                            "
                            @click="
                                createItem(option)
                            "
                        >
                            <div
                                class="flex items-start gap-4"
                            >
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-lg font-bold text-slate-800 shadow-sm ring-1 ring-slate-100 transition group-hover:scale-105"
                                >
                                    {{
                                        option.icon
                                    }}
                                </div>

                                <div
                                    class="min-w-0"
                                >
                                    <p
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        {{
                                            option.label
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs leading-5 text-slate-500"
                                    >
                                        {{
                                            option.description
                                        }}
                                    </p>
                                </div>
                            </div>

                            <span
                                v-if="
                                    option.disabled
                                "
                                class="absolute right-3 top-3 rounded-full bg-white px-2 py-1 text-[9px] font-bold uppercase tracking-wide text-slate-400 shadow-sm"
                            >
                                Soon
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- APON RIGHT PANEL -->
        <Transition name="drawer">
            <div
                v-if="aponPanelOpen"
                class="fixed inset-0 z-[70] bg-slate-950/20 backdrop-blur-[2px]"
                @click.self="
                    aponPanelOpen = false
                "
            >
                <aside
                    class="absolute bottom-0 right-0 top-0 flex w-full max-w-md flex-col bg-white shadow-2xl"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-5"
                    >
                        <div
                            class="flex items-center gap-3"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-950 text-white shadow-lg"
                            >
                                ✦
                            </div>

                            <div>
                                <p
                                    class="text-sm font-black text-slate-950"
                                >
                                    APON
                                </p>

                                <p
                                    class="text-xs text-slate-400"
                                >
                                    Personal
                                    intelligence
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200"
                            @click="
                                aponPanelOpen =
                                    false
                            "
                        >
                            ×
                        </button>
                    </div>

                    <div
                        class="apon-scrollbar flex-1 space-y-4 overflow-y-auto p-5"
                    >
                        <!-- ATTENTION CARD -->
                        <div
                            class="apon-card-glow overflow-hidden rounded-3xl bg-slate-950 p-5 text-white"
                        >
                            <div
                                class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-white/50"
                            >
                                <span>✦</span>
                                Anything I should
                                know?
                            </div>

                            <h3
                                class="mt-4 text-lg font-black"
                            >
                                You're all caught
                                up.
                            </h3>

                            <p
                                class="mt-2 text-sm leading-6 text-white/60"
                            >
                                When APON detects
                                an expiry, overdue
                                promise, conflict
                                or useful
                                connection, it
                                will appear here.
                            </p>
                        </div>

                        <!-- REMINDER BLOCK -->
                        <div
                            class="rounded-3xl border border-slate-200 bg-white p-5"
                        >
                            <div
                                class="flex items-center justify-between"
                            >
                                <h3
                                    class="text-sm font-bold text-slate-900"
                                >
                                    Upcoming
                                </h3>

                                <span
                                    class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-400"
                                >
                                    LIVE
                                </span>
                            </div>

                            <div
                                class="mt-4 rounded-2xl bg-slate-50 p-4"
                            >
                                <p
                                    class="text-sm font-semibold text-slate-700"
                                >
                                    No urgent
                                    reminders.
                                </p>

                                <p
                                    class="mt-1 text-xs leading-5 text-slate-400"
                                >
                                    Tasks and
                                    document
                                    expiries will
                                    appear here.
                                </p>
                            </div>
                        </div>

                        <!-- RECENT MEMORY -->
                        <div
                            class="rounded-3xl border border-slate-200 bg-white p-5"
                        >
                            <h3
                                class="text-sm font-bold text-slate-900"
                            >
                                Recent Memories
                            </h3>

                            <p
                                class="mt-2 text-xs leading-5 text-slate-400"
                            >
                                Your recent
                                captured
                                information will
                                appear here as
                                APONWORKS learns
                                from your saved
                                items.
                            </p>
                        </div>

                        <!-- SPONSORED -->
                        <div
                            class="rounded-3xl border border-dashed border-slate-200 p-5"
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-300"
                            >
                                Sponsored
                            </p>

                            <p
                                class="mt-2 text-xs leading-5 text-slate-400"
                            >
                                Reserved for
                                future optional
                                advertising.
                            </p>
                        </div>
                    </div>

                    <div
                        class="border-t border-slate-100 p-4"
                    >
                        <form
                            class="flex gap-2"
                            @submit.prevent="
                                submitAskApon
                            "
                        >
                            <input
                                v-model="askApon"
                                type="text"
                                placeholder="Ask APON..."
                                class="h-11 flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-slate-300 focus:bg-white"
                            />

                            <button
                                type="submit"
                                class="h-11 rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white"
                            >
                                Ask
                            </button>
                        </form>
                    </div>
                </aside>
            </div>
        </Transition>

        <!-- MOBILE SIDE MENU -->
        <Transition name="drawer-left">
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-0 z-[75] bg-slate-950/25 backdrop-blur-[2px] lg:hidden"
                @click.self="
                    mobileMenuOpen = false
                "
            >
                <aside
                    class="absolute bottom-0 left-0 top-0 w-[82%] max-w-xs bg-white p-4 shadow-2xl"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-4"
                    >
                        <Link
                            href="/dashboard"
                            class="flex items-center gap-3"
                            @click="
                                mobileMenuOpen =
                                    false
                            "
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-950 font-black text-white"
                            >
                                A
                            </div>

                            <div>
                                <p
                                    class="text-sm font-black text-slate-950"
                                >
                                    APONWORKS
                                </p>

                                <p
                                    class="text-[10px] text-slate-400"
                                >
                                    Personal
                                    Secretary
                                </p>
                            </div>
                        </Link>

                        <button
                            type="button"
                            class="h-9 w-9 rounded-xl bg-slate-100"
                            @click="
                                mobileMenuOpen =
                                    false
                            "
                        >
                            ×
                        </button>
                    </div>

                    <div
                        class="mt-5 space-y-2"
                    >
                        <Link
                            href="/dashboard"
                            class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            ⌂ Home
                        </Link>

                        <Link
                            href="/dashboard#my-day"
                            class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            ◉ My Day
                        </Link>

                        <Link
                            href="/tasks"
                            class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            ✓ Tasks
                        </Link>

                        <Link
                            href="/notes"
                            class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            ✎ Notes
                        </Link>

                        <Link
                            href="/diary"
                            class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            ◫ Diary
                        </Link>

                        <div class="my-3 border-t border-slate-100"></div>

                        <Link
                            href="/memories"
                            class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-500 hover:bg-slate-50"
                        >
                            ▦ All Items
                        </Link>

                        <Link
                            href="/tasks/completed"
                            class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-500 hover:bg-slate-50"
                        >
                            ✓ Completed
                        </Link>

                        <Link
                            href="/bin/tasks"
                            class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-500 hover:bg-slate-50"
                        >
                            ⌫ Bin
                        </Link>

                        <button
                            type="button"
                            class="w-full rounded-xl px-4 py-3 text-left text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            @click="
                                showToast(
                                    'Documents are coming soon.',
                                    'info',
                                )
                            "
                        >
                            ▤ Documents
                        </button>
                    </div>
                </aside>
            </div>
        </Transition>

        <!-- TOAST / FLASH -->
        <Transition name="toast">
            <div
                v-if="toastVisible"
                class="fixed bottom-24 left-1/2 z-[100] w-[calc(100%-32px)] max-w-sm -translate-x-1/2 rounded-2xl border bg-white p-4 shadow-2xl lg:bottom-8"
                :class="{
                    'border-emerald-200':
                        toastType ===
                        'success',
                    'border-blue-200':
                        toastType === 'info',
                    'border-amber-200':
                        toastType ===
                        'warning',
                }"
            >
                <div
                    class="flex items-start gap-3"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                        :class="{
                            'bg-emerald-50 text-emerald-600':
                                toastType ===
                                'success',
                            'bg-blue-50 text-blue-600':
                                toastType ===
                                'info',
                            'bg-amber-50 text-amber-600':
                                toastType ===
                                'warning',
                        }"
                    >
                        {{
                            toastType ===
                            'success'
                                ? '✓'
                                : toastType ===
                                    'warning'
                                  ? '!'
                                  : 'i'
                        }}
                    </div>

                    <div class="min-w-0">
                        <p
                            class="text-sm font-bold text-slate-900"
                        >
                            APONWORKS
                        </p>

                        <p
                            class="mt-0.5 text-xs leading-5 text-slate-500"
                        >
                            {{ toastMessage }}
                        </p>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* -------------------------------------------------------
   APONWORKS COMMERCIAL UI MOTION SYSTEM
------------------------------------------------------- */

.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 180ms ease,
        transform 180ms ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-2px);
}

.popup-enter-active,
.popup-leave-active {
    transition:
        opacity 180ms ease,
        transform 180ms cubic-bezier(0.2, 0.8, 0.2, 1);
    transform-origin: top right;
}

.popup-enter-from,
.popup-leave-to {
    opacity: 0;
    transform: scale(0.94) translateY(-5px);
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 220ms ease;
}

.modal-enter-active > div,
.modal-leave-active > div {
    transition:
        transform 260ms cubic-bezier(0.2, 0.8, 0.2, 1),
        opacity 220ms ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div,
.modal-leave-to > div {
    opacity: 0;
    transform: translateY(24px) scale(0.98);
}

.drawer-enter-active,
.drawer-leave-active {
    transition: opacity 240ms ease;
}

.drawer-enter-active aside,
.drawer-leave-active aside {
    transition: transform 280ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}

.drawer-enter-from aside,
.drawer-leave-to aside {
    transform: translateX(100%);
}

.drawer-left-enter-active,
.drawer-left-leave-active {
    transition: opacity 240ms ease;
}

.drawer-left-enter-active aside,
.drawer-left-leave-active aside {
    transition: transform 280ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.drawer-left-enter-from,
.drawer-left-leave-to {
    opacity: 0;
}

.drawer-left-enter-from aside,
.drawer-left-leave-to aside {
    transform: translateX(-100%);
}

.toast-enter-active,
.toast-leave-active {
    transition:
        opacity 220ms ease,
        transform 300ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translate(-50%, 18px) scale(0.96);
}

/* APON glow animation */
.apon-card-glow {
    position: relative;
    isolation: isolate;
}

.apon-card-glow::before {
    position: absolute;
    right: -40px;
    top: -50px;
    z-index: -1;
    height: 130px;
    width: 130px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.12);
    content: '';
    filter: blur(10px);
    animation: aponGlow 5s ease-in-out infinite alternate;
}

@keyframes aponGlow {
    from {
        transform: translate3d(0, 0, 0) scale(1);
        opacity: 0.55;
    }

    to {
        transform: translate3d(-30px, 25px, 0) scale(1.25);
        opacity: 0.9;
    }
}

/* Thin scrollbar */
.apon-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #d7dce2 transparent;
}

.apon-scrollbar::-webkit-scrollbar {
    width: 5px;
}

.apon-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.apon-scrollbar::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: #d7dce2;
}


/* APONWORKS V2 visual identity */
.apon-brand-mark {
    background:
        radial-gradient(circle at 30% 20%, rgba(255,255,255,.28), transparent 30%),
        linear-gradient(145deg, #111827 0%, #334155 55%, #0f172a 100%);
    box-shadow: 0 10px 24px rgba(15, 23, 42, .18);
}

.apon-assistant-button {
    border: 1px solid rgba(99, 102, 241, .16);
    background:
        linear-gradient(135deg, rgba(238,242,255,.95), rgba(255,255,255,.98));
    color: #4338ca;
}

.apon-assistant-button:hover {
    border-color: rgba(99, 102, 241, .28);
    background: linear-gradient(135deg, #eef2ff, #faf5ff);
}

/* Respect users who disable motion */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>