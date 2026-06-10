<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: [String, Array, Object], default: '' },
});

const classes = computed(() => cn('p-5 pt-0', props.class));
</script>

<template>
    <div :class="classes">
        <slot />
    </div>
</template>
