<script setup>
import { Head } from '@inertiajs/vue3';
import { Users, Search, Filter } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import PageEmpty from '@/Components/PageEmpty.vue';

const props = defineProps({
    leads: { type: Array, default: () => [] },
});

const scoreVariant = (score) => {
    if (score === null || score === undefined) return 'secondary';
    if (score >= 70) return 'success';
    if (score >= 40) return 'warning';
    return 'secondary';
};

const categoryVariant = (cat) => {
    const map = { Hot: 'hot', Warm: 'warm', Cold: 'cold' };
    return map[cat] || 'secondary';
};
</script>

<template>
    <Head title="Leads" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-extrabold font-display text-text tracking-tight sm:text-xl">Leads</h1>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between flex-wrap gap-3">
                    <div>
                        <CardTitle>Captured Leads</CardTitle>
                        <CardDescription>{{ leads.length }} {{ leads.length === 1 ? 'lead' : 'leads' }} in the pipeline</CardDescription>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="secondary" size="sm">
                            <Filter class="h-3 w-3" /> Filter
                        </Button>
                        <Button variant="secondary" size="sm">
                            <Search class="h-3 w-3" /> Search
                        </Button>
                    </div>
                </CardHeader>

                <CardContent class="p-0">
                    <PageEmpty
                        v-if="leads.length === 0"
                        title="No leads yet"
                        description="Once your n8n webhook captures form submissions, they'll appear here with AI-generated scores."
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
                                    <td class="px-5 py-3"><Badge variant="secondary">{{ lead.source }}</Badge></td>
                                    <td class="px-5 py-3">
                                        <Badge :variant="scoreVariant(lead.score)">{{ lead.score ?? '—' }}</Badge>
                                    </td>
                                    <td class="px-5 py-3">
                                        <Badge v-if="lead.category" :variant="categoryVariant(lead.category)">{{ lead.category }}</Badge>
                                        <span v-else class="text-text-tertiary">—</span>
                                    </td>
                                    <td class="px-5 py-3"><Badge variant="outline">{{ lead.status }}</Badge></td>
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
