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
        :user-name="user?.name || 'User'"
    >
        <div class="apon-dashboard">
            <!-- WELCOME + APON BRIEF -->
            <section class="hero-grid">
                <div class="welcome-card">
                    <div class="welcome-copy">
                        <span class="eyebrow">YOUR PERSONAL SECRETARY</span>
                        <h1>{{ greeting }}, {{ firstName }}.</h1>
                        <p>
                            Here is your day at a glance. Capture what matters and APON will
                            help keep the important things in view.
                        </p>

                        <div class="hero-actions">
                            <button
                                type="button"
                                class="primary-action"
                                @click="
                                    memoryForm.type = 'task';
                                    document
                                        .getElementById('memory-composer')
                                        ?.scrollIntoView({ behavior: 'smooth' });
                                "
                            >
                                <span>＋</span>
                                Add something
                            </button>

                            <button
                                type="button"
                                class="soft-action"
                                @click="
                                    document
                                        .getElementById('my-day')
                                        ?.scrollIntoView({ behavior: 'smooth' })
                                "
                            >
                                View my day
                            </button>
                        </div>
                    </div>

                    <div class="hero-visual" aria-hidden="true">
                        <div class="orbit orbit-one"></div>
                        <div class="orbit orbit-two"></div>

                        <div class="apon-orb">
                            <span>✦</span>
                            <small>APON</small>
                        </div>

                        <div class="floating-chip chip-one">✓ Tasks</div>
                        <div class="floating-chip chip-two">✎ Notes</div>
                        <div class="floating-chip chip-three">◫ Diary</div>
                    </div>
                </div>

                <button
                    type="button"
                    class="brief-card"
                    @click="
                        dispatchToast(
                            'Open APON from the top bar for your intelligence panel.',
                            'info',
                        )
                    "
                >
                    <div class="brief-top">
                        <div class="brief-icon">✦</div>
                        <span class="live-pill">APON BRIEF</span>
                    </div>

                    <h2>
                        {{
                            todayMemories.length + tomorrowMemories.length
                                ? 'Your day has movement.'
                                : 'Your day looks clear.'
                        }}
                    </h2>

                    <p v-if="todayMemories.length">
                        {{ todayMemories.length }}
                        item{{ todayMemories.length === 1 ? '' : 's' }}
                        need attention today.
                    </p>

                    <p v-else-if="tomorrowMemories.length">
                        Nothing due today. {{ tomorrowMemories.length }}
                        item{{ tomorrowMemories.length === 1 ? '' : 's' }}
                        coming tomorrow.
                    </p>

                    <p v-else>
                        No immediate schedule pressure. Add a task, note or diary entry
                        whenever something comes up.
                    </p>

                    <div class="brief-footer">
                        <span>Anything I should know?</span>
                        <span class="arrow">→</span>
                    </div>
                </button>
            </section>

            <!-- MY DAY -->
            <section id="my-day" class="section-block">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">MY DAY</span>
                        <h2>What matters now</h2>
                    </div>

                    <span class="date-chip">
                        {{
                            new Date().toLocaleDateString(undefined, {
                                weekday: 'short',
                                day: 'numeric',
                                month: 'short',
                            })
                        }}
                    </span>
                </div>

                <div class="metric-grid">
                    <div class="metric-card metric-today">
                        <div class="metric-icon">◉</div>
                        <div>
                            <strong>{{ todayMemories.length }}</strong>
                            <span>Today</span>
                        </div>
                        <div class="metric-line"></div>
                    </div>

                    <div class="metric-card metric-tomorrow">
                        <div class="metric-icon">↗</div>
                        <div>
                            <strong>{{ tomorrowMemories.length }}</strong>
                            <span>Tomorrow</span>
                        </div>
                        <div class="metric-line"></div>
                    </div>

                    <div class="metric-card metric-upcoming">
                        <div class="metric-icon">⌁</div>
                        <div>
                            <strong>{{ upcomingMemories.length }}</strong>
                            <span>Upcoming</span>
                        </div>
                        <div class="metric-line"></div>
                    </div>

                    <div class="metric-card metric-recent">
                        <div class="metric-icon">✦</div>
                        <div>
                            <strong>{{ recentMemories.length }}</strong>
                            <span>Recent</span>
                        </div>
                        <div class="metric-line"></div>
                    </div>
                </div>
            </section>

            <!-- QUICK ACTIONS -->
            <section class="quick-grid">
                <button
                    type="button"
                    class="quick-card task-card"
                    @click="
                        memoryForm.type = 'task';
                        document
                            .getElementById('memory-composer')
                            ?.scrollIntoView({ behavior: 'smooth' });
                    "
                >
                    <span class="quick-icon">✓</span>
                    <span class="quick-copy">
                        <b>New Task</b>
                        <small>Something to get done</small>
                    </span>
                    <span class="quick-arrow">→</span>
                </button>

                <button
                    type="button"
                    class="quick-card note-card"
                    @click="
                        memoryForm.type = 'note';
                        document
                            .getElementById('memory-composer')
                            ?.scrollIntoView({ behavior: 'smooth' });
                    "
                >
                    <span class="quick-icon">✎</span>
                    <span class="quick-copy">
                        <b>Quick Note</b>
                        <small>Remember an idea or fact</small>
                    </span>
                    <span class="quick-arrow">→</span>
                </button>

                <button
                    type="button"
                    class="quick-card diary-card"
                    @click="
                        memoryForm.type = 'diary';
                        document
                            .getElementById('memory-composer')
                            ?.scrollIntoView({ behavior: 'smooth' });
                    "
                >
                    <span class="quick-icon">◫</span>
                    <span class="quick-copy">
                        <b>Diary</b>
                        <small>Record your day privately</small>
                    </span>
                    <span class="quick-arrow">→</span>
                </button>

                <button
                    type="button"
                    class="quick-card document-card"
                    @click="
                        dispatchToast(
                            'Document capture will be added in the next stage.',
                            'info',
                        )
                    "
                >
                    <span class="quick-icon">▤</span>
                    <span class="quick-copy">
                        <b>Document</b>
                        <small>Capture and track expiry</small>
                    </span>
                    <span class="soon">SOON</span>
                </button>
            </section>

            <!-- QUICK CAPTURE -->
            <section id="memory-composer" class="capture-card">
                <form @submit.prevent="saveMemory">
                    <div class="capture-head">
                        <div>
                            <span class="eyebrow">QUICK CAPTURE</span>
                            <h2>{{ composerTitle }}</h2>
                        </div>

                        <div class="capture-tabs">
                            <button
                                type="button"
                                :class="{ active: memoryForm.type === 'task' }"
                                @click="memoryForm.type = 'task'"
                            >
                                Task
                            </button>

                            <button
                                type="button"
                                :class="{ active: memoryForm.type === 'note' }"
                                @click="memoryForm.type = 'note'"
                            >
                                Note
                            </button>

                            <button
                                type="button"
                                :class="{ active: memoryForm.type === 'diary' }"
                                @click="memoryForm.type = 'diary'"
                            >
                                Diary
                            </button>
                        </div>
                    </div>

                    <div class="capture-box">
                        <textarea
                            v-model="memoryForm.description"
                            rows="3"
                            :placeholder="composerPlaceholder"
                        ></textarea>

                        <p
                            v-if="memoryForm.errors.description"
                            class="form-error"
                        >
                            {{ memoryForm.errors.description }}
                        </p>

                        <div class="capture-tools">
                            <div class="tool-left">
                                <label class="date-control">
                                    <span>
                                        {{
                                            memoryForm.type === 'task'
                                                ? 'Due'
                                                : 'Date'
                                        }}
                                    </span>

                                    <input
                                        v-model="memoryForm.due_at"
                                        type="date"
                                    />
                                </label>

                                <button
                                    type="button"
                                    class="tool-button"
                                    @click="
                                        dispatchToast(
                                            'File attachment will be added in the next stage.',
                                            'info',
                                        )
                                    "
                                >
                                    ⌕ <span>File</span>
                                </button>

                                <button
                                    type="button"
                                    class="tool-button"
                                    @click="
                                        dispatchToast(
                                            'Photo capture will be added in the next stage.',
                                            'info',
                                        )
                                    "
                                >
                                    ▣ <span>Photo</span>
                                </button>

                                <button
                                    type="button"
                                    class="tool-button"
                                    @click="
                                        dispatchToast(
                                            'Voice capture will be added in the next stage.',
                                            'info',
                                        )
                                    "
                                >
                                    ◉ <span>Voice</span>
                                </button>
                            </div>

                            <button
                                type="submit"
                                class="save-button"
                                :disabled="
                                    memoryForm.processing ||
                                    !memoryForm.description.trim()
                                "
                            >
                                {{ saveButtonText }}
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <!-- SCHEDULE -->
            <section v-if="scheduleBlocks.length" class="section-block">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">SCHEDULE</span>
                        <h2>Coming up</h2>
                    </div>

                    <span class="muted-caption">
                        Only periods with items are shown
                    </span>
                </div>

                <div class="schedule-grid">
                    <article
                        v-for="block in scheduleBlocks"
                        :key="block.key"
                        class="schedule-card"
                    >
                        <header>
                            <div class="schedule-title">
                                <span class="schedule-icon">
                                    {{ block.icon }}
                                </span>

                                <div>
                                    <b>{{ block.title }}</b>
                                    <small>{{ block.subtitle }}</small>
                                </div>
                            </div>

                            <span class="count-pill">
                                {{ block.items.length }}
                            </span>
                        </header>

                        <div class="schedule-list">
                            <div
                                v-for="memory in block.items"
                                :key="memory.id"
                                class="schedule-item"
                            >
                                <span
                                    class="type-dot"
                                    :class="`type-${memory.type}`"
                                ></span>

                                <div class="schedule-copy">
                                    <div class="schedule-meta">
                                        <span>{{ memory.type }}</span>

                                        <span
                                            v-if="block.key === 'upcoming'"
                                        >
                                            {{ formatDate(memory.due_at) }}
                                        </span>
                                    </div>

                                    <p>{{ memory.description }}</p>
                                </div>

                                <button
                                    v-if="memory.type === 'task'"
                                    type="button"
                                    class="round-complete"
                                    title="Complete task"
                                    @click="completeMemory(memory.id)"
                                >
                                    ✓
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <!-- PLANNER + RECENT -->
            <section class="lower-grid">
                <div class="planner-card">
                    <div class="card-title-row">
                        <div>
                            <span class="eyebrow">PLANNER</span>
                            <h2>Look ahead</h2>
                        </div>

                        <span class="count-pill">
                            {{ customScheduleMemories.length }}
                        </span>
                    </div>

                    <form
                        class="planner-controls"
                        @submit.prevent="applyCustomSchedule"
                    >
                        <label>
                            <span>Next days</span>
                            <input
                                v-model.number="customDays"
                                type="number"
                                min="1"
                                step="1"
                            />
                        </label>

                        <label>
                            <span>Show</span>
                            <select v-model="customType">
                                <option value="all">Everything</option>
                                <option value="task">Tasks</option>
                                <option value="note">Notes</option>
                                <option value="diary">Diary</option>
                                <option value="expiry">Expiries</option>
                            </select>
                        </label>

                        <button type="submit">Apply</button>
                    </form>

                    <div
                        v-if="customScheduleMemories.length"
                        class="planner-list"
                    >
                        <div
                            v-for="memory in customScheduleMemories"
                            :key="`${customMemoryLabel(memory)}-${memory.id}`"
                            class="planner-item"
                        >
                            <div>
                                <span>
                                    {{ customMemoryLabel(memory) }}
                                </span>

                                <p>{{ memory.description }}</p>
                            </div>

                            <div class="planner-side">
                                <small>
                                    {{ customMemoryDate(memory) }}
                                </small>

                                <button
                                    v-if="memory.type === 'task'"
                                    type="button"
                                    @click="completeMemory(memory.id)"
                                >
                                    ✓
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-state">
                        <span>⌁</span>
                        <b>No matching items</b>
                        <small>
                            Change the range or type to look further ahead.
                        </small>
                    </div>
                </div>

                <div class="recent-card">
                    <div class="card-title-row">
                        <div>
                            <span class="eyebrow">RECENT</span>
                            <h2>Memory stream</h2>
                        </div>

                        <span class="count-pill">
                            {{ recentMemories.length }}
                        </span>
                    </div>

                    <div v-if="recentMemories.length" class="recent-list">
                        <div
                            v-for="memory in recentMemories"
                            :key="memory.id"
                            class="recent-item"
                        >
                            <div
                                class="recent-icon"
                                :class="`recent-${memory.type}`"
                            >
                                {{
                                    memory.type === 'task'
                                        ? '✓'
                                        : memory.type === 'diary'
                                          ? '◫'
                                          : '✎'
                                }}
                            </div>

                            <div>
                                <div class="recent-meta">
                                    <span>{{ memory.type }}</span>
                                    <small>
                                        {{ formatDate(memory.created_at) }}
                                    </small>
                                </div>

                                <p>{{ memory.description }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-state">
                        <span>✦</span>
                        <b>Your memory stream is ready</b>
                        <small>
                            New tasks, notes and diary entries will appear
                            here.
                        </small>
                    </div>
                </div>
            </section>
        </div>
    </AponworksLayout>
</template>

<style scoped>
.apon-dashboard {
    max-width: 1500px;
    margin: 0 auto;
    padding-bottom: 24px;
}

.hero-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(300px, 0.65fr);
    gap: 18px;
    margin-bottom: 26px;
}

