<script setup>
import { Head } from '@inertiajs/vue3';
import { Inbox, Filter, Search } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import PageEmpty from '@/Components/PageEmpty.vue';

defineProps({
    tickets: { type: Array, default: () => [] },
});

const priorityVariant = (p) => {
    const map = { CRITICAL: 'danger', HIGH: 'warning', MEDIUM: 'default', LOW: 'secondary' };
    return map[p] || 'secondary';
};

const statusVariant = (s) => {
    const map = { open: 'warning', pending_response: 'default', resolved: 'success', closed: 'secondary' };
    return map[s] || 'secondary';
};
</script>

<template>
    <Head title="Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-extrabold font-display text-text tracking-tight sm:text-xl">Tickets</h1>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between flex-wrap gap-3">
                    <div>
                        <CardTitle>Support Tickets</CardTitle>
                        <CardDescription>{{ tickets.length }} {{ tickets.length === 1 ? 'ticket' : 'tickets' }} triaged</CardDescription>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="secondary" size="sm"><Filter class="h-3 w-3" /> Filter</Button>
                        <Button variant="secondary" size="sm"><Search class="h-3 w-3" /> Search</Button>
                    </div>
                </CardHeader>

                <CardContent class="p-0">
                    <PageEmpty
                        v-if="tickets.length === 0"
                        title="No tickets yet"
                        description="Tickets appear here automatically when n8n triages incoming Gmail messages."
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
                                    <td class="px-5 py-3 font-mono text-[10px] text-text-tertiary">#{{ t.id }}</td>
                                    <td class="px-5 py-3 text-text">{{ t.customer_name || t.source_email }}</td>
                                    <td class="px-5 py-3 text-text-secondary truncate max-w-xs">{{ t.subject }}</td>
                                    <td class="px-5 py-3"><Badge variant="secondary">{{ t.category || 'Uncategorized' }}</Badge></td>
                                    <td class="px-5 py-3"><Badge :variant="priorityVariant(t.priority)">{{ t.priority }}</Badge></td>
                                    <td class="px-5 py-3"><Badge :variant="statusVariant(t.status)">{{ t.status }}</Badge></td>
                                    <td class="px-5 py-3 text-text-tertiary">{{ t.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
