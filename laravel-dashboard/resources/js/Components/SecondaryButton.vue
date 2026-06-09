<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-full border border-border bg-white px-5 py-2.5 text-xs font-bold text-text-secondary shadow-sm transition-all duration-300 hover:bg-surface-hover hover:border-blue-200 hover:text-blue-600 disabled:opacity-50 dark:bg-surface"
    >
        <slot />
    </button>
</template>
