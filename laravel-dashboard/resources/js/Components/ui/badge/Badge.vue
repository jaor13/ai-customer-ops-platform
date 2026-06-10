<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import { badgeVariants } from './index.js';

const props = defineProps({
    variant: { type: String, default: 'default' },
    class: { type: [String, Array, Object], default: '' },
});

const classes = computed(() =>
    cn(badgeVariants({ variant: props.variant }), props.class),
);
</script>

<template>
    <span :class="classes">
        <slot />
    </span>
</template>
