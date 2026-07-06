<template>
  <label class="inline-flex items-center gap-2 group cursor-pointer">
    <div class="relative">
      <input
        type="checkbox"
        class="sr-only"
        :checked="modelValue"
        @change="toggle"
        :disabled="disabled"
      />
      <!-- Track -->
      <div
        class="block w-10 h-6 rounded-full transition-all duration-300"
        :class="[
          modelValue
            ? 'bg-blue-500 shadow-[0_0_12px_rgba(59,130,246,0.4)]'
            : 'bg-gray-300 dark:bg-white/20',
          disabled ? 'opacity-40 cursor-not-allowed' : 'group-hover:scale-[1.03]'
        ]"
      ></div>
      <!-- Knob -->
      <div
        class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full transition-all duration-300 shadow-md"
        :class="[
          modelValue
            ? 'translate-x-4 bg-white dark:bg-white'
            : 'translate-x-0 bg-white dark:bg-gray-300',
          disabled ? '' : 'group-hover:shadow-lg'
        ]"
      ></div>
    </div>
    <span
      v-if="label"
      class="text-sm font-medium transition-colors duration-200 select-none"
      :class="[
        disabled
          ? 'text-gray-400 dark:text-white/30'
          : 'text-gray-900 dark:text-white/70 group-hover:text-gray-900 dark:group-hover:text-white'
      ]"
    >
      {{ label }}
    </span>
  </label>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  label: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);

function toggle(event) {
  try {
    emit('update:modelValue', event.target.checked);
  } catch (e) {
    console.error('[GlassToggle] toggle error:', e);
  }
}
</script>