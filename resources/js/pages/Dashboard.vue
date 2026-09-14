<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AponworksLayout from '@/layouts/aponworks/AponworksLayout.vue';

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
        (page.props.dayAfterTomorrowMemories as ScheduledMemory[] | undefined) ?? []
    );
});

const upcomingMemories = computed<ScheduledMemory[]>(() => {
    return (page.props.upcomingMemories as ScheduledMemory[] | undefined) ?? [];
});

const customScheduleMemories = computed<ScheduledMemory[]>(() => {
    return (
        (page.props.customScheduleMemories as ScheduledMemory[] | undefined) ?? []
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

    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
});

const firstName = computed(() => {
    const name = String(user?.name ?? '').trim();
    return name ? name.split(' ')[0] : 'there';
});

const memoryForm = useForm({
    type: 'note',
    description: '',
    due_at: '',
});

const composerTitle = computed(() => {
    if (memoryForm.type === 'task') return 'Add Task';
    if (memoryForm.type === 'diary') return 'Write Diary';
    return 'Add Note';
});

const composerPlaceholder = computed(() => {
    if (memoryForm.type === 'task') return 'What do you need to do?';
    if (memoryForm.type === 'diary') return 'Write your diary here...';
    return 'Write your note here...';
});

const saveButtonText = computed(() => {
    if (memoryForm.processing) return 'Saving...';
    if (memoryForm.type === 'task') return 'Save Task';
    if (memoryForm.type === 'diary') return 'Save Diary';
    return 'Save Note';
});

const dispatchToast = (
    message: string,
    type: 'success' | 'info' | 'warning' = 'success',
) => {
    window.dispatchEvent(
        new CustomEvent('aponworks-toast', {
            detail: { message, type },
        }),
    );
};

const saveMemory = () => {
    memoryForm.post('/memories', {
        preserveScroll: true,
        onSuccess: () => {
            const savedType = memoryForm.type;
            memoryForm.reset('description', 'due_at');
            dispatchToast(
                savedType === 'task'
                    ? 'Task saved successfully.'
                    : savedType === 'diary'
                      ? 'Diary entry saved.'
                      : 'Note saved successfully.',
            );
        },
    });
};

const completeMemory = (memoryId: number) => {
    router.patch(
        `/memories/${memoryId}/complete`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => dispatchToast('Task completed.'),
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
    if (!date) return '';

    return new Date(date).toLocaleDateString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
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

const scheduleBlocks = computed(() => {
    return [
        {
            key: 'today',
            title: 'Today',
            subtitle: 'Focus for today',
            items: todayMemories.value,
            icon: '◉',
        },
        {
            key: 'tomorrow',
            title: 'Tomorrow',
            subtitle: 'Coming next',
            items: tomorrowMemories.value,
            icon: '→',
        },
        {
            key: 'day-after',
            title: 'Day After Tomorrow',
            subtitle: 'A little further ahead',
            items: dayAfterTomorrowMemories.value,
            icon: '↗',
        },
        {
            key: 'upcoming',
            title: 'Upcoming',
            subtitle: 'Later schedule',
            items: upcomingMemories.value,
            icon: '⌁',
        },
    ].filter((block) => block.items.length > 0);
});
</script>

<template>
    <Head title="APONWORKS" />

    <AponworksLayout
        active-page="home"
        :page-title="`${greeting}, ${firstName}`"
        page-subtitle="Your day, memories and next actions — all in one place."
        :user-name="user?.name || 'User'"
    >
        <!-- HERO / DAILY BRIEF -->
        <section
            class="mb-6 overflow-hidden rounded-[28px] border border-slate-200/70 bg-white shadow-sm"
        >
            <div
                class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_310px]"
            >
                <div class="relative overflow-hidden p-5 sm:p-7">
                    <div
                        class="pointer-events-none absolute -right-10 -top-16 h-48 w-48 rounded-full bg-slate-100 blur-2xl"
                    ></div>

                    <div class="relative">
                        <div class="mb-5 flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-slate-950 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.15em] text-white"
                            >
                                <span>✦</span>
                                APON Daily
                            </span>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-500"
                            >
                                Live workspace
                            </span>
                        </div>

                        <h2
                            class="max-w-2xl text-2xl font-black tracking-tight text-slate-950 sm:text-3xl"
                        >
                            What deserves your attention today?
                        </h2>

                        <p
                            class="mt-3 max-w-2xl text-sm leading-6 text-slate-500"
                        >
                            Capture something quickly, review what is due, and let APON
                            surface what may need your attention.
                        </p>

                        <div class="mt-6 grid gap-3 sm:grid-cols-3">
                            <button
                                type="button"
                                class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition duration-200 hover:-translate-y-1 hover:bg-white hover:shadow-lg"
                                @click="memoryForm.type = 'task'"
                            >
                                <div
                                    class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-white text-sm font-black text-slate-900 shadow-sm"
                                >
                                    ✓
                                </div>

                                <p class="text-sm font-bold text-slate-900">
                                    Add Task
                                </p>

                                <p class="mt-1 text-xs leading-5 text-slate-400">
                                    Something you need to do
                                </p>
                            </button>

                            <button
                                type="button"
                                class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition duration-200 hover:-translate-y-1 hover:bg-white hover:shadow-lg"
                                @click="memoryForm.type = 'note'"
                            >
                                <div
                                    class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-white text-sm font-black text-slate-900 shadow-sm"
                                >
                                    ✎
                                </div>

                                <p class="text-sm font-bold text-slate-900">
                                    Add Note
                                </p>

                                <p class="mt-1 text-xs leading-5 text-slate-400">
                                    Save information or an idea
                                </p>
                            </button>

                            <button
                                type="button"
                                class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition duration-200 hover:-translate-y-1 hover:bg-white hover:shadow-lg"
                                @click="memoryForm.type = 'diary'"
                            >
                                <div
                                    class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-white text-sm font-black text-slate-900 shadow-sm"
                                >
                                    ◫
                                </div>

                                <p class="text-sm font-bold text-slate-900">
                                    Write Diary
                                </p>

                                <p class="mt-1 text-xs leading-5 text-slate-400">
                                    Record your day privately
                                </p>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="border-t border-slate-100 bg-slate-950 p-5 text-white xl:border-l xl:border-t-0"
                >
                    <div
                        class="flex items-center justify-between text-[10px] font-bold uppercase tracking-[0.14em] text-white/50"
                    >
                        <span>At a glance</span>
                        <span>✦</span>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white/8 p-4">
                            <p class="text-2xl font-black">
                                {{ todayMemories.length }}
                            </p>
                            <p class="mt-1 text-xs text-white/55">Today</p>
                        </div>

                        <div class="rounded-2xl bg-white/8 p-4">
                            <p class="text-2xl font-black">
                                {{ tomorrowMemories.length }}
                            </p>
                            <p class="mt-1 text-xs text-white/55">Tomorrow</p>
                        </div>

                        <div class="rounded-2xl bg-white/8 p-4">
                            <p class="text-2xl font-black">
                                {{ upcomingMemories.length }}
                            </p>
                            <p class="mt-1 text-xs text-white/55">Upcoming</p>
                        </div>

                        <div class="rounded-2xl bg-white/8 p-4">
                            <p class="text-2xl font-black">
                                {{ recentMemories.length }}
                            </p>
                            <p class="mt-1 text-xs text-white/55">Recent</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- QUICK CAPTURE -->
        <section
            id="memory-composer"
            class="mb-6 rounded-[28px] border border-slate-200/70 bg-white p-5 shadow-sm sm:p-6"
        >
            <form @submit.prevent="saveMemory">
                <div
                    class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-slate-950">
                                {{ composerTitle }}
                            </h3>

                            <span
                                class="rounded-full bg-slate-100 px-2.5 py-1 text-[9px] font-bold uppercase tracking-[0.12em] text-slate-500"
                            >
                                {{ memoryForm.type }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs leading-5 text-slate-400">
                            Quick capture from your dashboard.
                        </p>
                    </div>

                    <div class="flex rounded-2xl bg-slate-100 p-1">
                        <button
                            type="button"
                            class="rounded-xl px-3 py-2 text-xs font-bold transition"
                            :class="
                                memoryForm.type === 'task'
                                    ? 'bg-white text-slate-950 shadow-sm'
                                    : 'text-slate-400 hover:text-slate-700'
                            "
                            @click="memoryForm.type = 'task'"
                        >
                            Task
                        </button>

                        <button
                            type="button"
                            class="rounded-xl px-3 py-2 text-xs font-bold transition"
                            :class="
                                memoryForm.type === 'note'
                                    ? 'bg-white text-slate-950 shadow-sm'
                                    : 'text-slate-400 hover:text-slate-700'
                            "
                            @click="memoryForm.type = 'note'"
                        >
                            Note
                        </button>

                        <button
                            type="button"
                            class="rounded-xl px-3 py-2 text-xs font-bold transition"
                            :class="
                                memoryForm.type === 'diary'
                                    ? 'bg-white text-slate-950 shadow-sm'
                                    : 'text-slate-400 hover:text-slate-700'
                            "
                            @click="memoryForm.type = 'diary'"
                        >
                            Diary
                        </button>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition focus-within:border-slate-300 focus-within:bg-white focus-within:ring-4 focus-within:ring-slate-100"
                >
                    <textarea
                        id="memory-description"
                        v-model="memoryForm.description"
                        rows="4"
                        :placeholder="composerPlaceholder"
                        class="w-full resize-none border-0 bg-transparent text-base leading-7 text-slate-800 outline-none placeholder:text-slate-400"
                    ></textarea>

                    <p
                        v-if="memoryForm.errors.description"
                        class="mt-2 text-sm text-rose-600"
                    >
                        {{ memoryForm.errors.description }}
                    </p>

                    <div
                        class="mt-4 flex flex-col gap-3 border-t border-slate-200/70 pt-4 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div class="flex flex-wrap items-end gap-2">
                            <div>
                                <label
                                    for="memory-due-date"
                                    class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    {{
                                        memoryForm.type === 'task'
                                            ? 'Due date'
                                            : 'Date'
                                    }}
                                </label>

                                <input
                                    id="memory-due-date"
                                    v-model="memoryForm.due_at"
                                    type="date"
                                    class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-600 outline-none transition focus:border-slate-300"
                                />
                            </div>

                            <button
                                type="button"
                                class="h-10 rounded-xl bg-white px-3 text-xs font-bold text-slate-500 ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:text-slate-900 hover:shadow-sm"
                                @click="
                                    dispatchToast(
                                        'File attachment will be added in the next stage.',
                                        'info',
                                    )
                                "
                            >
                                📎 File
                            </button>

                            <button
                                type="button"
                                class="h-10 rounded-xl bg-white px-3 text-xs font-bold text-slate-500 ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:text-slate-900 hover:shadow-sm"
                                @click="
                                    dispatchToast(
                                        'Photo capture will be added in the next stage.',
                                        'info',
                                    )
                                "
                            >
                                ◉ Photo
                            </button>

                            <button
                                type="button"
                                class="h-10 rounded-xl bg-white px-3 text-xs font-bold text-slate-500 ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:text-slate-900 hover:shadow-sm"
                                @click="
                                    dispatchToast(
                                        'Voice capture will be added in the next stage.',
                                        'info',
                                    )
                                "
                            >
                                ◌ Voice
                            </button>
                        </div>

                        <button
                            type="submit"
                            :disabled="
                                memoryForm.processing ||
                                !memoryForm.description.trim()
                            "
                            class="h-11 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            {{ saveButtonText }}
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <!-- CONDITIONAL SCHEDULE BLOCKS -->
        <section v-if="scheduleBlocks.length" class="mb-6">
            <div
                class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p
                        class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400"
                    >
                        Schedule
                    </p>

                    <h3 class="mt-1 text-xl font-black text-slate-950">
                        What is coming up
                    </h3>
                </div>

                <p class="text-xs text-slate-400">
                    Empty periods stay hidden automatically.
                </p>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                <TransitionGroup name="card-list">
                    <article
                        v-for="block in scheduleBlocks"
                        :key="block.key"
                        class="group rounded-[26px] border border-slate-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-sm font-black text-slate-700 transition group-hover:bg-slate-950 group-hover:text-white"
                                >
                                    {{ block.icon }}
                                </div>

                                <div>
                                    <p class="text-sm font-black text-slate-950">
                                        {{ block.title }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        {{ block.subtitle }}
                                    </p>
                                </div>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500"
                            >
                                {{ block.items.length }}
                            </span>
                        </div>

                        <div class="mt-4 space-y-2">
                            <div
                                v-for="memory in block.items"
                                :key="memory.id"
                                class="rounded-2xl bg-slate-50 p-4 transition hover:bg-slate-100/80"
                            >
                                <div
                                    class="flex flex-wrap items-center justify-between gap-2"
                                >
                                    <span
                                        class="rounded-full bg-white px-2.5 py-1 text-[9px] font-bold uppercase tracking-[0.12em] text-slate-400 ring-1 ring-slate-100"
                                    >
                                        {{ memory.type }}
                                    </span>

                                    <span
                                        v-if="block.key === 'upcoming'"
                                        class="text-[11px] font-medium text-slate-400"
                                    >
                                        {{ formatDate(memory.due_at) }}
                                    </span>
                                </div>

                                <p
                                    class="mt-3 text-sm leading-6 text-slate-700"
                                >
                                    {{ memory.description }}
                                </p>

                                <button
                                    v-if="memory.type === 'task'"
                                    type="button"
                                    class="mt-3 rounded-xl bg-white px-3 py-2 text-[11px] font-bold text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:text-slate-950 hover:shadow"
                                    @click="completeMemory(memory.id)"
                                >
                                    ✓ Complete
                                </button>
                            </div>
                        </div>
                    </article>
                </TransitionGroup>
            </div>
        </section>

        <!-- CUSTOM SCHEDULE + RECENT -->
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,.65fr)]">
            <section
                class="rounded-[28px] border border-slate-200/70 bg-white p-5 shadow-sm sm:p-6"
            >
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400"
                        >
                            Planner
                        </p>

                        <h3 class="mt-1 text-lg font-black text-slate-950">
                            Custom Schedule
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-slate-400">
                            Look ahead by date range and memory type.
                        </p>
                    </div>

                    <span
                        class="self-start rounded-full bg-slate-100 px-3 py-1.5 text-[10px] font-bold text-slate-500 sm:self-auto"
                    >
                        {{ customScheduleMemories.length }} items
                    </span>
                </div>

                <form
                    class="mt-5 grid gap-3 rounded-2xl bg-slate-50 p-4 md:grid-cols-[1fr_1fr_auto] md:items-end"
                    @submit.prevent="applyCustomSchedule"
                >
                    <div>
                        <label
                            for="custom-days"
                            class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                        >
                            Next number of days
                        </label>

                        <input
                            id="custom-days"
                            v-model.number="customDays"
                            type="number"
                            min="1"
                            step="1"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-slate-300"
                        />
                    </div>

                    <div>
                        <label
                            for="custom-type"
                            class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                        >
                            Show
                        </label>

                        <select
                            id="custom-type"
                            v-model="customType"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-slate-300"
                        >
                            <option value="all">All</option>
                            <option value="task">Tasks only</option>
                            <option value="note">Notes only</option>
                            <option value="diary">Diary only</option>
                            <option value="expiry">Expiry only</option>
                        </select>
                    </div>

                    <button
                        type="submit"
                        class="h-11 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-slate-800"
                    >
                        Apply
                    </button>
                </form>

                <div
                    v-if="customScheduleMemories.length"
                    class="mt-4 space-y-2"
                >
                    <TransitionGroup name="card-list">
                        <div
                            v-for="memory in customScheduleMemories"
                            :key="`${customMemoryLabel(memory)}-${memory.id}`"
                            class="rounded-2xl border border-slate-100 bg-slate-50 p-4 transition hover:bg-slate-100/70"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-3"
                            >
                                <span
                                    class="text-[10px] font-bold uppercase tracking-[0.13em] text-slate-400"
                                >
                                    {{ customMemoryLabel(memory) }}
                                </span>

                                <span
                                    class="rounded-lg bg-white px-2.5 py-1 text-[11px] font-medium text-slate-500 shadow-sm"
                                >
                                    {{ customMemoryDate(memory) }}
                                </span>
                            </div>

                            <p class="mt-2 text-sm leading-6 text-slate-700">
                                {{ memory.description }}
                            </p>

                            <button
                                v-if="memory.type === 'task'"
                                type="button"
                                class="mt-3 rounded-xl bg-white px-3 py-2 text-[11px] font-bold text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:text-slate-950"
                                @click="completeMemory(memory.id)"
                            >
                                ✓ Complete
                            </button>
                        </div>
                    </TransitionGroup>
                </div>

                <div
                    v-else
                    class="mt-4 rounded-2xl border border-dashed border-slate-200 p-6 text-center"
                >
                    <div
                        class="mx-auto flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"
                    >
                        ◌
                    </div>

                    <p class="mt-3 text-sm font-bold text-slate-700">
                        Nothing matches this view
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Change the range or type and try again.
                    </p>
                </div>
            </section>

            <section
                class="rounded-[28px] border border-slate-200/70 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400"
                        >
                            Memory stream
                        </p>

                        <h3 class="mt-1 text-lg font-black text-slate-950">
                            Recent Memories
                        </h3>
                    </div>

                    <span
                        class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500"
                    >
                        {{ recentMemories.length }}
                    </span>
                </div>

                <div v-if="recentMemories.length" class="mt-5 space-y-3">
                    <div
                        v-for="memory in recentMemories"
                        :key="memory.id"
                        class="group rounded-2xl bg-slate-50 p-4 transition duration-200 hover:-translate-y-0.5 hover:bg-white hover:shadow-md hover:ring-1 hover:ring-slate-100"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <span
                                class="text-[9px] font-bold uppercase tracking-[0.13em] text-slate-400"
                            >
                                {{ memory.type }}
                            </span>

                            <span class="text-[10px] text-slate-400">
                                {{ formatDate(memory.created_at) }}
                            </span>
                        </div>

                        <p
                            class="mt-2 line-clamp-3 text-sm leading-6 text-slate-700"
                        >
                            {{ memory.description }}
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="mt-5 rounded-2xl border border-dashed border-slate-200 p-6 text-center"
                >
                    <div
                        class="mx-auto flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"
                    >
                        ✦
                    </div>

                    <p class="mt-3 text-sm font-bold text-slate-700">
                        Your memory stream is empty
                    </p>

                    <p class="mt-1 text-xs leading-5 text-slate-400">
                        Save a task, note or diary entry and it will appear here.
                    </p>
                </div>
            </section>
        </div>
    </AponworksLayout>
</template>

<style scoped>
.card-list-enter-active,
.card-list-leave-active {
    transition:
        opacity 220ms ease,
        transform 260ms cubic-bezier(0.2, 0.8, 0.2, 1);
}

.card-list-enter-from,
.card-list-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.98);
}

.card-list-move {
    transition: transform 260ms ease;
}

@media (prefers-reduced-motion: reduce) {
    .card-list-enter-active,
    .card-list-leave-active,
    .card-list-move {
        transition: none !important;
    }
}
</style>
