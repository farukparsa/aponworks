<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AponworksLayout from '@/layouts/aponworks/AponworksLayout.vue';

type Memory = {
    id: number;
    type: 'task' | 'note' | 'diary';
    title: string | null;
    description: string;
    status: string;
    due_at: string | null;
    deleted_at: string | null;
    created_at: string;
    updated_at: string;
};

const page = usePage();

const memories = computed<Memory[]>(() => {
    return (page.props.memories as Memory[]) ?? [];
});

const currentView = computed<string>(() => {
    return (page.props.currentView as string) ?? 'all';
});

const taskCount = computed(() => memories.value.filter((memory) => memory.type === 'task').length);
const noteCount = computed(() => memories.value.filter((memory) => memory.type === 'note').length);
const diaryCount = computed(() => memories.value.filter((memory) => memory.type === 'diary').length);

const calendarCursor = ref(new Date());

const calendarTitle = computed(() =>
    calendarCursor.value.toLocaleDateString(undefined, {
        month: 'long',
        year: 'numeric',
    }),
);

const calendarDays = computed(() => {
    const year = calendarCursor.value.getFullYear();
    const month = calendarCursor.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const mondayOffset = (firstDay.getDay() + 6) % 7;
    const cells: Array<{
        key: string;
        day: number | null;
        dateKey: string | null;
        count: number;
        isToday: boolean;
    }> = [];

    for (let index = 0; index < mondayOffset; index += 1) {
        cells.push({
            key: `blank-${index}`,
            day: null,
            dateKey: null,
            count: 0,
            isToday: false,
        });
    }

    const today = new Date();

    for (let day = 1; day <= lastDay.getDate(); day += 1) {
        const date = new Date(year, month, day);
        const dateKey = [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0'),
        ].join('-');

        const count = memories.value.filter((memory) => {
            if (!memory.due_at) return false;
            const due = new Date(memory.due_at);
            const dueKey = [
                due.getFullYear(),
                String(due.getMonth() + 1).padStart(2, '0'),
                String(due.getDate()).padStart(2, '0'),
            ].join('-');
            return dueKey === dateKey;
        }).length;

        cells.push({
            key: dateKey,
            day,
            dateKey,
            count,
            isToday:
                today.getFullYear() === year &&
                today.getMonth() === month &&
                today.getDate() === day,
        });
    }

    return cells;
});

const previousMonth = () => {
    calendarCursor.value = new Date(
        calendarCursor.value.getFullYear(),
        calendarCursor.value.getMonth() - 1,
        1,
    );
};

const nextMonth = () => {
    calendarCursor.value = new Date(
        calendarCursor.value.getFullYear(),
        calendarCursor.value.getMonth() + 1,
        1,
    );
};

const isDeletedView = computed(() => {
    return [
        'deleted-tasks',
        'deleted-diary',
        'deleted-notes',
    ].includes(currentView.value);
});

const pageTitle = computed(() => {
    switch (currentView.value) {
        case 'completed':
            return 'Completed Tasks';
        case 'deleted-tasks':
            return 'Deleted Tasks';
        case 'deleted-diary':
            return 'Deleted Diary';
        case 'deleted-notes':
            return 'Deleted Notes';
        default:
            return 'All Memories';
    }
});

const moveToBin = (memoryId: number) => {
    const confirmed = window.confirm('Move this item to BIN?');

    if (!confirmed) {
        return;
    }

    router.delete(`/memories/${memoryId}`);
};

const reopenTask = (memoryId: number) => {
    router.patch(`/memories/${memoryId}/reopen`);
};

const formatDate = (date: string | null) => {
    if (!date) {
        return '';
    }

    return new Date(date).toLocaleDateString();
};
</script>