.welcome-card {
    min-height: 310px;
    position: relative;
    overflow: hidden;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 330px;
    border: 1px solid #e6e9ef;
    border-radius: 32px;
    background: linear-gradient(
        135deg,
        #ffffff 0%,
        #fbfcff 55%,
        #eef2ff 100%
    );
    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.055);
}

.welcome-copy {
    padding: 38px 38px 34px;
    position: relative;
    z-index: 2;
}

.eyebrow {
    display: block;
    color: #8b95a7;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.17em;
}

.welcome-copy h1 {
    margin-top: 12px;
    max-width: 620px;
    color: #101828;
    font-size: clamp(30px, 3vw, 46px);
    line-height: 1.08;
    font-weight: 850;
    letter-spacing: -0.045em;
}

.welcome-copy p {
    max-width: 590px;
    margin-top: 14px;
    color: #667085;
    font-size: 14px;
    line-height: 1.75;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 26px;
}

.primary-action,
.soft-action {
    height: 44px;
    border-radius: 14px;
    padding: 0 17px;
    font-size: 12px;
    font-weight: 800;
    transition: 0.2s ease;
}

.primary-action {
    background: #171b26;
    color: #ffffff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14);
}

.soft-action {
    border: 1px solid #e2e7ef;
    background: rgba(255, 255, 255, 0.8);
    color: #475467;
}

