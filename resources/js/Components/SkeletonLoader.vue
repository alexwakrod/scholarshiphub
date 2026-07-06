<template>
  <div class="animate-pulse">
    <div v-if="type === 'card'" class="glass-surface rounded-xl p-4 space-y-3">
      <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-3/4"></div>
      <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/2"></div>
      <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-5/6"></div>
    </div>
    <div v-else-if="type === 'list'" class="space-y-3">
      <div v-for="n in count" :key="n" class="flex items-center space-x-3">
        <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-white/10"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-3/4"></div>
          <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/2"></div>
        </div>
      </div>
    </div>
    <div v-else-if="type === 'table'" class="space-y-2">
      <div class="h-6 bg-gray-200 dark:bg-white/10 rounded w-full"></div>
      <div v-for="n in count" :key="n" class="h-4 bg-gray-200 dark:bg-white/10 rounded w-full"></div>
    </div>
    <div v-else class="h-20 bg-gray-200 dark:bg-white/10 rounded"></div>
  </div>
</template>

<script setup>
defineProps({
  type: { type: String, default: 'card', validator: (v) => ['card', 'list', 'table'].includes(v) },
  count: { type: Number, default: 3 },
});
</script>

<style scoped>
.animate-pulse {
  animation: pulse 1.5s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 0.4; }
  50% { opacity: 0.8; }
}
</style>