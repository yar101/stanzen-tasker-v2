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
        ref="input"
        v-model="model"
        class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0 disabled:bg-neutral-200 disabled:text-neutral-500"
    />
</template>

<!-- new input class
mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-0 -->

<!-- old input class
rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-0 disabled:bg-neutral-200 disabled:text-gray-500 -->
