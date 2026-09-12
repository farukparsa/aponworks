<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

type Task = {
    id: number;
    title: string | null;
    description: string;
    status: string;
    due_at: string | null;
    created_at: string;
    updated_at: string;
};

const props = defineProps<{
    tasks: Task[];
}>();

const taskForm = useForm({
    type: 'task',
    description: '',
    due_at: '',
});

const editingTaskId = ref<number | null>(null);

const editForm = useForm({
    description: '',
    due_at: '',
});

const localDateString = (date: Date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const todayDate = computed(() => {
    return localDateString(new Date());
});

const tomorrowDate = computed(() => {
    const date = new Date();
    date.setDate(date.getDate() + 1);

    return localDateString(date);
});

const dayAfterTomorrowDate = computed(() => {
    const date = new Date();
    date.setDate(date.getDate() + 2);

    return localDateString(date);
});

const taskDate = (task: Task) => {
    if (!task.due_at) {
        return null;
    }

    return task.due_at.substring(0, 10);
};

const overdueTasks = computed(() => {
    return props.tasks.filter((task) => {
        const due = taskDate(task);

        return due && due < todayDate.value;
    });
});

const todayTasks = computed(() => {
    return props.tasks.filter((task) => {
        return taskDate(task) === todayDate.value;
    });
});

const tomorrowTasks = computed(() => {
    return props.tasks.filter((task) => {
        return taskDate(task) === tomorrowDate.value;
    });
});

const dayAfterTomorrowTasks = computed(() => {
    return props.tasks.filter((task) => {
        return taskDate(task) === dayAfterTomorrowDate.value;
    });
});

const upcomingTasks = computed(() => {
    return props.tasks.filter((task) => {
        const due = taskDate(task);

        return due && due > dayAfterTomorrowDate.value;
    });
});

const noDateTasks = computed(() => {
    return props.tasks.filter((task) => !task.due_at);
});

const hasTasks = computed(() => {
    return props.tasks.length > 0;
});

const addTask = () => {
    taskForm.post('/memories', {
        preserveScroll: true,

        onSuccess: () => {
            taskForm.reset('description', 'due_at');
        },
    });
};

const completeTask = (task: Task) => {
    router.patch(
        `/memories/${task.id}/complete`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const startEditing = (task: Task) => {
    editingTaskId.value = task.id;

    editForm.description = task.description;
    editForm.due_at = task.due_at
        ? task.due_at.substring(0, 10)
        : '';
};

const cancelEditing = () => {
    editingTaskId.value = null;

    editForm.reset();
    editForm.clearErrors();
};

const saveEdit = (task: Task) => {
    editForm.patch(`/memories/${task.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            editingTaskId.value = null;
            editForm.reset();
        },
    });
};

const deleteTask = (task: Task) => {
    const confirmed = window.confirm(
        'Move this task to BIN?',
    );

    if (!confirmed) {
        return;
    }

    router.delete(`/memories/${task.id}`, {
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
    <Head title="Tasks - APONWORKS" />

    <div class="min-h-screen bg-[#f8f7f4] p-4 md:p-6">
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
                                href="/dashboard"
                                class="block rounded-xl bg-slate-900 px-3 py-2 text-white"
                            >
                                ✅ Add Task
                            </Link>

                            <Link
                                href="/dashboard"
                                class="block rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-100"
                            >
                                📝 Add Note
                            </Link>

                            <Link
                                href="/dashboard"
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
                                class="block rounded-xl bg-slate-100 px-3 py-2 font-semibold text-slate-900"
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

            <!-- MAIN TASK WORKSPACE -->
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
                                Task Management
                            </div>

                            <h2
                                class="text-3xl font-bold tracking-tight text-slate-900 md:text-4xl"
                            >
                                Tasks
                            </h2>

                            <p
                                class="mt-2 max-w-xl text-sm leading-6 text-slate-500"
                            >
                                Add, manage and complete your tasks in one
                                place.
                            </p>
                        </div>

                        <Link
                            href="/tasks/completed"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            ✓ Completed Tasks
                        </Link>
                    </div>
                </section>

                <!-- ADD TASK -->
                <section
                    class="mb-6 rounded-[2rem] bg-white p-5 shadow-sm md:p-6"
                >
                    <form @submit.prevent="addTask">
                        <div
                            class="flex items-center justify-between gap-3"
                        >
                            <div>
                                <h3
                                    class="text-lg font-semibold text-slate-900"
                                >
                                    Add Task
                                </h3>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    What do you need to do?
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500"
                            >
                                New
                            </span>
                        </div>

                        <textarea
                            v-model="taskForm.description"
                            rows="3"
                            placeholder="Write your task here..."
                            class="mt-4 w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white"
                        ></textarea>

                        <p
                            v-if="taskForm.errors.description"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ taskForm.errors.description }}
                        </p>

                        <div
                            class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
                        >
                            <div>
                                <label
                                    class="mb-2 block text-xs font-medium text-slate-500"
                                >
                                    Due Date
                                    <span class="text-slate-400">
                                        (optional)
                                    </span>
                                </label>

                                <input
                                    v-model="taskForm.due_at"
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
                                        taskForm.processing ||
                                        !taskForm.description.trim()
                                    "
                                    class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        taskForm.processing
                                            ? 'Adding...'
                                            : '+ Add Task'
                                    }}
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- NO TASKS -->
                <section
                    v-if="!hasTasks"
                    class="rounded-[2rem] bg-white p-10 text-center shadow-sm"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl"
                    >
                        ✓
                    </div>

                    <h3
                        class="mt-4 text-lg font-semibold text-slate-900"
                    >
                        No active tasks
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500"
                    >
                        Add your first task above. APONWORKS will organise it
                        automatically by date.
                    </p>
                </section>

                <!-- TASK SECTIONS -->
                <div v-else class="space-y-6">

                    <!-- OVERDUE -->
                    <section
                        v-if="overdueTasks.length"
                        class="rounded-[2rem] bg-white p-5 shadow-sm md:p-6"
                    >
                        <div
                            class="mb-5 flex items-center justify-between"
                        >
                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900"
                                >
                                    Overdue
                                </h3>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    Tasks that passed their due date
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500"
                            >
                                {{ overdueTasks.length }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="task in overdueTasks"
                                :key="task.id"
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <template
                                    v-if="editingTaskId !== task.id"
                                >
                                    <div
                                        class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="text-sm font-medium leading-6 text-slate-800"
                                            >
                                                {{ task.description }}
                                            </p>

                                            <p
                                                class="mt-2 text-xs text-slate-400"
                                            >
                                                Due
                                                {{ formatDate(task.due_at) }}
                                            </p>
                                        </div>

                                        <div
                                            class="flex flex-wrap gap-2"
                                        >
                                            <button
                                                type="button"
                                                @click="completeTask(task)"
                                                class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white"
                                            >
                                                ✓ Complete
                                            </button>

                                            <button
                                                type="button"
                                                @click="startEditing(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-slate-200"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                type="button"
                                                @click="deleteTask(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-red-600 ring-1 ring-slate-200"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <form
                                        @submit.prevent="saveEdit(task)"
                                    >
                                        <textarea
                                            v-model="editForm.description"
                                            rows="3"
                                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none"
                                        ></textarea>

                                        <input
                                            v-model="editForm.due_at"
                                            type="date"
                                            class="mt-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm"
                                        />

                                        <div
                                            class="mt-3 flex gap-2"
                                        >
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

                    <!-- TODAY -->
                    <section
                        v-if="todayTasks.length"
                        class="rounded-[2rem] bg-white p-5 shadow-sm md:p-6"
                    >
                        <div
                            class="mb-5 flex items-center justify-between"
                        >
                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900"
                                >
                                    Today
                                </h3>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    Tasks for today
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500"
                            >
                                {{ todayTasks.length }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="task in todayTasks"
                                :key="task.id"
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <template
                                    v-if="editingTaskId !== task.id"
                                >
                                    <div
                                        class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="text-sm font-medium leading-6 text-slate-800"
                                            >
                                                {{ task.description }}
                                            </p>

                                            <p
                                                class="mt-2 text-xs text-slate-400"
                                            >
                                                Today
                                            </p>
                                        </div>

                                        <div
                                            class="flex flex-wrap gap-2"
                                        >
                                            <button
                                                type="button"
                                                @click="completeTask(task)"
                                                class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white"
                                            >
                                                ✓ Complete
                                            </button>

                                            <button
                                                type="button"
                                                @click="startEditing(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-slate-200"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                type="button"
                                                @click="deleteTask(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-red-600 ring-1 ring-slate-200"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <form
                                        @submit.prevent="saveEdit(task)"
                                    >
                                        <textarea
                                            v-model="editForm.description"
                                            rows="3"
                                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none"
                                        ></textarea>

                                        <input
                                            v-model="editForm.due_at"
                                            type="date"
                                            class="mt-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm"
                                        />

                                        <div
                                            class="mt-3 flex gap-2"
                                        >
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

                    <!-- TOMORROW -->
                    <section
                        v-if="tomorrowTasks.length"
                        class="rounded-[2rem] bg-white p-5 shadow-sm md:p-6"
                    >
                        <div
                            class="mb-5 flex items-center justify-between"
                        >
                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900"
                                >
                                    Tomorrow
                                </h3>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    Tasks for tomorrow
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500"
                            >
                                {{ tomorrowTasks.length }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="task in tomorrowTasks"
                                :key="task.id"
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <template
                                    v-if="editingTaskId !== task.id"
                                >
                                    <div
                                        class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                                    >
                                        <div class="flex-1">
                                            <p
                                                class="text-sm font-medium leading-6 text-slate-800"
                                            >
                                                {{ task.description }}
                                            </p>

                                            <p
                                                class="mt-2 text-xs text-slate-400"
                                            >
                                                Tomorrow
                                            </p>
                                        </div>

                                        <div
                                            class="flex flex-wrap gap-2"
                                        >
                                            <button
                                                type="button"
                                                @click="completeTask(task)"
                                                class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white"
                                            >
                                                ✓ Complete
                                            </button>

                                            <button
                                                type="button"
                                                @click="startEditing(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-slate-200"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                type="button"
                                                @click="deleteTask(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-red-600 ring-1 ring-slate-200"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <form
                                        @submit.prevent="saveEdit(task)"
                                    >
                                        <textarea
                                            v-model="editForm.description"
                                            rows="3"
                                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none"
                                        ></textarea>

                                        <input
                                            v-model="editForm.due_at"
                                            type="date"
                                            class="mt-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm"
                                        />

                                        <div
                                            class="mt-3 flex gap-2"
                                        >
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

                    <!-- DAY AFTER TOMORROW -->
                    <section
                        v-if="dayAfterTomorrowTasks.length"
                        class="rounded-[2rem] bg-white p-5 shadow-sm md:p-6"
                    >
                        <div
                            class="mb-5 flex items-center justify-between"
                        >
                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900"
                                >
                                    Day After Tomorrow
                                </h3>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    Tasks for the following day
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500"
                            >
                                {{ dayAfterTomorrowTasks.length }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="task in dayAfterTomorrowTasks"
                                :key="task.id"
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <template
                                    v-if="editingTaskId !== task.id"
                                >
                                    <div
                                        class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                                    >
                                        <div class="flex-1">
                                            <p
                                                class="text-sm font-medium leading-6 text-slate-800"
                                            >
                                                {{ task.description }}
                                            </p>

                                            <p
                                                class="mt-2 text-xs text-slate-400"
                                            >
                                                {{
                                                    formatDate(task.due_at)
                                                }}
                                            </p>
                                        </div>

                                        <div
                                            class="flex flex-wrap gap-2"
                                        >
                                            <button
                                                type="button"
                                                @click="completeTask(task)"
                                                class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white"
                                            >
                                                ✓ Complete
                                            </button>

                                            <button
                                                type="button"
                                                @click="startEditing(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-slate-200"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                type="button"
                                                @click="deleteTask(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-red-600 ring-1 ring-slate-200"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <form
                                        @submit.prevent="saveEdit(task)"
                                    >
                                        <textarea
                                            v-model="editForm.description"
                                            rows="3"
                                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none"
                                        ></textarea>

                                        <input
                                            v-model="editForm.due_at"
                                            type="date"
                                            class="mt-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm"
                                        />

                                        <div
                                            class="mt-3 flex gap-2"
                                        >
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

                    <!-- UPCOMING -->
                    <section
                        v-if="upcomingTasks.length"
                        class="rounded-[2rem] bg-white p-5 shadow-sm md:p-6"
                    >
                        <div
                            class="mb-5 flex items-center justify-between"
                        >
                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900"
                                >
                                    Upcoming
                                </h3>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    Future tasks
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500"
                            >
                                {{ upcomingTasks.length }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="task in upcomingTasks"
                                :key="task.id"
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <template
                                    v-if="editingTaskId !== task.id"
                                >
                                    <div
                                        class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                                    >
                                        <div class="flex-1">
                                            <p
                                                class="text-sm font-medium leading-6 text-slate-800"
                                            >
                                                {{ task.description }}
                                            </p>

                                            <p
                                                class="mt-2 text-xs text-slate-400"
                                            >
                                                {{
                                                    formatDate(task.due_at)
                                                }}
                                            </p>
                                        </div>

                                        <div
                                            class="flex flex-wrap gap-2"
                                        >
                                            <button
                                                type="button"
                                                @click="completeTask(task)"
                                                class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white"
                                            >
                                                ✓ Complete
                                            </button>

                                            <button
                                                type="button"
                                                @click="startEditing(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-slate-200"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                type="button"
                                                @click="deleteTask(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-red-600 ring-1 ring-slate-200"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <form
                                        @submit.prevent="saveEdit(task)"
                                    >
                                        <textarea
                                            v-model="editForm.description"
                                            rows="3"
                                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none"
                                        ></textarea>

                                        <input
                                            v-model="editForm.due_at"
                                            type="date"
                                            class="mt-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm"
                                        />

                                        <div
                                            class="mt-3 flex gap-2"
                                        >
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

                    <!-- NO DATE -->
                    <section
                        v-if="noDateTasks.length"
                        class="rounded-[2rem] bg-white p-5 shadow-sm md:p-6"
                    >
                        <div
                            class="mb-5 flex items-center justify-between"
                        >
                            <div>
                                <h3
                                    class="text-lg font-bold text-slate-900"
                                >
                                    No Date
                                </h3>

                                <p
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    Tasks without a due date
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500"
                            >
                                {{ noDateTasks.length }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="task in noDateTasks"
                                :key="task.id"
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <template
                                    v-if="editingTaskId !== task.id"
                                >
                                    <div
                                        class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                                    >
                                        <div class="flex-1">
                                            <p
                                                class="text-sm font-medium leading-6 text-slate-800"
                                            >
                                                {{ task.description }}
                                            </p>

                                            <p
                                                class="mt-2 text-xs text-slate-400"
                                            >
                                                No due date
                                            </p>
                                        </div>

                                        <div
                                            class="flex flex-wrap gap-2"
                                        >
                                            <button
                                                type="button"
                                                @click="completeTask(task)"
                                                class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white"
                                            >
                                                ✓ Complete
                                            </button>

                                            <button
                                                type="button"
                                                @click="startEditing(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-slate-200"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                type="button"
                                                @click="deleteTask(task)"
                                                class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-red-600 ring-1 ring-slate-200"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <form
                                        @submit.prevent="saveEdit(task)"
                                    >
                                        <textarea
                                            v-model="editForm.description"
                                            rows="3"
                                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none"
                                        ></textarea>

                                        <input
                                            v-model="editForm.due_at"
                                            type="date"
                                            class="mt-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm"
                                        />

                                        <div
                                            class="mt-3 flex gap-2"
                                        >
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
                </div>
            </main>
        </div>
    </div>
</template>