.primary-action:hover,
.soft-action:hover {
    transform: translateY(-2px);
}

.hero-visual {
    position: relative;
    min-height: 300px;
}

.apon-orb {
    position: absolute;
    left: 50%;
    top: 50%;
    z-index: 3;
    width: 116px;
    height: 116px;
    transform: translate(-50%, -50%);
    border-radius: 36px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    background: linear-gradient(
        145deg,
        #6366f1,
        #7c3aed 55%,
        #4f46e5
    );
    box-shadow: 0 30px 60px rgba(79, 70, 229, 0.3);
    animation: floatOrb 5s ease-in-out infinite;
}

.apon-orb span {
    font-size: 30px;
}

.apon-orb small {
    margin-top: 5px;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: 0.2em;
}

.orbit {
    position: absolute;
    left: 50%;
    top: 50%;
    border: 1px solid rgba(99, 102, 241, 0.16);
    border-radius: 999px;
    transform: translate(-50%, -50%);
}

.orbit-one {
    width: 220px;
    height: 220px;
}

.orbit-two {
    width: 310px;
    height: 310px;
    border-style: dashed;
}

.floating-chip {
    position: absolute;
    z-index: 4;
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 13px;
    background: rgba(255, 255, 255, 0.88);
    padding: 8px 11px;
    color: #667085;
    font-size: 10px;
    font-weight: 800;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(10px);
}

