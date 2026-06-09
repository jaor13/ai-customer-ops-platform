<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-[#FAF8F5] text-slate-900 font-sans selection:bg-blue-500 selection:text-white overflow-hidden relative">
        
        <!-- Left Panel: Dark DOSS grid illustration (Desktop only) -->
        <div class="lg:col-span-6 xl:col-span-7 bg-slate-950 text-white relative overflow-hidden flex flex-col justify-between p-12 hidden lg:flex border-r border-slate-900">
            <!-- 3D Perspective Grid Background (DOSS style) -->
            <div class="absolute inset-0 w-full h-full perspective-grid opacity-20 pointer-events-none">
                <div class="w-full h-[200%] perspective-grid-inner"></div>
            </div>

            <!-- Glowing light orbs -->
            <div class="absolute top-[30%] left-[25%] h-[300px] w-[300px] rounded-full bg-blue-500/5 blur-[80px] pointer-events-none"></div>

            <!-- Header logo -->
            <div class="relative z-10">
                <Link href="/" class="flex items-center gap-2.5 group">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 shadow-md shadow-blue-500/10 group-hover:scale-105 transition-transform duration-300">
                        <svg class="h-4.5 w-4.5 text-white" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <polygon points="50,12 88,34 88,78 50,100 12,78 12,34" stroke="currentColor" stroke-width="8" stroke-linejoin="round"/>
                            <path d="M50,12 L50,56 L88,78 M50,56 L12,78" stroke="currentColor" stroke-width="6" stroke-linejoin="round"/>
                            <circle cx="50" cy="56" r="10" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold font-display uppercase tracking-widest text-white group-hover:text-blue-400 transition-colors">AI Ops</span>
                </Link>
            </div>

            <!-- Floating visual simulation -->
            <div class="relative z-10 m-auto max-w-sm w-full space-y-4">
                <div class="rounded-2xl border border-white/10 p-5 shadow-2xl bg-white/5 backdrop-blur-md animate-float">
                    <div class="flex items-center justify-between border-b border-white/5 pb-3 mb-3">
                        <span class="text-[10px] font-bold text-white flex items-center gap-1.5 font-mono">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                            INCOMING EMAIL INTAKE
                        </span>
                        <span class="text-[9px] font-mono text-white/40">10s ago</span>
                    </div>
                    <div class="text-xs text-white/70 font-mono leading-relaxed line-clamp-2">
                        "Hi Dave support, we have a midnight DB connection timeout problem on Qdrant nodes..."
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 p-5 shadow-2xl bg-white/5 backdrop-blur-md animate-float" style="animation-delay: -3s;">
                    <div class="flex items-center justify-between border-b border-white/5 pb-3 mb-3">
                        <span class="text-[10px] font-bold text-white flex items-center gap-1.5 font-mono">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            AI AUTO-DRAFT RESPONSE
                        </span>
                        <span class="text-[9px] font-mono text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20">94% Confidence</span>
                    </div>
                    <div class="text-xs text-white/95 font-mono leading-relaxed">
                        "Hi Dave, checking our manuals, this timeout is resolved by adjusting midnight cron schedules..."
                    </div>
                </div>
            </div>

            <!-- Testimonial/Tagline -->
            <div class="relative z-10 space-y-2">
                <h3 class="text-base font-bold font-display text-white">Trust the loop, speed the scale.</h3>
                <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                    Keep full control with human-in-the-loop validation while AI handles the intake triage and grounding search.
                </p>
            </div>
        </div>

        <!-- Right Panel: Form view -->
        <div class="col-span-1 lg:col-span-6 xl:col-span-5 flex flex-col justify-center items-center p-6 sm:p-12 relative">
            <!-- Dotted pattern backdrop (matching Welcome.vue) -->
            <div class="absolute inset-0 bg-dot-pattern opacity-[0.5] pointer-events-none" style="-webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 100%); mask-image: radial-gradient(circle at center, black 40%, transparent 100%);"></div>

            <div class="w-full max-w-md relative z-10">
                <!-- Mobile brand logo (Hidden on desktop) -->
                <div class="flex lg:hidden justify-center mb-8">
                    <Link href="/" class="flex items-center gap-2.5 group">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 shadow-md">
                            <svg class="h-4.5 w-4.5 text-white" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <polygon points="50,12 88,34 88,78 50,100 12,78 12,34" stroke="currentColor" stroke-width="8" stroke-linejoin="round"/>
                                <path d="M50,12 L50,56 L88,78 M50,56 L12,78" stroke="currentColor" stroke-width="6" stroke-linejoin="round"/>
                                <circle cx="50" cy="56" r="10" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold font-display uppercase tracking-widest text-slate-900">AI Ops</span>
                    </Link>
                </div>

                <!-- Form Card -->
                <div class="rounded-2xl border border-slate-200/60 bg-white p-6 sm:p-8 shadow-xl">
                    <slot />
                </div>
            </div>
        </div>

    </div>
</template>
