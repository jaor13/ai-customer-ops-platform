<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, usePoll } from '@inertiajs/vue3';
import { Inbox, Search, X, Ticket as TicketIcon, AlertTriangle, CheckCircle2 } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import PageEmpty from '@/Components/PageEmpty.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    tickets: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ search: '', category: '', priority: '', status: '' }) },
    options: { type: Object, default: () => ({ categories: [], priorities: [], statuses: [], allStatuses: [] }) },
    stats: { type: Object, default: () => ({ total: 0, open: 0, urgent: 0, resolved: 0 }) },
});

const form = ref({ ...props.filters });

const hasActiveFilters = computed(
    () => form.value.search || form.value.category || form.value.priority || form.value.status,
);

let debounce = null;
const pruneEmpty = (obj) =>
    Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== '' && v !== null));

const reload = () => {
    router.get(route('tickets.index'), pruneEmpty(form.value), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['tickets', 'filters', 'stats'],
    });
};

watch(
    () => form.value.search,
    () => {
        clearTimeout(debounce);
        debounce = setTimeout(reload, 350);
    },
);

watch(
    [() => form.value.category, () => form.value.priority, () => form.value.status],
    reload,
);

const clearFilters = () => {
    form.value = { search: '', category: '', priority: '', status: '' };
    reload();
};

// Live updates — re-fetch just the list + stats every 20s (pauses when the
// tab is backgrounded; preserves the current filters in the URL).
usePoll(20000, { only: ['tickets', 'stats'] });

const priorityVariant = (p) => ({ CRITICAL: 'danger', HIGH: 'warning', MEDIUM: 'default', LOW: 'secondary' }[p] || 'secondary');

const statusColor = (s) => ({
    open: 'text-warning',
    pending_response: 'text-primary',
    resolved: 'text-success',
    closed: 'text-text-tertiary',
}[s] || 'text-text');

const updatingId = ref(null);
const updateStatus = (ticket, status) => {
    if (status === ticket.status) return;
    updatingId.value = ticket.id;
    router.patch(
        route('tickets.update', ticket.id),
        { status },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => (updatingId.value = null),
        },
    );
};

const capitalize = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1).replace(/_/g, ' ') : '');

const selectClass =
    'rounded-xl border-border bg-white text-text text-xs shadow-sm transition-all duration-200 focus:border-primary focus:ring-primary/20 focus:ring-2 dark:bg-surface';
</script>

