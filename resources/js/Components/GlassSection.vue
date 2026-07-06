<template>
  <div class="glass-section rounded-2xl border border-gray-200 dark:border-white/5 p-4 transition-all duration-300 hover:bg-gray-50 dark:hover:bg-white/[0.04]">
    <div class="flex items-center gap-2.5 mb-4">
      <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="iconBg">
        <component :is="icon" class="w-4 h-4" :class="iconColor" />
      </div>
      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
      <span v-if="required" class="text-[10px] text-red-400 dark:text-red-300 ml-1">*required</span>
    </div>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  icon: { type: Object, required: true },
  iconBg: { type: String, default: 'bg-gray-100 dark:bg-white/5' },
  iconColor: { type: String, default: 'text-gray-500 dark:text-white/40' },
  required: { type: Boolean, default: false },
});
</script>

<style scoped>
.glass-section {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
}
.dark .glass-section {
  background: rgba(255, 255, 255, 0.02);
}
</style>