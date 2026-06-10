<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: [String, Array, Object], default: '' },
});

const classes = computed(() => cn('text-[11px] text-text-secondary', props.class));
</script>

<template>
    <p :class="classes">
        <slot />
    </p>
</template>
