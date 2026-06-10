<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from './index.js';

const props = defineProps({
    variant: { type: String, default: 'default' },
    size: { type: String, default: 'default' },
    shape: { type: String, default: 'pill' },
    as: { type: String, default: 'button' },
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});

const classes = computed(() =>
    cn(
        buttonVariants({
            variant: props.variant,
            size: props.size,
            shape: props.shape,
        }),
        props.class,
    ),
);
</script>

<template>
    <component
        :is="as"
        :type="as === 'button' ? type : undefined"
        :disabled="as === 'button' ? disabled : undefined"
        :class="classes"
    >
        <slot />
    </component>
</template>
