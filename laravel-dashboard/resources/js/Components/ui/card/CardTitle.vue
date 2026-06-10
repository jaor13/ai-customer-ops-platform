<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    as: { type: String, default: 'h3' },
    class: { type: [String, Array, Object], default: '' },
});

const classes = computed(() =>
    cn('text-sm font-extrabold font-display tracking-tight text-text', props.class),
);
</script>

<template>
    <component :is="as" :class="classes">
        <slot />
    </component>
</template>
