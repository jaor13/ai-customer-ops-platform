<script setup>
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft, Mail, Phone, Building2, Activity,
    UserPlus, Sparkles, Inbox, FileText, CheckCircle2, XCircle, Send, Clock,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import PageEmpty from '@/Components/PageEmpty.vue';

defineProps({
    customer: { type: Object, required: true },
    timeline: { type: Array, default: () => [] },
    tickets: { type: Array, default: () => [] },
});

// Map an interaction event_type to an icon + accent colour.
const eventMeta = (type) => ({
    lead_created: { icon: UserPlus, class: 'bg-primary-light text-primary', label: 'Lead Created' },
    lead_scored: { icon: Sparkles, class: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400', label: 'Lead Scored' },
    lead_welcome_generated: { icon: FileText, class: 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400', label: 'Welcome Draft Generated' },
    email_received: { icon: Inbox, class: 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400', label: 'Email Received' },
    ticket_opened: { icon: Inbox, class: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400', label: 'Ticket Opened' },
    draft_generated: { icon: FileText, class: 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400', label: 'Draft Generated' },
    draft_approved: { icon: CheckCircle2, class: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400', label: 'Draft Approved' },
    draft_rejected: { icon: XCircle, class: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400', label: 'Draft Rejected' },
    email_sent: { icon: Send, class: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400', label: 'Email Sent' },
    ticket_closed: { icon: CheckCircle2, class: 'bg-slate-50 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400', label: 'Ticket Closed' },
}[type] || { icon: Activity, class: 'bg-slate-50 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400', label: type });

const categoryVariant = (cat) => ({ Hot: 'hot', Warm: 'warm', Cold: 'cold' }[cat] || 'secondary');
const priorityVariant = (p) => ({ CRITICAL: 'danger', HIGH: 'warning', MEDIUM: 'default', LOW: 'secondary' }[p] || 'secondary');
const statusVariant = (s) => ({ open: 'warning', pending_response: 'default', resolved: 'success', closed: 'secondary' }[s] || 'secondary');
const capitalize = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1).replace(/_/g, ' ') : '');
const initials = (name) => (name || '?').split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase();
</script>

<template>
    <Head :title="`${customer.name} — Timeline`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('customers.index')"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-text-tertiary transition-colors hover:bg-surface-hover hover:text-text"
                    aria-label="Back to customers"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <h1 class="text-lg font-extrabold font-display text-text tracking-tight sm:text-xl">Customer Timeline</h1>
            </div>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Profile -->
                <div class="space-y-6 lg:col-span-1">
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary text-base font-extrabold font-display text-white">
                                    {{ initials(customer.name) }}
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-base font-extrabold font-display text-text truncate">{{ customer.name }}</h2>
                                    <p class="text-xs text-text-secondary truncate">{{ customer.company || 'No company' }}</p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-2.5 text-xs">
                                <div class="flex items-center gap-2 text-text-secondary">
                                    <Mail class="h-3.5 w-3.5 text-text-tertiary shrink-0" />
                                    <span class="truncate">{{ customer.email }}</span>
                                </div>
                                <div v-if="customer.phone" class="flex items-center gap-2 text-text-secondary">
                                    <Phone class="h-3.5 w-3.5 text-text-tertiary shrink-0" />
                                    <span>{{ customer.phone }}</span>
                                </div>
                                <div v-if="customer.company" class="flex items-center gap-2 text-text-secondary">
                                    <Building2 class="h-3.5 w-3.5 text-text-tertiary shrink-0" />
                                    <span class="truncate">{{ customer.company }}</span>
                                </div>
                            </div>

                            <div v-if="customer.lead" class="mt-5 border-t border-border pt-4">
                                <p class="text-[9px] font-bold uppercase tracking-widest text-text-tertiary mb-2">Lead origin</p>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <Badge v-if="customer.lead.category" :variant="categoryVariant(customer.lead.category)">
                                        {{ customer.lead.category }}
                                    </Badge>
                                    <Badge v-if="customer.lead.score !== null" variant="secondary">Score {{ customer.lead.score }}</Badge>
                                    <Badge v-if="customer.lead.source" variant="outline">{{ capitalize(customer.lead.source) }}</Badge>
                                </div>
                            </div>

                            <p class="mt-5 text-[10px] text-text-tertiary">Customer since {{ customer.created_at }}</p>
                        </CardContent>
                    </Card>

                    <!-- Tickets -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Tickets</CardTitle>
                            <CardDescription>{{ tickets.length }} {{ tickets.length === 1 ? 'ticket' : 'tickets' }}</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-2.5">
                            <div
                                v-for="t in tickets"
                                :key="t.id"
                                class="rounded-xl border border-border p-3"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-xs font-semibold text-text truncate">{{ t.subject || '(no subject)' }}</p>
                                    <Badge :variant="priorityVariant(t.priority)" class="shrink-0">{{ t.priority }}</Badge>
                                </div>
                                <div class="mt-1.5 flex items-center gap-1.5">
                                    <Badge variant="secondary" class="text-[9px]">{{ t.category || 'General' }}</Badge>
                                    <Badge :variant="statusVariant(t.status)" class="text-[9px]">{{ capitalize(t.status) }}</Badge>
                                    <span class="ml-auto text-[9px] text-text-tertiary">{{ t.created_at }}</span>
                                </div>
                            </div>
                            <p v-if="tickets.length === 0" class="text-[11px] text-text-tertiary py-2">No tickets for this customer.</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Timeline -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Activity Timeline</CardTitle>
                        <CardDescription>Every recorded event for this customer, newest first</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <PageEmpty
                            v-if="timeline.length === 0"
                            title="No activity yet"
                            description="Events appear here as the customer is scored, emails are triaged, and drafts are reviewed."
                        >
                            <template #icon><Activity class="h-7 w-7" /></template>
                        </PageEmpty>

                        <ol v-else class="relative space-y-5">
                            <!-- vertical line -->
                            <span class="absolute left-[15px] top-2 bottom-2 w-px bg-border" aria-hidden="true" />
                            <li v-for="event in timeline" :key="event.id" class="relative flex gap-4">
                                <div
                                    class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full ring-4 ring-surface"
                                    :class="eventMeta(event.event_type).class"
                                >
                                    <component :is="eventMeta(event.event_type).icon" class="h-3.5 w-3.5" />
                                </div>
                                <div class="min-w-0 flex-1 pb-1">
                                    <div class="flex flex-wrap items-center justify-between gap-1">
                                        <p class="text-xs font-bold text-text">{{ eventMeta(event.event_type).label }}</p>
                                        <span class="flex items-center gap-1 text-[9px] text-text-tertiary">
                                            <Clock class="h-2.5 w-2.5" />
                                            {{ event.time }}
                                        </span>
                                    </div>
                                    <p v-if="event.description" class="mt-0.5 text-[11px] text-text-secondary leading-relaxed">
                                        {{ event.description }}
                                    </p>
                                    <p class="mt-0.5 text-[9px] text-text-tertiary">{{ event.date }}</p>
                                </div>
                            </li>
                        </ol>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
