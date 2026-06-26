<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, Home, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    status: { type: Number, required: true },
});

const title = computed(() => ({
    503: 'Service Unavailable',
    500: 'Server Error',
    404: 'Page Not Found',
    403: 'Forbidden',
})[props.status] ?? 'Error');

const description = computed(() => ({
    503: 'We\'re doing some maintenance. Please check back in a moment.',
    500: 'Something went wrong on our end. Please try again or contact support if the issue persists.',
    404: 'The page you\'re looking for doesn\'t exist or has been moved.',
    403: 'You don\'t have permission to access this page.',
})[props.status] ?? 'An unexpected error occurred.');
</script>

<template>
    <Head :title="`${status} — ${title}`" />

    <div class="flex min-h-screen items-center justify-center bg-background px-4">
        <div class="w-full max-w-md text-center">
            <!-- Status code -->
            <p class="text-[80px] font-extrabold font-display leading-none tracking-tight text-primary/20">
                {{ status }}
            </p>

            <!-- Icon -->
            <div class="mx-auto mt-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <AlertTriangle class="h-6 w-6" />
            </div>

            <!-- Title & description -->
            <h1 class="mt-4 text-xl font-extrabold font-display text-text tracking-tight">
                {{ title }}
            </h1>
            <p class="mt-2 text-sm text-text-secondary">
                {{ description }}
            </p>

            <!-- Actions -->
            <div class="mt-8 flex items-center justify-center gap-3">
                <button
                    @click="window.history.back()"
                    class="inline-flex items-center gap-2 rounded-full border border-border bg-white px-5 py-2.5 text-xs font-bold text-text-secondary transition hover:bg-surface-hover dark:bg-surface dark:hover:bg-surface-hover"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    Go Back
                </button>
                <a
                    href="/"
                    class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-blue-500/10 transition hover:bg-blue-700"
                >
                    <Home class="h-3.5 w-3.5" />
                    Home
                </a>
            </div>
        </div>
    </div>
</template>
