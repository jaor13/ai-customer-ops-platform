<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, usePoll } from '@inertiajs/vue3';
import { Users, Search, X, Flame, Thermometer, Snowflake } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import PageEmpty from '@/Components/PageEmpty.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    leads: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ search: '', category: '', source: '', status: '' }) },
    options: { type: Object, default: () => ({ categories: [], sources: [], statuses: [] }) },
    stats: { type: Object, default: () => ({ total: 0, hot: 0, warm: 0, cold: 0 }) },
});

// Local, reactive copy of the active filters.
const form = ref({ ...props.filters });

const hasActiveFilters = computed(
    () => form.value.search || form.value.category || form.value.source || form.value.status,
);

// Push filters to the server (debounced for the search box) without losing
// scroll/focus and merging only the props the page needs.
let debounce = null;
const reload = () => {
    router.get(route('leads.index'), pruneEmpty(form.value), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['leads', 'filters', 'stats'],
    });
};

const pruneEmpty = (obj) =>
    Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== '' && v !== null));

watch(
    () => form.value.search,
    () => {
        clearTimeout(debounce);
        debounce = setTimeout(reload, 350);
    },
);

watch(
    [() => form.value.category, () => form.value.source, () => form.value.status],
    reload,
);

const clearFilters = () => {
    form.value = { search: '', category: '', source: '', status: '' };
    reload();
};

// Live updates — re-fetch just the list + stats every 20s (pauses when the
// tab is backgrounded; preserves the current filters in the URL).
usePoll(20000, { only: ['leads', 'stats'] });

const scoreVariant = (score) => {
    if (score === null || score === undefined) return 'secondary';
    if (score >= 70) return 'success';
    if (score >= 40) return 'warning';
    return 'secondary';
};

const categoryVariant = (cat) => ({ Hot: 'hot', Warm: 'warm', Cold: 'cold' }[cat] || 'secondary');

const capitalize = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1).replace(/_/g, ' ') : '');

const selectClass =
    'rounded-xl border-border bg-white text-text text-xs shadow-sm transition-all duration-200 focus:border-primary focus:ring-primary/20 focus:ring-2 dark:bg-surface';
</script>

<template>
    <Head title="Leads" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-extrabold font-display text-text tracking-tight sm:text-xl">Leads</h1>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Summary stats -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <StatCard label="Total Leads" :value="stats.total" accent="blue">
                    <template #icon><Users class="h-3.5 w-3.5" /></template>
                    <template #trend><span class="text-[10px] text-text-tertiary">In the pipeline</span></template>
                </StatCard>
                <StatCard label="Hot" :value="stats.hot" accent="red">
                    <template #icon><Flame class="h-3.5 w-3.5" /></template>
                    <template #trend><span class="text-[10px] text-text-tertiary">Score 70+</span></template>
                </StatCard>
                <StatCard label="Warm" :value="stats.warm" accent="amber">
                    <template #icon><Thermometer class="h-3.5 w-3.5" /></template>
                    <template #trend><span class="text-[10px] text-text-tertiary">Score 40–69</span></template>
                </StatCard>
                <StatCard label="Cold" :value="stats.cold" accent="sky">
                    <template #icon><Snowflake class="h-3.5 w-3.5" /></template>
                    <template #trend><span class="text-[10px] text-text-tertiary">Score under 40</span></template>
                </StatCard>
            </div>

            <Card>
                <CardHeader class="gap-4">
                    <div class="flex flex-row items-center justify-between flex-wrap gap-3">
                        <div>
                            <CardTitle>Captured Leads</CardTitle>
                            <CardDescription>
                                {{ leads.length }} {{ leads.length === 1 ? 'lead' : 'leads' }}
                                {{ hasActiveFilters ? 'matching filters' : 'in the pipeline' }}
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
                                placeholder="Search name, email, or company…"
                                :class="selectClass"
                                class="w-full pl-9 pr-3 py-2"
                            />
                        </div>

                        <select v-model="form.category" :class="selectClass" class="pl-3 pr-9 py-2" aria-label="Filter by category">
                            <option value="">All categories</option>
                            <option v-for="c in options.categories" :key="c" :value="c">{{ c }}</option>
                        </select>

                        <select v-model="form.source" :class="selectClass" class="pl-3 pr-9 py-2" aria-label="Filter by source">
                            <option value="">All sources</option>
                            <option v-for="s in options.sources" :key="s" :value="s">{{ capitalize(s) }}</option>
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
                        v-if="leads.length === 0"
                        :title="hasActiveFilters ? 'No matching leads' : 'No leads yet'"
                        :description="hasActiveFilters
                            ? 'Try adjusting or clearing the filters above.'
                            : 'Once your n8n webhook captures form submissions, they\'ll appear here with AI-generated scores.'"
                    >
                        <template #icon><Users class="h-7 w-7" /></template>
                    </PageEmpty>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Name</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Email</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Company</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Source</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Score</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Category</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Status</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="lead in leads" :key="lead.id" class="hover:bg-surface-hover transition">
                                    <td class="px-5 py-3 font-bold text-text">{{ lead.name }}</td>
                                    <td class="px-5 py-3 text-text-secondary">{{ lead.email }}</td>
                                    <td class="px-5 py-3 text-text-secondary">{{ lead.company || '—' }}</td>
                                    <td class="px-5 py-3"><Badge variant="secondary">{{ capitalize(lead.source) }}</Badge></td>
                                    <td class="px-5 py-3">
                                        <Badge :variant="scoreVariant(lead.score)">{{ lead.score ?? '—' }}</Badge>
                                    </td>
                                    <td class="px-5 py-3">
                                        <Badge v-if="lead.category" :variant="categoryVariant(lead.category)">{{ lead.category }}</Badge>
                                        <span v-else class="text-text-tertiary">—</span>
                                    </td>
                                    <td class="px-5 py-3"><Badge variant="outline">{{ capitalize(lead.status) }}</Badge></td>
                                    <td class="px-5 py-3 text-text-tertiary">{{ lead.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
