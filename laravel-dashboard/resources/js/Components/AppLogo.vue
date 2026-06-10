<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    size: { type: String, default: 'md' }, // 'sm' | 'md' | 'lg'
    class: { type: [String, Array, Object], default: '' },
});

const sizeMap = {
    sm: { box: 'h-7 w-7 rounded-lg', icon: 'h-3.5 w-3.5' },
    md: { box: 'h-8 w-8 rounded-xl', icon: 'h-5 w-5' },
    lg: { box: 'h-10 w-10 rounded-xl', icon: 'h-6 w-6' },
};

const boxClass = computed(() =>
    cn(
        'flex shrink-0 items-center justify-center bg-blue-600 text-white shadow-md shadow-blue-500/20',
        sizeMap[props.size].box,
        props.class,
    ),
);

const iconClass = computed(() => sizeMap[props.size].icon);
</script>

<template>
    <div :class="boxClass">
        <svg :class="iconClass" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <polygon
                points="50,12 88,34 88,78 50,100 12,78 12,34"
                stroke="currentColor"
                stroke-width="8"
                stroke-linejoin="round"
            />
            <polygon points="50,30 74,44 74,70 50,84 26,70 26,44" fill="currentColor" />
        </svg>
    </div>
</template>