<template>
    <Head :title="pageTitle" />

    <AponworksLayout
        active-page="all"
        :page-title="pageTitle"
        page-subtitle="Your saved APONWORKS items in one place."
        :user-name="String((page.props.auth as any)?.user?.name ?? 'User')"
    >
        <div class="memory-v4">
            <section class="page-intro">
                <div class="intro-copy">
                    <span class="eyebrow">
                        {{ isDeletedView ? 'BIN' : currentView === 'completed' ? 'HISTORY' : 'MEMORY LIBRARY' }}
                    </span>
                    <h1>{{ pageTitle }}</h1>
                    <p>
                        {{
                            isDeletedView
                                ? 'Review deleted items and open one when you need to restore it.'
                                : currentView === 'completed'
                                  ? 'A clean record of the tasks you have already finished.'
                                  : 'Your thoughts, plans and important things — organized in one calm workspace.'
                        }}
                    </p>
                </div>

                <button
                    v-if="!isDeletedView && currentView === 'all'"
                    type="button"
                    class="add-button"
                    @click="window.dispatchEvent(new CustomEvent('aponworks-open-create'))"
                >
                    <span>＋</span>
                    Add New
                </button>
            </section>

            <div class="workspace">
                <main class="content-column">
                    <nav class="filter-bar" aria-label="Memory filters">
                        <Link href="/memories" :class="{ active: currentView === 'all' }">
                            <span class="filter-icon">◇</span> All
                        </Link>
                        <Link href="/tasks"><span class="filter-icon task">✓</span> Tasks</Link>
                        <Link href="/notes"><span class="filter-icon note">▤</span> Notes</Link>
                        <Link href="/diary"><span class="filter-icon diary">▢</span> Diary</Link>
                        <Link href="/tasks/completed" :class="{ active: currentView === 'completed' }">
                            <span class="filter-icon">○</span> Completed
                        </Link>
                    </nav>

                    <section v-if="memories.length === 0" class="empty-state">
                        <div class="empty-symbol">{{ isDeletedView ? '⌫' : currentView === 'completed' ? '✓' : '✦' }}</div>
                        <h2>No items here</h2>
                        <p>
                            {{
                                isDeletedView
                                    ? 'There are no deleted items in this category.'
                                    : currentView === 'completed'
                                      ? 'You have no completed tasks yet.'
                                      : 'Your tasks, notes and diary entries will appear here.'
                            }}
                        </p>
                    </section>

                    <section v-else class="memory-list">
                        <article
                            v-for="memory in memories"
                            :key="memory.id"
                            class="memory-row"
                            :class="`type-${memory.type}`"
                        >
                            <div class="type-tile">
                                <span>{{ memory.type === 'task' ? '✓' : memory.type === 'diary' ? '▢' : '▤' }}</span>
                            </div>

                            <div class="memory-copy">
                                <div class="memory-labels">
                                    <span class="type-label">{{ memory.type }}</span>
                                    <span v-if="currentView === 'completed'" class="complete-label">Completed</span>
                                    <span v-if="isDeletedView && memory.deleted_at" class="deleted-label">
                                        Deleted {{ formatDate(memory.deleted_at) }}
                                    </span>
                                </div>

                                <h2>{{ memory.title || memory.description }}</h2>
                                <p v-if="memory.title && memory.description" class="description">
                                    {{ memory.description }}
                                </p>

                                <div class="date-line">
                                    <span v-if="memory.due_at">
                                        <span class="date-icon">□</span> Due {{ formatDate(memory.due_at) }}
                                    </span>
                                    <span>
                                        <span class="date-icon">◷</span> Saved {{ formatDate(memory.created_at) }}
                                    </span>
                                </div>
                            </div>

                            <div class="row-actions">
                                <Link
                                    v-if="isDeletedView"
                                    :href="`/memories/deleted/${memory.id}`"
                                    class="open-button"
                                >
                                    Open <span>→</span>
                                </Link>

                                <template v-else-if="currentView === 'completed'">
                                    <button type="button" class="reopen-button" @click="reopenTask(memory.id)">
                                        ↩ Reopen
                                    </button>
                                    <button type="button" class="more-button" title="Move to BIN" @click="moveToBin(memory.id)">
                                        ⋮
                                    </button>
                                </template>

                                <button
                                    v-else
                                    type="button"
                                    class="more-button"
                                    title="Move to BIN"
                                    @click="moveToBin(memory.id)"
                                >
                                    ⋮
                                </button>
                            </div>
                        </article>
                    </section>
                </main>

                <aside class="side-column">
                    <section class="side-card">
                        <div class="side-title">
                            <div>
                                <span class="side-kicker">OVERVIEW</span>
                                <h2>Quick Stats</h2>
                            </div>
                            <span class="side-mark">⌁</span>
                        </div>

                        <div class="stat-grid">
                            <div class="stat total">
                                <span class="stat-icon">◇</span>
                                <strong>{{ memories.length }}</strong>
                                <small>Total Items</small>
                            </div>
                            <div class="stat tasks">
                                <span class="stat-icon">✓</span>
                                <strong>{{ taskCount }}</strong>
                                <small>Tasks</small>
                            </div>
                            <div class="stat notes">
                                <span class="stat-icon">▤</span>
                                <strong>{{ noteCount }}</strong>
                                <small>Notes</small>
                            </div>
                            <div class="stat diary">
                                <span class="stat-icon">▢</span>
                                <strong>{{ diaryCount }}</strong>
                                <small>Diary</small>
                            </div>
                        </div>
                    </section>

                    <section class="side-card calendar-card">
                        <div class="calendar-head">
                            <div>
                                <span class="side-kicker">SCHEDULE</span>
                                <h2>Calendar</h2>
                            </div>
                            <div class="calendar-controls">
                                <button type="button" aria-label="Previous month" @click="previousMonth">‹</button>
                                <span>{{ calendarTitle }}</span>
                                <button type="button" aria-label="Next month" @click="nextMonth">›</button>
                            </div>
                        </div>

                        <div class="weekdays" aria-hidden="true">
                            <span>Mon</span>
                            <span>Tue</span>
                            <span>Wed</span>
                            <span>Thu</span>
                            <span>Fri</span>
                            <span>Sat</span>
                            <span>Sun</span>
                        </div>

                        <div class="calendar-grid">
                            <div
                                v-for="cell in calendarDays"
                                :key="cell.key"
                                class="calendar-day"
                                :class="{
                                    blank: !cell.day,
                                    today: cell.isToday,
                                    'has-items': cell.count > 0,
                                }"
                                :title="cell.count > 0 ? `${cell.count} scheduled item${cell.count === 1 ? '' : 's'}` : ''"
                            >
                                <span v-if="cell.day">{{ cell.day }}</span>
                                <i v-if="cell.count > 0" aria-hidden="true"></i>
                            </div>
                        </div>

                        <div class="calendar-note">
                            <span class="calendar-dot"></span>
                            Dates with scheduled APONWORKS items
                        </div>
                    </section>

                    <section class="side-card quiet-card">
                        <span class="side-kicker">APONWORKS</span>
                        <h2>Everything in one place</h2>
                        <p>
                            Tasks, notes and diary entries stay connected to the same personal workspace.
                        </p>
                        <button
                            type="button"
                            class="ask-apon"
                            @click="window.dispatchEvent(new CustomEvent('aponworks-open-apon'))"
                        >
                            ✦ Ask APON <span>→</span>
                        </button>
                    </section>
                </aside>
            </div>
        </div>
    </AponworksLayout>
