<template>
  <label class="inline-flex items-center gap-2 group cursor-pointer">
    <div class="relative">
      <input
        type="checkbox"
        :checked="modelValue"
        @change="emit('update:modelValue', $event.target.checked)"
        :disabled="disabled"
        class="sr-only"
      />
      <div
        class="w-5 h-5 rounded-md transition-all duration-300 flex items-center justify-center"
        :class="[
          modelValue
            ? 'bg-blue-600/30 border-blue-500/50 shadow-[0_0_20px_rgba(59,130,246,0.2)]'
            : 'bg-white/10 border-gray-300 dark:border-white/20',
          disabled
            ? 'opacity-40 cursor-not-allowed'
            : 'group-hover:border-gray-400 dark:group-hover:border-white/40 group-hover:scale-[1.05]',
          'border'
        ]"
      >
        <!-- Heroicons checkmark -->
        <svg
          v-if="modelValue"
          class="w-3.5 h-3.5 text-white transition-all duration-300 animate-check"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
      </div>
    </div>
    <span
      v-if="label"
      class="text-sm transition-colors duration-200 select-none"
      :class="[
        disabled
          ? 'text-gray-400 dark:text-white/30'
          : 'text-gray-700 dark:text-white/60 group-hover:text-gray-900 dark:group-hover:text-white/80'
      ]"
    >
      {{ label }}
    </span>
  </label>
</template>

<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
  label: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);
</script>

<style scoped>
/* Micro-interactive reduced 3D: no rotation, only subtle scale */
.group-hover\:scale-\[1\.05\] {
  transform: scale(1.05);
}

/* Checkmark animation */
@keyframes check {
  0% { transform: scale(0); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}
.animate-check {
  animation: check 0.25s ease-out forwards;
}
</style>