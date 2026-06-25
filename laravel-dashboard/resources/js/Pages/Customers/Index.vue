<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePoll } from '@inertiajs/vue3';
import { UserCircle, Search, ArrowUpRight, X } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import PageEmpty from '@/Components/PageEmpty.vue';

const props = defineProps({
    customers: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ search: '' }) },
});

const form = ref({ ...props.filters });

const hasActiveFilters = computed(() => form.value.search);

const pruneEmpty = (obj) =>
    Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== '' && v !== null));

let debounce = null;
const reload = () => {
    router.get(route('customers.index'), pruneEmpty(form.value), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['customers', 'filters'],
    });
};

watch(
    () => form.value.search,
    () => {
        clearTimeout(debounce);
        debounce = setTimeout(reload, 350);
    },
);

const clearFilters = () => {
    form.value = { search: '' };
    reload();
};

usePoll(20000, { only: ['customers'] });
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
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-text-tertiary" />
                            <input
                                v-model="form.search"
                                type="text"
                                placeholder="Search customers…"
                                class="rounded-xl border border-border bg-white pl-9 pr-3 py-2 text-xs text-text placeholder-text-tertiary shadow-sm transition-all duration-200 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:bg-surface w-56"
                            />
                        </div>
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="inline-flex items-center gap-1 rounded-xl border border-border px-3 py-2 text-xs font-medium text-text-secondary hover:bg-surface-hover transition-colors"
                        >
                            <X class="h-3 w-3" /> Clear
                        </button>
                    </div>
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
                                    <th class="px-5 py-3 text-right text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Timeline</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="c in customers" :key="c.id" class="hover:bg-surface-hover transition">
                                    <td class="px-5 py-3 font-bold text-text">
                                        <Link :href="route('customers.show', c.id)" class="hover:text-primary transition-colors">
                                            {{ c.name }}
                                        </Link>
                                    </td>
                                    <td class="px-5 py-3 text-text-secondary">{{ c.email }}</td>
                                    <td class="px-5 py-3 text-text-secondary">{{ c.company || '—' }}</td>
                                    <td class="px-5 py-3 text-text-secondary">{{ c.phone || '—' }}</td>
                                    <td class="px-5 py-3 text-text-tertiary">{{ c.created_at }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <Link
                                            :href="route('customers.show', c.id)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-text-tertiary transition-colors hover:bg-primary-light hover:text-primary"
                                            :aria-label="`View ${c.name} timeline`"
                                        >
                                            <ArrowUpRight class="h-3.5 w-3.5" />
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