</template>

<style scoped>
.memory-v4{max-width:1500px;margin:0 auto;padding:2px 0 34px}
.page-intro{display:flex;align-items:center;justify-content:space-between;gap:24px;margin-bottom:20px;padding:8px 5px 2px}
.eyebrow,.side-kicker{display:block;color:#8b93a5;font-size:9px;font-weight:800;letter-spacing:.16em}
.intro-copy h1{margin-top:6px;color:#172036;font-size:clamp(31px,3.1vw,44px);font-weight:800;letter-spacing:-.045em}
.intro-copy p{max-width:690px;margin-top:6px;color:#748097;font-size:12px;line-height:1.65}
.add-button{display:inline-flex;height:45px;align-items:center;gap:9px;border:1px solid rgba(99,102,241,.18);border-radius:14px;background:linear-gradient(135deg,#6975ed,#7768e8);padding:0 20px;color:#fff;font-size:11px;font-weight:800;box-shadow:0 12px 26px rgba(99,102,241,.18);transition:.2s}
.add-button:hover{transform:translateY(-1px);box-shadow:0 15px 32px rgba(99,102,241,.22)}
.add-button span{font-size:17px;font-weight:400}
.workspace{display:grid;grid-template-columns:minmax(0,1fr) 290px;gap:18px;align-items:start}
.content-column{min-width:0}
.filter-bar{display:flex;gap:4px;overflow-x:auto;margin-bottom:13px;border:1px solid #e2e5eb;border-radius:17px;background:rgba(255,255,255,.9);padding:5px;box-shadow:0 8px 24px rgba(51,65,85,.035)}
.filter-bar a{display:inline-flex;align-items:center;gap:7px;white-space:nowrap;border-radius:12px;padding:9px 14px;color:#647087;font-size:10px;font-weight:750;transition:.18s}
.filter-bar a:hover{background:#f4f5f8;color:#293349}
.filter-bar a.active{background:linear-gradient(135deg,#6a75ed,#7566e7);color:#fff;box-shadow:0 7px 17px rgba(99,102,241,.17)}
.filter-icon{font-size:12px}.filter-icon.task{color:#16a36a}.filter-icon.note{color:#e89516}.filter-icon.diary{color:#3983e7}.filter-bar a.active .filter-icon{color:#fff}
.memory-list{display:grid;gap:9px}
.memory-row{display:grid;grid-template-columns:50px minmax(0,1fr) auto;gap:14px;align-items:center;min-height:88px;border:1px solid #e2e5eb;border-radius:18px;background:rgba(255,255,255,.94);padding:14px 14px 14px 15px;box-shadow:0 7px 22px rgba(51,65,85,.035);transition:.2s}
.memory-row:hover{transform:translateY(-1px);border-color:#d9dde6;box-shadow:0 12px 27px rgba(51,65,85,.055)}
.type-tile{width:50px;height:50px;display:flex;align-items:center;justify-content:center;border-radius:15px;background:#f2f4f7;color:#697386;font-size:18px;font-weight:800}
.type-task .type-tile{background:linear-gradient(145deg,#effcf6,#e0f8ed);color:#12a56d}
.type-note .type-tile{background:linear-gradient(145deg,#fff9ee,#fff0d8);color:#f09a16}
.type-diary .type-tile{background:linear-gradient(145deg,#eff6ff,#e5f0ff);color:#2f80ed}
.memory-copy{min-width:0}.memory-labels{display:flex;align-items:center;gap:7px;flex-wrap:wrap}
.type-label,.complete-label{border-radius:999px;padding:3px 7px;font-size:7px;font-weight:850;text-transform:uppercase;letter-spacing:.09em}
.type-label{background:#f0f2f6;color:#697386}.complete-label{background:#e8f9f0;color:#15945f}.deleted-label{color:#9aa3b2;font-size:8px}
.memory-copy h2{margin-top:5px;overflow:hidden;text-overflow:ellipsis;color:#1f2a44;font-size:12px;font-weight:800;line-height:1.35}
.description{margin-top:3px;display:-webkit-box;overflow:hidden;-webkit-box-orient:vertical;-webkit-line-clamp:1;color:#657189;font-size:10px;line-height:1.45}
.date-line{display:flex;gap:12px;flex-wrap:wrap;margin-top:7px;color:#8d98aa;font-size:8px}.date-icon{margin-right:3px;color:#7c86a0}
.row-actions{display:flex;align-items:center;gap:7px}.more-button,.reopen-button,.open-button{height:34px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #e0e4ea;border-radius:10px;background:#fff;color:#68748a;font-size:9px;font-weight:800;transition:.18s}
.more-button{width:34px;font-size:17px}.reopen-button,.open-button{padding:0 11px}.open-button{background:#f6f7fb;color:#4f5c75}
.more-button:hover,.reopen-button:hover,.open-button:hover{border-color:#ccd2dc;background:#f7f8fa;color:#273249}
.side-column{display:grid;gap:13px;position:sticky;top:94px}
.side-card{border:1px solid #e1e4eb;border-radius:20px;background:rgba(255,255,255,.92);padding:17px;box-shadow:0 9px 26px rgba(51,65,85,.04)}
.side-title{display:flex;align-items:center;justify-content:space-between}.side-title h2,.quiet-card h2{margin-top:4px;color:#202b43;font-size:13px;font-weight:800}.side-mark{color:#6b70e8;font-size:18px}
.stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:13px}.stat{min-height:98px;display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px solid #edf0f4;border-radius:15px;background:linear-gradient(145deg,#fafbfc,#f4f6f9);text-align:center}
.stat-icon{font-size:15px}.stat strong{margin-top:5px;color:#25314a;font-size:20px;line-height:1;font-weight:850}.stat small{margin-top:5px;color:#8791a4;font-size:8px;font-weight:700}
.stat.total .stat-icon{color:#6d68e8}.stat.tasks .stat-icon{color:#13a66e}.stat.notes .stat-icon{color:#ed9818}.stat.diary .stat-icon{color:#2f80ed}

.calendar-card{padding:17px}
.calendar-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.calendar-head h2{margin-top:4px;color:#202b43;font-size:13px;font-weight:800}
.calendar-controls{display:flex;align-items:center;gap:5px;color:#647087;font-size:8px;font-weight:800}
.calendar-controls span{min-width:72px;text-align:center}
.calendar-controls button{width:24px;height:24px;display:flex;align-items:center;justify-content:center;border:1px solid #e3e6ec;border-radius:8px;background:#fafbfc;color:#6f7990;font-size:15px;line-height:1;transition:.18s}
.calendar-controls button:hover{border-color:#cfd4df;background:#fff;color:#4f5b72}
.weekdays,.calendar-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px}
.weekdays{margin-top:14px;margin-bottom:5px}
.weekdays span{text-align:center;color:#9aa3b2;font-size:7px;font-weight:800}
.calendar-day{position:relative;aspect-ratio:1;display:flex;align-items:center;justify-content:center;border-radius:9px;color:#59657b;font-size:8px;font-weight:750}
.calendar-day.blank{visibility:hidden}
.calendar-day:not(.blank):hover{background:#f4f5f9}
.calendar-day.today{background:linear-gradient(135deg,#6a75ed,#7768e8);color:#fff;box-shadow:0 5px 12px rgba(99,102,241,.17)}
.calendar-day.has-items:not(.today){background:#f5f6fb;color:#39445a}
.calendar-day i{position:absolute;bottom:4px;width:3px;height:3px;border-radius:999px;background:#6b70e8}
.calendar-day.today i{background:#fff}
.calendar-note{display:flex;align-items:center;gap:6px;margin-top:11px;padding-top:10px;border-top:1px solid #edf0f4;color:#8a94a6;font-size:7px}
.calendar-dot{width:5px;height:5px;border-radius:999px;background:#6b70e8}
.quiet-card{background:linear-gradient(145deg,rgba(255,255,255,.96),rgba(241,243,249,.96))}.quiet-card p{margin-top:7px;color:#7b8597;font-size:10px;line-height:1.6}.ask-apon{width:100%;height:38px;display:flex;align-items:center;justify-content:space-between;margin-top:14px;border:1px solid #e0e3eb;border-radius:12px;background:#fff;padding:0 12px;color:#5863d9;font-size:9px;font-weight:850}
.empty-state{min-height:360px;display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px dashed #d9dee7;border-radius:20px;background:rgba(255,255,255,.85);text-align:center}.empty-symbol{width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:15px;background:#f1f3f7;color:#6e7890;font-size:17px}.empty-state h2{margin-top:12px;color:#29354d;font-size:14px;font-weight:800}.empty-state p{max-width:350px;margin-top:5px;color:#8b95a7;font-size:10px;line-height:1.6}
@media(max-width:1100px){.workspace{grid-template-columns:1fr}.side-column{position:static;grid-template-columns:1fr 1fr}.stat-grid{grid-template-columns:repeat(4,1fr)}}
@media(max-width:720px){.page-intro{align-items:flex-start}.add-button{height:40px;padding:0 14px}.workspace{gap:13px}.memory-row{grid-template-columns:42px minmax(0,1fr) auto;gap:10px;min-height:78px;padding:12px}.type-tile{width:42px;height:42px;border-radius:13px;font-size:15px}.date-line{gap:7px}.side-column{grid-template-columns:1fr}.stat-grid{grid-template-columns:repeat(4,1fr)}}
@media(max-width:520px){.memory-v4{padding-top:0}.page-intro{display:block;margin-bottom:15px}.add-button{margin-top:14px}.intro-copy h1{font-size:30px}.filter-bar a{padding:8px 11px}.memory-row{grid-template-columns:38px minmax(0,1fr) auto;border-radius:16px}.type-tile{width:38px;height:38px}.description{display:none}.date-line span:last-child{display:none}.stat-grid{grid-template-columns:1fr 1fr}.reopen-button{padding:0 8px}}
@media(prefers-reduced-motion:reduce){.add-button,.memory-row,.more-button,.reopen-button,.open-button{transition:none}}
</style>
