<script setup>
import { Head } from '@inertiajs/vue3';
import { UserCircle, Search } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import PageEmpty from '@/Components/PageEmpty.vue';

defineProps({
    customers: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Customers" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-extrabold font-display text-text tracking-tight sm:text-xl">Customers</h1>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between flex-wrap gap-3">
                    <div>
                        <CardTitle>Customer Directory</CardTitle>
                        <CardDescription>{{ customers.length }} {{ customers.length === 1 ? 'customer' : 'customers' }} on file</CardDescription>
                    </div>
                    <Button variant="secondary" size="sm"><Search class="h-3 w-3" /> Search</Button>
                </CardHeader>

                <CardContent class="p-0">
                    <PageEmpty
                        v-if="customers.length === 0"
                        title="No customers yet"
                        description="Customers are created when leads convert or when inbound emails are linked to a known sender."
                    >
                        <template #icon><UserCircle class="h-7 w-7" /></template>
                    </PageEmpty>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Name</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Email</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Company</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Phone</th>
                                    <th class="px-5 py-3 text-left text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="c in customers" :key="c.id" class="hover:bg-surface-hover transition">
                                    <td class="px-5 py-3 font-bold text-text">{{ c.name }}</td>
                                    <td class="px-5 py-3 text-text-secondary">{{ c.email }}</td>
                                    <td class="px-5 py-3 text-text-secondary">{{ c.company || '—' }}</td>
                                    <td class="px-5 py-3 text-text-secondary">{{ c.phone || '—' }}</td>
                                    <td class="px-5 py-3 text-text-tertiary">{{ c.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