.chip-one {
    left: 4%;
    top: 23%;
}

.chip-two {
    right: 3%;
    top: 34%;
}

.chip-three {
    left: 8%;
    bottom: 18%;
}

.brief-card {
    min-height: 310px;
    display: flex;
    flex-direction: column;
    text-align: left;
    padding: 28px;
    border-radius: 32px;
    color: #ffffff;
    background:
        radial-gradient(
            circle at 85% 10%,
            rgba(129, 140, 248, 0.38),
            transparent 32%
        ),
        linear-gradient(145deg, #151827, #25213c 60%, #312e81);
    box-shadow: 0 20px 50px rgba(30, 27, 75, 0.16);
    transition: 0.25s ease;
}

.brief-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 25px 60px rgba(30, 27, 75, 0.22);
}

.brief-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.brief-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.12);
    font-size: 18px;
}

.live-pill {
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.08);
    padding: 6px 9px;
    font-size: 8px;
    font-weight: 900;
    letter-spacing: 0.16em;
    color: rgba(255, 255, 255, 0.72);
}

.brief-card h2 {
    margin-top: 30px;
    font-size: 22px;
    line-height: 1.2;
    font-weight: 850;
    letter-spacing: -0.025em;
}

.brief-card p {
    margin-top: 10px;
    color: rgba(255, 255, 255, 0.62);
    font-size: 12px;
    line-height: 1.7;
}

