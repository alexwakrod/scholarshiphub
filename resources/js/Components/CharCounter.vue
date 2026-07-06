<template>
  <div class="flex justify-between items-center text-xs" :class="counterColor">
    <span v-if="label">{{ label }}</span>
    <span class="ml-auto">{{ current }} / {{ max }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  current: { type: Number, default: 0 },
  max: { type: Number, default: 200 },
  label: { type: String, default: '' },
});

const counterColor = computed(() => {
  const ratio = props.current / props.max;
  if (ratio >= 1) return 'text-red-500 dark:text-red-400';
  if (ratio >= 0.9) return 'text-yellow-500 dark:text-yellow-400';
  return 'text-gray-500 dark:text-white/50';
});
</script>