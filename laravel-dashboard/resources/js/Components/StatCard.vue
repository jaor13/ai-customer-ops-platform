<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    accent: {
        type: String,
        default: 'blue',
        validator: (v) =>
            ['blue', 'sky', 'indigo', 'emerald', 'amber', 'red', 'violet'].includes(v),
    },
    class: { type: [String, Array, Object], default: '' },
});

const accentMap = {
    blue: {
        icon: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
        ring: 'hover:border-blue-200 dark:hover:border-blue-500/30',
    },
    sky: {
        icon: 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
        ring: 'hover:border-sky-200 dark:hover:border-sky-500/30',
    },
    indigo: {
        icon: 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
        ring: 'hover:border-indigo-200 dark:hover:border-indigo-500/30',
    },
    emerald: {
        icon: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        ring: 'hover:border-emerald-200 dark:hover:border-emerald-500/30',
    },
    amber: {
        icon: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        ring: 'hover:border-amber-200 dark:hover:border-amber-500/30',
    },
    red: {
        icon: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
        ring: 'hover:border-red-200 dark:hover:border-red-500/30',
    },
    violet: {
        icon: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
        ring: 'hover:border-violet-200 dark:hover:border-violet-500/30',
    },
};

const cardClasses = computed(() =>
    cn(
        'group rounded-2xl border border-border bg-white/80 backdrop-blur-sm p-5 transition-all duration-300 hover:shadow-lg dark:bg-surface/80',
        accentMap[props.accent].ring,
        props.class,
    ),
);

const iconClasses = computed(() =>
    cn(
        'flex h-8 w-8 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110',
        accentMap[props.accent].icon,
    ),
);
</script>

<template>
    <div :class="cardClasses">
        <div class="flex items-center justify-between">
            <span class="text-[9px] font-bold uppercase tracking-widest text-text-tertiary">
                {{ label }}
            </span>
            <div :class="iconClasses">
                <slot name="icon" />
            </div>
        </div>
        <p class="mt-4 text-3xl font-extrabold font-display text-text leading-none">
            {{ value }}
        </p>
        <div class="mt-2.5 flex items-center gap-1.5">
            <slot name="trend" />
        </div>
    </div>
</template>
