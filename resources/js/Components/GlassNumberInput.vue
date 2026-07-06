<template>
  <div
    class="group glass-number-input-wrapper inline-flex items-stretch rounded-2xl border transition-all duration-300 select-none"
    :class="[
      disabled ? 'opacity-50 cursor-not-allowed' : '',
      error ? 'border-red-500/60 shadow-[0_0_20px_rgba(239,68,68,0.15)]' : 'border-gray-300 dark:border-white/10',
      focused && !error ? 'border-blue-500/60 dark:border-blue-400/50 shadow-[0_0_30px_rgba(59,130,246,0.15)]' : '',
      isFilter ? 'filter-glass' : 'elevated-glass',
    ]"
    :style="{ backdropFilter: 'blur(16px)' }"
  >
    <!-- Decrement Toggle -->
    <button
      type="button"
      @click="decrement"
      :disabled="disabled || (min !== undefined && Number(modelValue) <= min)"
      class="glass-number-btn group/btn relative flex items-center justify-center px-3 py-2 transition-all duration-200 focus:outline-none"
      :class="[
        (disabled || (min !== undefined && Number(modelValue) <= min)) ? 'opacity-30 cursor-not-allowed' : 'hover:scale-105 active:scale-95',
      ]"
      aria-label="Decrease"
    >
      <!-- Micro‑thin vertical seam -->
      <span class="absolute right-0 top-1/4 bottom-1/4 w-px bg-gradient-to-b from-transparent via-gray-400/30 dark:via-white/20 to-transparent"></span>
      <!-- Engraved minus icon -->
      <svg
        class="w-3 h-3 glass-icon transition-all duration-300"
        :class="[
          (disabled || (min !== undefined && Number(modelValue) <= min)) ? 'opacity-20' : 'opacity-40 group-hover/btn:opacity-100 group-hover/btn:drop-shadow-[0_0_4px_rgba(59,130,246,0.6)]',
        ]"
        fill="none" stroke="currentColor" viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" />
      </svg>
      <!-- Haptic radiant area -->
      <span class="absolute inset-0 -m-3"></span>
    </button>

    <!-- Numeric Input (recessed trench) -->
    <input
      type="text"
      inputmode="numeric"
      :value="modelValue"
      @input="handleInput"
      @blur="formatValue"
      @focus="focused = true"
      @focusout="focused = false"
      :disabled="disabled"
      :placeholder="placeholder"
      class="glass-number-input flex-1 w-16 text-center bg-transparent border-none outline-none text-sm py-2 font-mono tabular-nums text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-white/20"
    />

    <!-- Increment Toggle -->
    <button
      type="button"
      @click="increment"
      :disabled="disabled || (max !== undefined && Number(modelValue) >= max)"
      class="glass-number-btn group/btn relative flex items-center justify-center px-3 py-2 transition-all duration-200 focus:outline-none"
      :class="[
        (disabled || (max !== undefined && Number(modelValue) >= max)) ? 'opacity-30 cursor-not-allowed' : 'hover:scale-105 active:scale-95',
      ]"
      aria-label="Increase"
    >
      <!-- Micro‑thin vertical seam -->
      <span class="absolute left-0 top-1/4 bottom-1/4 w-px bg-gradient-to-b from-transparent via-gray-400/30 dark:via-white/20 to-transparent"></span>
      <!-- Engraved plus icon -->
      <svg
        class="w-3 h-3 glass-icon transition-all duration-300"
        :class="[
          (disabled || (max !== undefined && Number(modelValue) >= max)) ? 'opacity-20' : 'opacity-40 group-hover/btn:opacity-100 group-hover/btn:drop-shadow-[0_0_4px_rgba(59,130,246,0.6)]',
        ]"
        fill="none" stroke="currentColor" viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
      </svg>
      <!-- Haptic radiant area -->
      <span class="absolute inset-0 -m-3"></span>
    </button>

    <!-- Error shake animation -->
    <div v-if="error" class="absolute inset-0 rounded-2xl pointer-events-none glass-error-overlay animate-shake"></div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: { type: [Number, String], default: 0 },
  disabled: { type: Boolean, default: false },
  min: { type: Number, default: undefined },
  max: { type: Number, default: undefined },
  step: { type: Number, default: 1 },
  placeholder: { type: String, default: '' },
  isFilter: { type: Boolean, default: false },
  error: { type: Boolean, default: false }, // added for visual error state
});

