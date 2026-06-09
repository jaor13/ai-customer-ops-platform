<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block text-[10px] font-bold uppercase tracking-widest text-text-secondary">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
