<template>
  <div class="flex flex-col items-center justify-center py-16 text-center">
    <component v-if="icon" :is="icon" class="w-12 h-12 text-gray-400 dark:text-white/30 mb-4" />
    <h3 class="text-lg font-semibold text-gray-700 dark:text-white/70 mb-2">{{ title }}</h3>
    <p class="text-sm text-gray-500 dark:text-white/50 max-w-md">{{ description }}</p>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: 'Nothing here yet' },
  description: { type: String, default: '' },
  icon: { type: Object, default: null },
});
</script>