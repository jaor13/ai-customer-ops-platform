<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    interactive: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});

const classes = computed(() =>
    cn(
        'rounded-2xl border border-border bg-white/80 backdrop-blur-sm text-text shadow-sm transition-all duration-300 dark:bg-surface/80',
        props.interactive && 'hover:border-primary/30 hover:shadow-lg',
        props.class,
    ),
);
</script>

<template>
    <div :class="classes">
        <slot />
    </div>
</template>
