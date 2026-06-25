<script setup>
import { computed } from 'vue';
import { Head, Link, usePoll } from '@inertiajs/vue3';
import {
    Users,
    Inbox,
    ShieldCheck,
    Send,
    ArrowUpRight,
    ExternalLink,
    Sparkles,
    UserPlus,
    Mail,
    Database,
    CheckCircle2,
} from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import { Button } from '@/Components/ui/button';
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    stats: { type: Object, required: true },
    leadCaptureSeries: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    demoMode: { type: Boolean, default: false },
});

// Live refresh (tab-aware) — keep the dashboard current without a reload.
usePoll(30000, { only: ['stats', 'leadCaptureSeries', 'activities'] });

const chartData = computed(() =>
    props.leadCaptureSeries.length ? props.leadCaptureSeries : [{ day: '—', count: 0 }],
);

const chartSeries = computed(() => [
    {
        name: 'Leads Captured',
        data: chartData.value.map((d) => d.count),
    },
]);

const chartOptions = computed(() => ({
    chart: {
        type: 'area',
        height: 200,
        toolbar: { show: false },
        sparkline: { enabled: false },
        fontFamily: 'Plus Jakarta Sans, sans-serif',
        background: 'transparent',
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800,
            animateGradually: {
                enabled: true,
                delay: 150,
            },
            dynamicAnimation: {
                enabled: true,
                speed: 350,
            },
        },
    },
    colors: ['rgb(var(--color-primary))'],
    stroke: {
        curve: 'smooth',
        width: 3,
    },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.35,
            opacityTo: 0.01,
            stops: [0, 95, 100],
        },
    },
    markers: {
        size: 4,
        colors: ['#ffffff'],
        strokeColors: 'rgb(var(--color-primary))',
        strokeWidth: 2,
        hover: { size: 6 },
    },
    grid: {
        borderColor: 'rgb(var(--color-border))',
        strokeDashArray: 4,
        padding: { left: 10, right: 10, top: 0, bottom: 0 },
    },
    xaxis: {
        categories: chartData.value.map((d) => d.day),
        labels: {
            style: {
                colors: '#94a3b8',
                fontSize: '9px',
                fontWeight: 700,
            },
        },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            style: {
                colors: '#94a3b8',
                fontSize: '9px',
                fontWeight: 700,
            },
        },
        min: 0,
        tickAmount: 3,
    },
    tooltip: {
        theme: 'light',
        x: { show: true },
        marker: { show: false },
    },
    dataLabels: { enabled: false },
}));