<template>
    <Head title="Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-extrabold font-display text-text tracking-tight sm:text-xl">Tickets</h1>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Summary stats -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <StatCard label="Total Tickets" :value="stats.total" accent="blue">
                    <template #icon><TicketIcon class="h-3.5 w-3.5" /></template>
                    <template #trend><span class="text-[10px] text-text-tertiary">All time</span></template>
                </StatCard>
                <StatCard label="Open" :value="stats.open" accent="amber">
                    <template #icon><Inbox class="h-3.5 w-3.5" /></template>
                    <template #trend><span class="text-[10px] text-text-tertiary">Awaiting action</span></template>
                </StatCard>
                <StatCard label="Urgent" :value="stats.urgent" accent="red">
                    <template #icon><AlertTriangle class="h-3.5 w-3.5" /></template>
                    <template #trend><span class="text-[10px] text-text-tertiary">High / Critical</span></template>
                </StatCard>
                <StatCard label="Resolved" :value="stats.resolved" accent="emerald">
                    <template #icon><CheckCircle2 class="h-3.5 w-3.5" /></template>
                    <template #trend><span class="text-[10px] text-text-tertiary">Resolved or closed</span></template>
                </StatCard>
            </div>

            <Card>
                <CardHeader class="gap-4">
                    <div class="flex flex-row items-center justify-between flex-wrap gap-3">
                        <div>
                            <CardTitle>Support Tickets</CardTitle>
                            <CardDescription>
                                {{ tickets.length }} {{ tickets.length === 1 ? 'ticket' : 'tickets' }}
                                {{ hasActiveFilters ? 'matching filters' : 'triaged' }}
                            </CardDescription>
                        </div>
                    </div>

                    <!-- Filter bar -->
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:flex-wrap">
                        <div class="relative flex-1 min-w-[180px]">
                            <Search class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-text-tertiary" />
                            <input
                                v-model="form.search"
                                type="search"
                                placeholder="Search subject, body, or sender…"
                                :class="selectClass"
                                class="w-full pl-9 pr-3 py-2"
                            />
                        </div>

                        <select v-model="form.category" :class="selectClass" class="pl-3 pr-9 py-2" aria-label="Filter by category">
                            <option value="">All categories</option>
                            <option v-for="c in options.categories" :key="c" :value="c">{{ c }}</option>
                        </select>

                        <select v-model="form.priority" :class="selectClass" class="pl-3 pr-9 py-2" aria-label="Filter by priority">
                            <option value="">All priorities</option>
                            <option v-for="p in options.priorities" :key="p" :value="p">{{ capitalize(p) }}</option>
                        </select>

                        <select v-model="form.status" :class="selectClass" class="pl-3 pr-9 py-2" aria-label="Filter by status">
                            <option value="">All statuses</option>
                            <option v-for="st in options.statuses" :key="st" :value="st">{{ capitalize(st) }}</option>
                        </select>

                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-border px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-text-secondary transition-colors hover:bg-surface-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                            @click="clearFilters"
                        >
                            <X class="h-3 w-3" /> Clear
                        </button>
                    </div>
                </CardHeader>

                <CardContent class="p-0">
                    <PageEmpty
                        v-if="tickets.length === 0"
                        :title="hasActiveFilters ? 'No matching tickets' : 'No tickets yet'"
                        :description="hasActiveFilters
                            ? 'Try adjusting or clearing the filters above.'
                            : 'Tickets appear here automatically when n8n triages incoming Gmail messages.'"
                    >
                        <template #icon><Inbox class="h-7 w-7" /></template>
                    </PageEmpty>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">ID</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Customer</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Subject</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Category</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Priority</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Status</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="t in tickets" :key="t.id" class="hover:bg-surface-hover transition">
                                    <td class="px-5 py-3 font-mono text-[10px] text-text-tertiary align-top">#{{ t.id }}</td>
                                    <td class="px-5 py-3 text-text align-top">{{ t.customer_name || t.source_email }}</td>
                                    <td class="px-5 py-3 align-top">
                                        <p class="font-semibold text-text truncate max-w-xs">{{ t.subject || '(no subject)' }}</p>
                                        <p v-if="t.preview" class="mt-0.5 text-[10px] text-text-tertiary truncate max-w-xs">{{ t.preview }}</p>
                                    </td>
                                    <td class="px-5 py-3 align-top"><Badge variant="secondary">{{ t.category || 'Uncategorized' }}</Badge></td>
                                    <td class="px-5 py-3 align-top"><Badge :variant="priorityVariant(t.priority)">{{ t.priority }}</Badge></td>
                                    <td class="px-5 py-3 align-top">
                                        <select
                                            :value="t.status"
                                            :disabled="updatingId === t.id"
                                            @change="updateStatus(t, $event.target.value)"
                                            :class="statusColor(t.status)"
                                            class="rounded-lg border border-border bg-white text-[10px] font-bold pl-2 pr-7 py-1 focus:border-primary focus:ring-primary/20 focus:ring-2 cursor-pointer disabled:opacity-50 dark:bg-surface"
                                            aria-label="Change ticket status"
                                        >
                                            <option v-for="s in options.allStatuses" :key="s" :value="s">{{ capitalize(s) }}</option>
                                        </select>
                                    </td>
                                    <td class="px-5 py-3 text-text-tertiary align-top">{{ t.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
