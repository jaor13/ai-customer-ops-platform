<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const showMobileMenu = ref(false);

const navigation = [
    { name: 'Dashboard', route: 'dashboard', icon: 'dashboard' },
];
</script>

<template>
    <div class="min-h-screen bg-page relative">
        <!-- Subtle background texture -->
        <div class="fixed inset-0 bg-dot-pattern opacity-[0.4] pointer-events-none"></div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-60 border-r border-border bg-white/60 backdrop-blur-md lg:block dark:bg-surface/60">
            <!-- Logo -->
            <div class="flex h-14 items-center gap-2.5 border-b border-border px-5">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600 shadow-sm shadow-blue-500/10">
                    <svg class="h-3.5 w-3.5 text-white" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="50,12 88,34 88,78 50,100 12,78 12,34" stroke="currentColor" stroke-width="8" stroke-linejoin="round"/>
                        <path d="M50,12 L50,56 L88,78 M50,56 L12,78" stroke="currentColor" stroke-width="6" stroke-linejoin="round"/>
                        <circle cx="50" cy="56" r="10" fill="currentColor"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold font-display uppercase tracking-widest text-text">AI Ops</span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-0.5 px-3 py-4">
                <p class="px-3 mb-2 text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Main</p>
                <Link
                    v-for="item in navigation"
                    :key="item.route"
                    :href="route(item.route)"
                    :class="[
                        route().current(item.route)
                            ? 'bg-blue-50 text-blue-600 border-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20'
                            : 'text-text-secondary hover:text-text hover:bg-surface-hover border-transparent',
                        'flex items-center gap-2.5 rounded-xl border px-3 py-2 text-xs font-semibold transition-all duration-200'
                    ]"
                >
                    <!-- Dashboard Icon -->
                    <svg v-if="item.icon === 'dashboard'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User Section -->
            <div class="absolute bottom-0 left-0 right-0 border-t border-border p-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-50 text-[10px] font-bold text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 truncate">
                        <p class="truncate text-xs font-semibold text-text">{{ $page.props.auth.user.name }}</p>
                        <p class="truncate text-[10px] text-text-tertiary">{{ $page.props.auth.user.email }}</p>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <Link
                        :href="route('profile.edit')"
                        class="flex-1 rounded-lg border border-border px-2.5 py-1.5 text-center text-[10px] font-semibold text-text-secondary transition hover:bg-surface-hover"
                    >
                        Settings
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex-1 rounded-lg border border-border px-2.5 py-1.5 text-center text-[10px] font-semibold text-text-secondary transition hover:bg-surface-hover"
                    >
                        Sign Out
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Mobile Header -->
        <div class="sticky top-0 z-30 flex h-14 items-center gap-4 border-b border-border bg-white/70 backdrop-blur-md px-4 lg:hidden dark:bg-surface/70">
            <button
                @click="showMobileMenu = !showMobileMenu"
                class="rounded-lg p-2 text-text-secondary hover:bg-surface-hover transition"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <div class="flex h-6 w-6 items-center justify-center rounded-md bg-blue-600">
                    <svg class="h-3 w-3 text-white" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="50,12 88,34 88,78 50,100 12,78 12,34" stroke="currentColor" stroke-width="8" stroke-linejoin="round"/>
                        <path d="M50,12 L50,56 L88,78 M50,56 L12,78" stroke="currentColor" stroke-width="6" stroke-linejoin="round"/>
                        <circle cx="50" cy="56" r="10" fill="currentColor"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold font-display uppercase tracking-widest text-text">AI Ops</span>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div
            v-if="showMobileMenu"
            class="fixed inset-0 z-40 lg:hidden"
            @click="showMobileMenu = false"
        >
            <div class="fixed inset-0 bg-black/30 backdrop-blur-sm"></div>
            <aside class="fixed inset-y-0 left-0 w-60 border-r border-border bg-white dark:bg-surface" @click.stop>
                <div class="flex h-14 items-center gap-2.5 border-b border-border px-5">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600">
                        <svg class="h-3.5 w-3.5 text-white" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <polygon points="50,12 88,34 88,78 50,100 12,78 12,34" stroke="currentColor" stroke-width="8" stroke-linejoin="round"/>
                            <path d="M50,12 L50,56 L88,78 M50,56 L12,78" stroke="currentColor" stroke-width="6" stroke-linejoin="round"/>
                            <circle cx="50" cy="56" r="10" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold font-display uppercase tracking-widest text-text">AI Ops</span>
                </div>
                <nav class="space-y-0.5 px-3 py-4">
                    <p class="px-3 mb-2 text-[9px] font-bold uppercase tracking-widest text-text-tertiary">Main</p>
                    <Link
                        v-for="item in navigation"
                        :key="item.route"
                        :href="route(item.route)"
                        :class="[
                            route().current(item.route)
                                ? 'bg-blue-50 text-blue-600 border-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20'
                                : 'text-text-secondary hover:text-text hover:bg-surface-hover border-transparent',
                            'flex items-center gap-2.5 rounded-xl border px-3 py-2 text-xs font-semibold transition-all duration-200'
                        ]"
                        @click="showMobileMenu = false"
                    >
                        <svg v-if="item.icon === 'dashboard'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                        {{ item.name }}
                    </Link>
                </nav>
            </aside>
        </div>

        <!-- Main Content -->
        <div class="lg:pl-60 relative z-10">
            <!-- Page Heading -->
            <header v-if="$slots.header" class="border-b border-border bg-white/50 backdrop-blur-sm px-6 py-4 dark:bg-surface/50">
                <slot name="header" />
            </header>

            <!-- Page Content -->
            <main class="px-4 py-6 sm:px-6">
                <slot />
            </main>
        </div>
    </div>
</template>