const emit = defineEmits(['update:modelValue']);
const focused = ref(false);

function handleInput(event) {
  let val = event.target.value.replace(/[^0-9.-]/g, '');
  emit('update:modelValue', val);
}

function formatValue() {
  let num = parseFloat(props.modelValue);
  if (isNaN(num)) {
    emit('update:modelValue', props.min ?? 0);
    return;
  }
  if (props.min !== undefined) num = Math.max(num, props.min);
  if (props.max !== undefined) num = Math.min(num, props.max);
  emit('update:modelValue', num);
}

function increment() {
  let num = parseFloat(props.modelValue) || 0;
  num += props.step;
  if (props.max !== undefined) num = Math.min(num, props.max);
  emit('update:modelValue', num);
}

function decrement() {
  let num = parseFloat(props.modelValue) || 0;
  num -= props.step;
  if (props.min !== undefined) num = Math.max(num, props.min);
  emit('update:modelValue', num);
}
</script>

<style scoped>
/* ============================================================
   GLASS NUMBER INPUT – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* ---- Base elevated glass ---- */
.elevated-glass {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  box-shadow:
    0 8px 24px rgba(0, 0, 0, 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.5);
  color: #111827;
}
.dark .elevated-glass {
  background: rgba(255, 255, 255, 0.06);
  box-shadow:
    0 8px 24px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
  color: #f3f4f6;
}

.filter-glass {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border-color: rgba(0, 0, 0, 0.06);
}
.dark .filter-glass {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.06);
}

/* ---- Toggle buttons ---- */
.glass-number-btn {
  /* micro tilt is applied by parent's group perspective if needed; we rely on hover scale only */
  transition: transform 0.2s ease;
}
.glass-number-btn:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
}

/* ---- Input field ---- */
.glass-number-input {
  font-variant-numeric: tabular-nums;
}
.glass-number-input::placeholder {
  opacity: 0.2;
}
/* Remove native number spinners */
.glass-number-input::-webkit-outer-spin-button,
.glass-number-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.glass-number-input[type='number'] {
  -moz-appearance: textfield;
}

/* ---- Icons (engraved effect) ---- */
.glass-icon {
  stroke: currentColor;
}

/* ---- Autofill transparency ---- */
.glass-number-input:-webkit-autofill,
.glass-number-input:-webkit-autofill:hover,
.glass-number-input:-webkit-autofill:focus,
.glass-number-input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 30px transparent inset !important;
  box-shadow: 0 0 0 30px transparent inset !important;
  -webkit-text-fill-color: currentColor !important;
  transition: background-color 5000s ease-in-out 0s;
}

/* ---- Error shake & glow ---- */
.animate-shake {
  animation: shake 0.4s ease-in-out;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

.glass-error-overlay {
  background: rgba(239, 68, 68, 0.05);
  border-radius: inherit;
}

/* ---- Mobile & accessibility ---- */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-number-btn {
    transform: none !important;
  }
  .glass-number-btn:hover:not(:disabled) {
    transform: none !important;
    scale: 1;
  }
  .glass-number-btn:active:not(:disabled) {
    transform: scale(0.95) translateY(1px);
  }
}

@media (prefers-reduced-motion: reduce) {
  .glass-number-btn,
  .glass-number-btn:hover,
  .glass-number-btn:active {
    transition: none !important;
    transform: none !important;
  }
  .animate-shake {
    animation: none !important;
  }
}
</style>