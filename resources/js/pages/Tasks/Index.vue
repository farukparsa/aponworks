<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AponworksLayout from '@/layouts/aponworks/AponworksLayout.vue';

type Task = {
    id: number;
    title: string | null;
    description: string;
    status: string;
    due_at: string | null;
    created_at: string;
    updated_at: string;
};

const props = defineProps<{ tasks: Task[] }>();
const page = usePage();
const taskTextarea = ref<HTMLTextAreaElement | null>(null);
const editingTaskId = ref<number | null>(null);

const taskForm = useForm({ type: 'task', description: '', due_at: '' });
const editForm = useForm({ description: '', due_at: '' });

const localDateString = (date: Date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const todayDate = computed(() => localDateString(new Date()));
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

const taskDate = (task: Task) => task.due_at ? task.due_at.substring(0, 10) : null;
const overdueTasks = computed(() => props.tasks.filter((task) => {
    const due = taskDate(task);
    return due && due < todayDate.value;
}));
const todayTasks = computed(() => props.tasks.filter((task) => taskDate(task) === todayDate.value));
const tomorrowTasks = computed(() => props.tasks.filter((task) => taskDate(task) === tomorrowDate.value));
const dayAfterTomorrowTasks = computed(() => props.tasks.filter((task) => taskDate(task) === dayAfterTomorrowDate.value));
const upcomingTasks = computed(() => props.tasks.filter((task) => {
    const due = taskDate(task);
    return due && due > dayAfterTomorrowDate.value;
}));
const noDateTasks = computed(() => props.tasks.filter((task) => !task.due_at));
const hasTasks = computed(() => props.tasks.length > 0);

const groups = computed(() => [
    { key: 'overdue', title: 'Overdue', subtitle: 'Tasks that passed their due date', tasks: overdueTasks.value, tone: 'danger' },
    { key: 'today', title: 'Today', subtitle: 'Focus on what needs attention today', tasks: todayTasks.value, tone: 'today' },
    { key: 'tomorrow', title: 'Tomorrow', subtitle: 'Tasks planned for tomorrow', tasks: tomorrowTasks.value, tone: 'normal' },
    { key: 'day-after', title: 'Day After Tomorrow', subtitle: 'Tasks for the following day', tasks: dayAfterTomorrowTasks.value, tone: 'normal' },
    { key: 'upcoming', title: 'Upcoming', subtitle: 'Future tasks already on your schedule', tasks: upcomingTasks.value, tone: 'normal' },
    { key: 'no-date', title: 'No Date', subtitle: 'Tasks waiting without a due date', tasks: noDateTasks.value, tone: 'quiet' },
].filter((group) => group.tasks.length > 0));

const addTask = () => {
    taskForm.post('/memories', {
        preserveScroll: true,
        onSuccess: () => taskForm.reset('description', 'due_at'),
    });
};

const completeTask = (task: Task) => {
    router.patch(`/memories/${task.id}/complete`, {}, { preserveScroll: true });
};

const startEditing = (task: Task) => {
    editingTaskId.value = task.id;
    editForm.description = task.description;
    editForm.due_at = task.due_at ? task.due_at.substring(0, 10) : '';
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
    if (!window.confirm('Move this task to BIN?')) return;
    router.delete(`/memories/${task.id}`, { preserveScroll: true });
};

const formatDate = (value: string | null) => {
    if (!value) return '';
    const [year, month, day] = value.substring(0, 10).split('-');
    return new Date(Number(year), Number(month) - 1, Number(day)).toLocaleDateString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const taskMeta = (task: Task, key: string) => {
    if (key === 'today') return 'Today';
    if (key === 'tomorrow') return 'Tomorrow';
    if (key === 'no-date') return 'No due date';
    return task.due_at ? formatDate(task.due_at) : 'No due date';
};

const focusCreateTask = async () => {
    await nextTick();
    taskTextarea.value?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    taskTextarea.value?.focus();
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('create') === '1') focusCreateTask();
});
</script>