.brief-footer {
    margin-top: auto;
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    color: rgba(255, 255, 255, 0.86);
    font-size: 11px;
    font-weight: 750;
}

.arrow {
    font-size: 16px;
}

.section-block {
    margin-bottom: 26px;
}

.section-heading,
.card-title-row {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 14px;
}

.section-heading h2,
.card-title-row h2 {
    margin-top: 4px;
    color: #182230;
    font-size: 20px;
    font-weight: 850;
    letter-spacing: -0.025em;
}

.date-chip,
.count-pill {
    border: 1px solid #e6e9ef;
    border-radius: 999px;
    background: #ffffff;
    padding: 7px 11px;
    color: #667085;
    font-size: 10px;
    font-weight: 750;
    box-shadow: 0 3px 10px rgba(15, 23, 42, 0.025);
}

.metric-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.metric-card {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 13px;
    min-height: 92px;
    border: 1px solid #e6e9ef;
    border-radius: 22px;
    background: #ffffff;
    padding: 17px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.035);
    transition: 0.2s ease;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
}

.metric-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 900;
}

.metric-card strong {
    display: block;
    color: #182230;
    font-size: 24px;
    line-height: 1;
    font-weight: 850;
}

.metric-card span {
    display: block;
    margin-top: 6px;
    color: #8a94a6;
    font-size: 10px;
    font-weight: 700;
}

.metric-today .metric-icon {
    background: #eef2ff;
    color: #4f46e5;
}

.metric-tomorrow .metric-icon {
    background: #ecfdf3;
    color: #039855;
}

.metric-upcoming .metric-icon {
    background: #fff7ed;
    color: #ea580c;
}

.metric-recent .metric-icon {
    background: #fdf2f8;
    color: #c026d3;
}

.metric-line {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 3px;
    opacity: 0.75;
}

.metric-today .metric-line {
    background: #6366f1;
}

.metric-tomorrow .metric-line {
    background: #34d399;
}

.metric-upcoming .metric-line {
    background: #fb923c;
}

.metric-recent .metric-line {
    background: #e879f9;
}

.quick-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 26px;
}

.quick-card {
    min-height: 100px;
    display: flex;
    align-items: center;
    gap: 13px;
    border: 1px solid #e6e9ef;
    border-radius: 22px;
    padding: 17px;
    text-align: left;
    transition: 0.22s ease;
}

.quick-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 34px rgba(15, 23, 42, 0.07);
}