const iconFor = (type) => {
    const map = {
        lead: UserPlus,
        ticket: Mail,
        system: Database,
        approval: CheckCircle2,
    };
    return map[type] || Sparkles;
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-extrabold font-display text-text tracking-tight sm:text-xl">
                    Dashboard
                </h1>
                <Badge v-if="demoMode" variant="warning">
                    <span class="h-1 w-1 rounded-full bg-amber-500 animate-pulse" />
                    Demo Sandbox
                </Badge>
                <Badge v-else variant="success">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                    Live
                </Badge>
            </div>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Welcome row -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-extrabold font-display text-text">
                        Welcome back, {{ $page.props.auth.user.name }}.
                    </p>
                    <p class="text-xs text-text-secondary mt-0.5">
                        Here's what your AI agents and automations processed today.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button as="a" href="https://n8n.ai-ops.jaor13.app" target="_blank" variant="secondary" size="sm">
                        n8n Workflows
                        <ExternalLink class="h-3 w-3" />
                    </Button>
                    <Link :href="route('approvals.index')">
                        <Button size="sm">
                            Review Queue
                            <span
                                v-if="stats.pendingApprovalsCount > 0"
                                class="flex h-4 min-w-4 items-center justify-center rounded-full bg-white/25 px-1 text-[9px] font-black"
                            >
                                {{ stats.pendingApprovalsCount }}
                            </span>
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Demo banner -->
            <Card v-if="demoMode" class="border-amber-200/60 bg-amber-50/40 dark:bg-amber-500/5 dark:border-amber-500/20">
                <CardContent class="flex items-start gap-3 p-4">
                    <Sparkles class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" />
                    <div class="text-xs text-amber-800 dark:text-amber-400">
                        <span class="font-bold">Sandbox active:</span>
                        Showing mock data because your database is empty. Once n8n captures real leads or tickets, the dashboard switches to live mode automatically.
                    </div>
                </CardContent>
            </Card>

            <!-- Stat grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard label="Total Leads" :value="stats.leadsCount" accent="blue">
                    <template #icon><Users class="h-4 w-4" /></template>
                    <template #trend>
                        <Badge variant="success" class="text-[9px]">+{{ stats.leadsThisWeek }}</Badge>
                        <span class="text-[9px] text-text-tertiary">this week</span>
                    </template>
                </StatCard>

                <StatCard label="Open Tickets" :value="stats.openTicketsCount" accent="sky">
                    <template #icon><Inbox class="h-4 w-4" /></template>
                    <template #trend>
                        <Badge :variant="stats.urgentTicketsCount > 0 ? 'danger' : 'secondary'" class="text-[9px]">
                            {{ stats.urgentTicketsCount }} urgent
                        </Badge>
                        <span class="text-[9px] text-text-tertiary">high / critical</span>
                    </template>
                </StatCard>

                <StatCard label="Pending Approvals" :value="stats.pendingApprovalsCount" accent="indigo">
                    <template #icon><ShieldCheck class="h-4 w-4" /></template>
                    <template #trend>
                        <Link v-if="stats.pendingApprovalsCount > 0" :href="route('approvals.index')">
                            <Badge variant="danger" class="cursor-pointer hover:opacity-80 text-[9px]">
                                Action required
                            </Badge>
                        </Link>
                        <Badge v-else variant="success" class="text-[9px]">All clear</Badge>
                        <span class="text-[9px] text-text-tertiary">needs review</span>
                    </template>
                </StatCard>

                <StatCard label="Emails Sent" :value="stats.sentCount" accent="emerald">
                    <template #icon><Send class="h-4 w-4" /></template>
                    <template #trend>
                        <Badge variant="success" class="text-[9px]">{{ stats.customersCount }} customers</Badge>
                        <span class="text-[9px] text-text-tertiary">after human review</span>
                    </template>
                </StatCard>
            </div>

            <!-- Chart + Activity -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Lead Capture Activity</CardTitle>
                            <CardDescription>New leads captured per day over the last 7 days</CardDescription>
                        </div>
                        <Badge variant="secondary">Last 7 Days</Badge>
                    </CardHeader>
                    <CardContent class="pb-1.5 pl-1.5 pr-2 pt-0">
                        <div class="relative w-full h-[210px] overflow-hidden">
                            <VueApexCharts
                                type="area"
                                height="210"
                                :options="chartOptions"
                                :series="chartSeries"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Recent Activity</CardTitle>
                            <CardDescription>Latest events across the platform</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-for="a in activities"
                            :key="a.id"
                            class="flex items-start gap-3 rounded-xl p-2 hover:bg-surface-hover transition"
                        >
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                <component :is="iconFor(a.type)" class="h-3.5 w-3.5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-bold text-text leading-tight truncate">{{ a.title }}</p>
                                <p class="text-[10px] text-text-secondary mt-0.5 line-clamp-2">{{ a.desc }}</p>
                                <p class="text-[9px] text-text-tertiary mt-1">{{ a.time }}</p>
                            </div>
                        </div>
                        <div v-if="activities.length === 0" class="text-center py-6 text-[11px] text-text-tertiary">
                            No activity yet.
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick links -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>Jump to the most common operations</CardDescription>
                </CardHeader>
                <CardContent class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <Link
                        v-for="quick in [
                            { name: 'Approvals', route: 'approvals.index', icon: ShieldCheck },
                            { name: 'Leads', route: 'leads.index', icon: Users },
                            { name: 'Tickets', route: 'tickets.index', icon: Inbox },
                            { name: 'Knowledge Base', route: 'knowledge-base.index', icon: Database },
                        ]"
                        :key="quick.name"
                        :href="route(quick.route)"
                        class="group flex items-center gap-3 rounded-xl border border-border p-3 hover:border-primary/30 hover:shadow-md transition"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-light text-primary group-hover:scale-110 transition-transform">
                            <component :is="quick.icon" class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-bold text-text">{{ quick.name }}</span>
                        <ArrowUpRight class="h-3 w-3 ml-auto text-text-tertiary group-hover:text-primary transition" />
                    </Link>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
