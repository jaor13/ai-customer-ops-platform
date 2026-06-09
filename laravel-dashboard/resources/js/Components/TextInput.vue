<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border-border bg-white text-text placeholder-text-tertiary shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-blue-500/20 focus:ring-2 dark:bg-surface"
        v-model="model"
        ref="input"
    />
</template>