.task-card {
    background: linear-gradient(135deg, #f0fdf4, #ffffff);
}

.note-card {
    background: linear-gradient(135deg, #eff6ff, #ffffff);
}

.diary-card {
    background: linear-gradient(135deg, #fff7ed, #ffffff);
}

.document-card {
    background: linear-gradient(135deg, #faf5ff, #ffffff);
}

.quick-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: none;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 5px 16px rgba(15, 23, 42, 0.06);
    font-weight: 900;
}

.quick-copy {
    min-width: 0;
    display: block;
}

.quick-copy b {
    display: block;
    color: #273142;
    font-size: 12px;
    font-weight: 850;
}

.quick-copy small {
    display: block;
    margin-top: 5px;
    color: #98a2b3;
    font-size: 9px;
    line-height: 1.35;
}

.quick-arrow {
    margin-left: auto;
    color: #98a2b3;
}

.soon {
    margin-left: auto;
    border-radius: 999px;
    background: #ffffff;
    padding: 5px 7px;
    color: #98a2b3;
    font-size: 7px;
    font-weight: 900;
    letter-spacing: 0.1em;
}

.capture-card,
.planner-card,
.recent-card {
    border: 1px solid #e6e9ef;
    border-radius: 28px;
    background: #ffffff;
    padding: 24px;
    box-shadow: 0 12px 35px rgba(15, 23, 42, 0.035);
}

.capture-card {
    margin-bottom: 26px;
}

.capture-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
}

.capture-head h2 {
    margin-top: 4px;
    color: #182230;
    font-size: 19px;
    font-weight: 850;
}

.capture-tabs {
    display: flex;
    border-radius: 13px;
    background: #f2f4f7;
    padding: 3px;
}

.capture-tabs button {
    border-radius: 10px;
    padding: 7px 11px;
    color: #98a2b3;
    font-size: 9px;
    font-weight: 800;
}

.capture-tabs button.active {
    background: #ffffff;
    color: #344054;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
}

.capture-box {
    border: 1px solid #e4e7ec;
    border-radius: 20px;
    background: #fafbfc;
    padding: 15px;
    transition: 0.2s ease;
}

.capture-box:focus-within {
    border-color: #c7d2fe;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.06);
}

.capture-box textarea {
    width: 100%;
    resize: none;
    border: 0;
    background: transparent;
    color: #344054;
    font-size: 14px;
    line-height: 1.7;
    outline: 0;
}

.form-error {
    margin-top: 7px;
    color: #e11d48;
    font-size: 10px;
}

.capture-tools {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 12px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #eaecf0;
}

.tool-left {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    gap: 7px;
}

.date-control span {
    display: block;
    margin-bottom: 4px;
    color: #98a2b3;
    font-size: 8px;
    font-weight: 850;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.date-control input,
.tool-button {
    height: 35px;
    border: 1px solid #e4e7ec;
    border-radius: 10px;
    background: #ffffff;
    padding: 0 10px;
    color: #667085;
    font-size: 9px;
    font-weight: 750;
}

.save-button {
    height: 38px;
    border-radius: 11px;
    background: #171b26;
    padding: 0 15px;
    color: #ffffff;
    font-size: 10px;
    font-weight: 850;
    box-shadow: 0 7px 16px rgba(15, 23, 42, 0.12);
    transition: 0.2s;
}

.save-button:hover {
    transform: translateY(-1px);
}

.save-button:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.muted-caption {
    color: #98a2b3;
    font-size: 9px;
}

.schedule-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}

.schedule-card {
    border: 1px solid #e6e9ef;
    border-radius: 25px;
    background: #ffffff;
    padding: 19px;
    box-shadow: 0 8px 26px rgba(15, 23, 42, 0.035);
}

.schedule-card header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.schedule-title {
    display: flex;
    align-items: center;
    gap: 11px;
}

.schedule-icon {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 13px;
    background: #f2f4f7;
    color: #475467;
    font-size: 12px;
    font-weight: 900;
}

.schedule-title b {
    display: block;
    color: #273142;
    font-size: 11px;
}

.schedule-title small {
    display: block;
    margin-top: 3px;
    color: #98a2b3;
    font-size: 8px;
}

.schedule-list {
    margin-top: 14px;
    display: grid;
    gap: 7px;
}

.schedule-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    border-radius: 15px;
    background: #f8fafc;
    padding: 12px;
}

.type-dot {
    width: 7px;
    height: 7px;
    flex: none;
    margin-top: 6px;
    border-radius: 99px;
    background: #94a3b8;
}

.type-task {
    background: #22c55e;
}

.type-note {
    background: #3b82f6;
}

.type-diary {
    background: #f59e0b;
}

.schedule-copy {
    min-width: 0;
    flex: 1;
}

.schedule-meta {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    color: #98a2b3;
    font-size: 7px;
    font-weight: 850;
    text-transform: uppercase;
    letter-spacing: 0.09em;
}

.schedule-copy p {
    margin-top: 5px;
    color: #475467;
    font-size: 10px;
    line-height: 1.55;
}

.round-complete {
    width: 27px;
    height: 27px;
    flex: none;
    border: 1px solid #e4e7ec;
    border-radius: 9px;
    background: #ffffff;
    color: #667085;
    font-size: 9px;
    font-weight: 900;
    transition: 0.2s;
}

.round-complete:hover {
    background: #ecfdf3;
    color: #039855;
    border-color: #a7f3d0;
}

.lower-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(300px, 0.65fr);
    gap: 16px;
}

.planner-controls {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 9px;
    border-radius: 17px;
    background: #f8fafc;
    padding: 12px;
}

.planner-controls label span {
    display: block;
    margin-bottom: 4px;
    color: #98a2b3;
    font-size: 8px;
    font-weight: 800;
}

.planner-controls input,
.planner-controls select {
    height: 36px;
    width: 100%;
    border: 1px solid #e4e7ec;
    border-radius: 10px;
    background: #ffffff;
    padding: 0 9px;
    color: #667085;
    font-size: 9px;
    outline: 0;
}

