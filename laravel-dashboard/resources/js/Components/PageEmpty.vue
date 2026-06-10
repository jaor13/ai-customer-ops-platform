<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col items-center text-center py-12">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
            <slot name="icon" />
        </div>
        <h3 class="mt-4 text-sm font-extrabold font-display text-text">{{ title }}</h3>
        <p v-if="description" class="mt-1 text-xs text-text-secondary max-w-sm">{{ description }}</p>
        <div class="mt-4">
            <slot />
        </div>
    </div>
</template>
