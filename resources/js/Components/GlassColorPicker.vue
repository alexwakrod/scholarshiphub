<template>
  <div class="relative inline-flex items-center gap-3">
    <input
      type="color"
      :value="modelValue"
      @input="handleInput"
      :disabled="disabled"
      class="w-10 h-10 rounded-full border-2 border-white/20 overflow-hidden cursor-pointer bg-transparent"
    />
    <input
      type="text"
      :value="modelValue"
      @input="handleHexInput"
      :disabled="disabled"
      placeholder="#FFFFFF"
      class="w-24 px-2 py-1 text-sm rounded-lg glass-input border border-white/20 text-white outline-none"
    />
    <div class="flex gap-1">
      <button
        v-for="swatch in presets"
        :key="swatch"
        @click="setColor(swatch)"
        class="w-6 h-6 rounded-full border border-white/20"
        :style="{ backgroundColor: swatch }"
      ></button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: String, default: '#3B82F6' },
  disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);

const presets = ['#3B82F6', '#FFD700', '#10B981', '#EF4444', '#8B5CF6', '#F59E0B'];

function handleInput(event) {
  try {
    emit('update:modelValue', event.target.value);
  } catch (e) {
    console.error('[GlassColorPicker] handleInput error:', e);
  }
}

function handleHexInput(event) {
  try {
    let val = event.target.value;
    if (!val.startsWith('#')) val = '#' + val;
    if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
      emit('update:modelValue', val);
    }
  } catch (e) {
    console.error('[GlassColorPicker] handleHexInput error:', e);
  }
}

function setColor(color) {
  try {
    emit('update:modelValue', color);
  } catch (e) {
    console.error('[GlassColorPicker] setColor error:', e);
  }
}
</script>

<style scoped>
.glass-input {
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(8px);
}
</style>