.planner-controls button {
    align-self: end;
    height: 36px;
    border-radius: 10px;
    background: #171b26;
    padding: 0 14px;
    color: #ffffff;
    font-size: 9px;
    font-weight: 850;
}

.planner-list,
.recent-list {
    display: grid;
    gap: 7px;
    margin-top: 12px;
}

.planner-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    border-bottom: 1px solid #f0f2f5;
    padding: 10px 3px;
}

.planner-item > div > span {
    color: #98a2b3;
    font-size: 7px;
    font-weight: 850;
    text-transform: uppercase;
}

.planner-item p {
    margin-top: 3px;
    color: #475467;
    font-size: 10px;
    line-height: 1.45;
}

.planner-side {
    display: flex;
    align-items: center;
    gap: 7px;
    flex: none;
}

.planner-side small {
    color: #98a2b3;
    font-size: 8px;
}

.planner-side button {
    width: 25px;
    height: 25px;
    border-radius: 8px;
    background: #ecfdf3;
    color: #039855;
    font-size: 8px;
    font-weight: 900;
}

.recent-item {
    display: grid;
    grid-template-columns: 35px 1fr;
    gap: 10px;
    border-radius: 15px;
    padding: 10px;
    transition: 0.2s;
}

.recent-item:hover {
    background: #f8fafc;
}

.recent-icon {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    font-size: 10px;
    font-weight: 900;
}

.recent-task {
    background: #ecfdf3;
    color: #039855;
}

.recent-note {
    background: #eff6ff;
    color: #2563eb;
}

.recent-diary {
    background: #fff7ed;
    color: #ea580c;
}

.recent-meta {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    color: #98a2b3;
    font-size: 7px;
    font-weight: 850;
    text-transform: uppercase;
}

.recent-item p {
    margin-top: 4px;
    display: -webkit-box;
    overflow: hidden;
    color: #475467;
    font-size: 10px;
    line-height: 1.5;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.empty-state {
    min-height: 150px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #98a2b3;
}

.empty-state > span {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 13px;
    background: #f2f4f7;
}

.empty-state b {
    margin-top: 9px;
    color: #667085;
    font-size: 10px;
}

.empty-state small {
    max-width: 230px;
    margin-top: 4px;
    font-size: 8px;
    line-height: 1.5;
}

@keyframes floatOrb {
    0%,
    100% {
        transform: translate(-50%, -50%) translateY(0);
    }

    50% {
        transform: translate(-50%, -50%) translateY(-8px);
    }
}

@media (max-width: 1180px) {
    .hero-grid,
    .lower-grid {
        grid-template-columns: 1fr;
    }

    .welcome-card {
        grid-template-columns: minmax(0, 1fr) 280px;
    }

    .brief-card {
        min-height: 230px;
    }

    .metric-grid,
    .quick-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 760px) {
    .hero-grid {
        gap: 12px;
    }

    .welcome-card {
        display: block;
        min-height: auto;
        border-radius: 25px;
    }

    .welcome-copy {
        padding: 25px;
    }

    .welcome-copy h1 {
        font-size: 31px;
    }

    .hero-visual {
        display: none;
    }

    .brief-card {
        min-height: 220px;
        border-radius: 25px;
        padding: 22px;
    }

    .metric-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .metric-card {
        min-height: 80px;
        border-radius: 18px;
    }

    .quick-grid {
        grid-template-columns: 1fr 1fr;
    }

    .quick-card {
        min-height: 88px;
        border-radius: 18px;
        padding: 13px;
    }

    .quick-copy small {
        display: none;
    }

    .capture-card,
    .planner-card,
    .recent-card {
        border-radius: 22px;
        padding: 17px;
    }

    .capture-head {
        align-items: flex-start;
        flex-direction: column;
    }

    .schedule-grid {
        grid-template-columns: 1fr;
    }

    .capture-tools {
        align-items: stretch;
        flex-direction: column;
    }

    .save-button {
        width: 100%;
    }

    .planner-controls {
        grid-template-columns: 1fr 1fr;
    }

    .planner-controls button {
        grid-column: 1 / -1;
    }

    .section-heading h2,
    .card-title-row h2 {
        font-size: 18px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .apon-orb {
        animation: none;
    }

    .primary-action,
    .soft-action,
    .brief-card,
    .metric-card,
    .quick-card,
    .save-button {
        transition: none;
    }
}
</style>
