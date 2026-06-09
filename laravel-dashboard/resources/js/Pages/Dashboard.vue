<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-extrabold font-display text-text tracking-tight">Dashboard</h1>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-text-tertiary mt-0.5">Operations overview</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-success animate-pulse"></span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-text-tertiary">System online</span>
                </div>
            </div>
        </template>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Leads -->
            <div class="group rounded-2xl border border-border bg-white/80 backdrop-blur-sm p-5 transition-all duration-300 hover:shadow-lg hover:border-blue-100 dark:bg-surface/80">
                <div class="flex items-center justify-between">
                    <span class="text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Total Leads</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:scale-110 transition-transform duration-300 dark:bg-blue-500/10 dark:text-blue-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-extrabold font-display text-text">0</p>
                <p class="mt-0.5 text-[10px] text-text-tertiary">No leads captured yet</p>
            </div>

            <!-- Open Tickets -->
            <div class="group rounded-2xl border border-border bg-white/80 backdrop-blur-sm p-5 transition-all duration-300 hover:shadow-lg hover:border-blue-100 dark:bg-surface/80">
                <div class="flex items-center justify-between">
                    <span class="text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Open Tickets</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-50 text-sky-600 group-hover:scale-110 transition-transform duration-300 dark:bg-sky-500/10 dark:text-sky-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-extrabold font-display text-text">0</p>
                <p class="mt-0.5 text-[10px] text-text-tertiary">No tickets yet</p>
            </div>

            <!-- Pending Approvals -->
            <div class="group rounded-2xl border border-border bg-white/80 backdrop-blur-sm p-5 transition-all duration-300 hover:shadow-lg hover:border-amber-100 dark:bg-surface/80">
                <div class="flex items-center justify-between">
                    <span class="text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Pending Approvals</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 group-hover:scale-110 transition-transform duration-300 dark:bg-amber-500/10 dark:text-amber-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-extrabold font-display text-text">0</p>
                <p class="mt-0.5 text-[10px] text-text-tertiary">No drafts awaiting review</p>
            </div>

            <!-- Hot Leads -->
            <div class="group rounded-2xl border border-border bg-white/80 backdrop-blur-sm p-5 transition-all duration-300 hover:shadow-lg hover:border-red-100 dark:bg-surface/80">
                <div class="flex items-center justify-between">
                    <span class="text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Hot Leads</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 group-hover:scale-110 transition-transform duration-300 dark:bg-red-500/10 dark:text-red-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-extrabold font-display text-text">0</p>
                <p class="mt-0.5 text-[10px] text-text-tertiary">No hot leads scored</p>
            </div>
        </div>

        <!-- Empty State -->
        <div class="mt-8 rounded-2xl border border-border bg-white/60 backdrop-blur-sm p-12 text-center dark:bg-surface/60">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-500/10">
                <svg class="h-7 w-7 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-bold font-display text-text">Ready for action</h3>
            <p class="mx-auto mt-2 max-w-sm text-xs text-text-secondary leading-relaxed">
                Your AI operations platform is set up. Once n8n workflows start capturing leads and processing emails, data will populate here automatically.
            </p>
            <div class="mt-6">
                <a href="https://n8n.ai-ops.jaor13.app" target="_blank" class="inline-flex items-center gap-1.5 rounded-full border border-border bg-white px-4 py-2 text-[10px] font-bold uppercase tracking-widest text-text-secondary hover:border-blue-200 hover:text-blue-600 transition-all duration-300 dark:bg-surface">
                    Open n8n Workflows
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