<template>
    <Head title="Tasks - APONWORKS" />

    <AponworksLayout
        active-page="tasks"
        page-title="Tasks"
        page-subtitle="Plan, manage and complete what matters."
        :user-name="String((page.props.auth as any)?.user?.name ?? 'User')"
    >
        <div class="tasks-v4">
            <section class="task-hero">
                <div>
                    <span class="eyebrow">TASK WORKSPACE</span>
                    <h1>Tasks</h1>
                    <p>Keep your work clear, dated and easy to finish — without losing sight of what comes next.</p>
                </div>

                <div class="hero-actions">
                    <Link href="/tasks/completed" class="completed-link">✓ Completed</Link>
                    <button type="button" class="new-task-button" @click="focusCreateTask">＋ New Task</button>
                </div>
            </section>

            <section class="task-summary">
                <div class="summary-item">
                    <span>ACTIVE</span>
                    <strong>{{ tasks.length }}</strong>
                    <small>Total tasks</small>
                </div>
                <div class="summary-item danger">
                    <span>OVERDUE</span>
                    <strong>{{ overdueTasks.length }}</strong>
                    <small>Need attention</small>
                </div>
                <div class="summary-item today">
                    <span>TODAY</span>
                    <strong>{{ todayTasks.length }}</strong>
                    <small>Due today</small>
                </div>
                <div class="summary-item">
                    <span>UPCOMING</span>
                    <strong>{{ upcomingTasks.length + tomorrowTasks.length + dayAfterTomorrowTasks.length }}</strong>
                    <small>Scheduled ahead</small>
                </div>
            </section>

            <section class="create-card">
                <form @submit.prevent="addTask">
                    <div class="create-head">
                        <div>
                            <span class="eyebrow">QUICK CREATE</span>
                            <h2>Add a task</h2>
                        </div>
                        <span class="new-pill">New</span>
                    </div>

                    <textarea
                        ref="taskTextarea"
                        v-model="taskForm.description"
                        rows="2"
                        placeholder="What do you need to do?"
                        class="task-input"
                    ></textarea>

                    <p v-if="taskForm.errors.description" class="error-text">
                        {{ taskForm.errors.description }}
                    </p>

                    <div class="create-footer">
                        <label class="date-field">
                            <span>Due date <em>optional</em></span>
                            <input v-model="taskForm.due_at" type="date" />
                        </label>

                        <div class="create-actions">
                            <button type="button" class="soft-button" title="Coming later">⌕ File</button>
                            <button type="button" class="soft-button" title="Coming later">▣ Photo</button>
                            <button type="button" class="soft-button" title="Coming later">◉ Voice</button>
                            <button
                                type="submit"
                                class="add-task"
                                :disabled="taskForm.processing || !taskForm.description.trim()"
                            >
                                {{ taskForm.processing ? 'Adding...' : '+ Add Task' }}
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <section v-if="!hasTasks" class="empty-state">
                <div class="empty-icon">✓</div>
                <h2>No active tasks</h2>
                <p>Add your first task above. APONWORKS will organize it automatically by date.</p>
            </section>

            <div v-else class="task-groups">
                <section
                    v-for="group in groups"
                    :key="group.key"
                    class="group-card"
                    :class="`tone-${group.tone}`"
                >
                    <header class="group-head">
                        <div>
                            <span class="group-kicker">{{ group.key === 'overdue' ? 'ATTENTION' : 'SCHEDULE' }}</span>
                            <h2>{{ group.title }}</h2>
                            <p>{{ group.subtitle }}</p>
                        </div>
                        <span class="count-pill">{{ group.tasks.length }}</span>
                    </header>

                    <div class="task-list">
                        <article v-for="task in group.tasks" :key="task.id" class="task-row">
                            <template v-if="editingTaskId !== task.id">
                                <button
                                    type="button"
                                    class="complete-circle"
                                    title="Complete task"
                                    @click="completeTask(task)"
                                >
                                    ✓
                                </button>

                                <div class="task-copy">
                                    <h3>{{ task.title || task.description }}</h3>
                                    <p v-if="task.title && task.description">{{ task.description }}</p>
                                </div>

                                <div class="task-date">
                                    <span>{{ taskMeta(task, group.key) }}</span>
                                </div>

                                <div class="task-actions">
                                    <button type="button" class="action-button" @click="startEditing(task)">Edit</button>
                                    <button type="button" class="more-button" title="Move to BIN" @click="deleteTask(task)">⋮</button>
                                </div>
                            </template>

                            <form v-else class="edit-form" @submit.prevent="saveEdit(task)">
                                <textarea v-model="editForm.description" rows="2"></textarea>
                                <input v-model="editForm.due_at" type="date" />
                                <div class="edit-actions">
                                    <button type="submit" class="save-button">Save</button>
                                    <button type="button" class="cancel-button" @click="cancelEditing">Cancel</button>
                                </div>
                            </form>
                        </article>
                    </div>
                </section>
            </div>
        </div>
    </AponworksLayout>
