<template>
  <label class="inline-flex items-center cursor-pointer">
    <div class="relative">
      <input
        type="radio"
        class="sr-only"
        :value="value"
        :checked="modelValue === value"
        @change="select"
        :disabled="disabled"
        :name="name"
      />
      <div
        class="w-5 h-5 rounded-full border transition-all duration-200 flex items-center justify-center"
        :class="modelValue === value ? 'bg-blue-600 border-blue-600' : 'border-gray-400 dark:border-white/30 bg-white dark:bg-white/10'"
      >
        <div v-if="modelValue === value" class="w-2.5 h-2.5 rounded-full bg-white" />
      </div>
    </div>
    <span v-if="label" class="ml-2 text-sm text-gray-900 dark:text-white">{{ label }}</span>
  </label>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: [String, Number, Boolean], required: true },
  value: { type: [String, Number, Boolean], required: true },
  label: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  name: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue']);

function select() {
  try {
    emit('update:modelValue', props.value);
  } catch (e) {
    console.error('[GlassRadio] select error:', e);
  }
}
</script>