</template>

<style scoped>
.tasks-v4{max-width:1500px;margin:0 auto;padding:4px 0 38px;color:#1f2a44}
.task-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;padding:10px 5px 18px}
.eyebrow,.group-kicker{display:block;color:#9098a9;font-size:9px;font-weight:850;letter-spacing:.16em}
.task-hero h1{margin-top:6px;font-size:clamp(32px,3.2vw,45px);font-weight:850;letter-spacing:-.045em;color:#172036}
.task-hero p{max-width:680px;margin-top:7px;color:#778298;font-size:12px;line-height:1.65}
.hero-actions{display:flex;align-items:center;gap:9px}
.completed-link,.new-task-button{height:42px;display:inline-flex;align-items:center;justify-content:center;border-radius:13px;padding:0 15px;font-size:10px;font-weight:800;transition:.18s}
.completed-link{border:1px solid #dfe3ea;background:#fff;color:#667187}
.new-task-button{border:1px solid rgba(99,102,241,.16);background:linear-gradient(135deg,#6975ed,#7768e8);color:#fff;box-shadow:0 10px 22px rgba(99,102,241,.17)}
.completed-link:hover,.new-task-button:hover{transform:translateY(-1px)}
.task-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:13px}
.summary-item{min-height:92px;border:1px solid #e1e5eb;border-radius:17px;background:rgba(255,255,255,.9);padding:15px 17px;box-shadow:0 7px 20px rgba(51,65,85,.03)}
.summary-item span{color:#969fb0;font-size:8px;font-weight:850;letter-spacing:.12em}.summary-item strong{display:block;margin-top:6px;color:#26324a;font-size:23px;line-height:1;font-weight:850}.summary-item small{display:block;margin-top:6px;color:#8993a5;font-size:8px;font-weight:650}
.summary-item.danger strong{color:#d65c62}.summary-item.today strong{color:#626be2}
.create-card,.group-card,.empty-state{border:1px solid #e1e5eb;border-radius:20px;background:rgba(255,255,255,.94);box-shadow:0 8px 24px rgba(51,65,85,.035)}
.create-card{margin-bottom:14px;padding:18px}
.create-head{display:flex;align-items:center;justify-content:space-between}.create-head h2{margin-top:4px;color:#202b43;font-size:15px;font-weight:850}.new-pill,.count-pill{border-radius:999px;background:#f1f2f7;padding:5px 9px;color:#727d91;font-size:8px;font-weight:800}
.task-input{width:100%;margin-top:14px;resize:none;border:1px solid #e1e5eb;border-radius:15px;background:#f8f9fb;padding:13px 14px;color:#273249;font-size:11px;line-height:1.55;outline:none;transition:.18s}
.task-input:focus{border-color:#bfc4ee;background:#fff;box-shadow:0 0 0 3px rgba(99,102,241,.06)}
.task-input::placeholder{color:#a0a8b7}.error-text{margin-top:6px;color:#d5525b;font-size:9px}
.create-footer{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-top:12px}
.date-field{display:grid;gap:5px;color:#7c879a;font-size:8px;font-weight:750}.date-field em{font-style:normal;color:#a4acb9;font-weight:600}.date-field input{height:35px;border:1px solid #e0e4ea;border-radius:10px;background:#fff;padding:0 10px;color:#647087;font-size:9px;outline:none}
.create-actions{display:flex;align-items:center;gap:7px;flex-wrap:wrap}.soft-button,.add-task{height:35px;border-radius:10px;padding:0 11px;font-size:9px;font-weight:800;transition:.18s}.soft-button{border:1px solid #e4e7ec;background:#f7f8fa;color:#778196}.soft-button:hover{background:#fff;border-color:#d5d9e1}.add-task{border:0;background:#151c2e;color:#fff;padding:0 15px}.add-task:disabled{opacity:.45;cursor:not-allowed}
.task-groups{display:grid;gap:13px}.group-card{overflow:hidden}.group-head{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 17px 13px;border-bottom:1px solid #edf0f4}.group-head h2{margin-top:3px;color:#202b43;font-size:14px;font-weight:850}.group-head p{margin-top:3px;color:#8a94a6;font-size:9px}.tone-danger .group-kicker{color:#d16b70}.tone-today .group-kicker{color:#656ee3}
.task-list{display:grid}.task-row{display:grid;grid-template-columns:34px minmax(0,1fr) 120px auto;gap:12px;align-items:center;min-height:68px;padding:11px 15px;border-bottom:1px solid #eef0f4;transition:.16s}.task-row:last-child{border-bottom:0}.task-row:hover{background:#fafbfc}
.complete-circle{width:29px;height:29px;border:1px solid #dce1e8;border-radius:10px;background:#f8f9fb;color:#9aa4b5;font-size:11px;font-weight:850;transition:.18s}.complete-circle:hover{border-color:#bde4d3;background:#eaf9f2;color:#13a56d}
.task-copy{min-width:0}.task-copy h3{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#273249;font-size:11px;font-weight:800}.task-copy p{margin-top:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#8a94a6;font-size:8px}
.task-date{text-align:right;color:#7d8799;font-size:8px;font-weight:700;white-space:nowrap}.task-actions{display:flex;align-items:center;gap:6px}.action-button,.more-button{height:31px;border:1px solid #e1e5eb;border-radius:9px;background:#fff;color:#6c778b;font-size:8px;font-weight:800}.action-button{padding:0 10px}.more-button{width:31px;font-size:15px}.action-button:hover,.more-button:hover{background:#f6f7f9;color:#303b51}
.edit-form{grid-column:1/-1;display:grid;grid-template-columns:minmax(0,1fr) 150px auto;gap:9px;align-items:center}.edit-form textarea,.edit-form input{border:1px solid #dfe3ea;border-radius:10px;background:#fff;padding:9px 10px;color:#354057;font-size:9px;outline:none}.edit-form textarea{resize:none}.edit-actions{display:flex;gap:6px}.save-button,.cancel-button{height:33px;border-radius:9px;padding:0 11px;font-size:8px;font-weight:800}.save-button{border:0;background:#20283a;color:#fff}.cancel-button{border:1px solid #e1e5eb;background:#fff;color:#748095}
.empty-state{min-height:270px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}.empty-icon{width:46px;height:46px;display:flex;align-items:center;justify-content:center;border-radius:14px;background:#edf9f3;color:#15a46d;font-size:16px;font-weight:850}.empty-state h2{margin-top:12px;font-size:14px;font-weight:850}.empty-state p{max-width:380px;margin-top:5px;color:#8a94a6;font-size:10px;line-height:1.6}
@media(max-width:900px){.task-summary{grid-template-columns:1fr 1fr}.create-footer{align-items:flex-start;flex-direction:column}.task-row{grid-template-columns:34px minmax(0,1fr) auto}.task-date{display:none}.edit-form{grid-template-columns:1fr}}
@media(max-width:600px){.task-hero{display:block}.hero-actions{margin-top:14px}.task-summary{grid-template-columns:1fr 1fr}.summary-item{min-height:82px}.create-actions{width:100%}.soft-button{display:none}.add-task{margin-left:auto}.task-row{grid-template-columns:30px minmax(0,1fr) auto;padding:10px}.action-button{display:none}.group-head{padding:14px}.task-hero h1{font-size:31px}}
@media(prefers-reduced-motion:reduce){.completed-link,.new-task-button,.task-input,.soft-button,.complete-circle,.task-row{transition:none}}
</style>
