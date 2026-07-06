<template>
  <div class="space-y-1.5 relative group">
    <!-- Label + Tooltip trigger -->
    <div class="flex items-center justify-between">
      <label
        class="block text-[10px] uppercase tracking-wider font-medium transition-colors duration-300"
        :class="[
          isFilter
            ? 'text-gray-500 dark:text-white/30'
            : error
              ? 'text-red-400'
              : 'text-gray-500 dark:text-white/40'
        ]"
      >
        {{ label }}
        <span v-if="required" class="text-red-400">*</span>
      </label>

      <!-- Tooltip Icon -->
      <div
        v-if="tooltip"
        class="relative cursor-help w-4 h-4 rounded-full glass-tooltip-trigger flex items-center justify-center text-gray-400 dark:text-white/20 hover:text-gray-600 dark:hover:text-white/50 transition-colors group/tip"
        @mouseenter="showTooltip = true"
        @mouseleave="showTooltip = false"
        @touchstart="showTooltip = !showTooltip"
      >
        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>

        <!-- Tooltip (3D animated) -->
        <div
          v-if="showTooltip"
          class="absolute z-50 bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 p-3 rounded-xl glass-tooltip pointer-events-none"
          style="transform-style: preserve-3d; animation: tooltipFade 0.25s ease-out forwards;"
        >
          <div class="relative" style="transform: rotateX(2deg) rotateY(-2deg);">
            <p class="text-xs text-white/80 leading-relaxed">{{ tooltip }}</p>
            <p v-if="example" class="text-[10px] text-white/20 mt-1.5 font-mono">
              e.g. <span class="text-white/40">{{ example }}</span>
            </p>
            <div
              class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-2.5 h-2.5 rotate-45 glass-tooltip-arrow"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Input slot -->
    <slot>
      <GlassInput
        v-if="!hasCustomInput"
        :modelValue="modelValue"
        @update:modelValue="emit('update:modelValue', $event)"
        :placeholder="placeholder"
        :error="error"
        :type="type"
        :is-filter="isFilter"
      />
    </slot>

    <!-- Error display -->
    <div
      v-if="error"
      class="text-xs text-red-400/80 flex items-center gap-1 animate-shake"
    >
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, useSlots } from 'vue';
import GlassInput from './GlassInput.vue';

const props = defineProps({
  modelValue: { type: [String, Number, Array], default: '' },
  label: { type: String, required: true },
  placeholder: { type: String, default: '' },
  error: { type: String, default: null },
  tooltip: { type: String, default: '' },
  example: { type: String, default: '' },
  type: { type: String, default: 'text' },
  required: { type: Boolean, default: false },
  isFilter: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const showTooltip = ref(false);
const slots = useSlots();
const hasCustomInput = computed(() => !!slots.default);
</script>

<style scoped>
/* Premium Glass Tooltip Trigger */
.glass-tooltip-trigger {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-tooltip-trigger {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(255, 255, 255, 0.06);
}

/* Premium Glass Tooltip */
.glass-tooltip {
  background: rgba(0, 0, 0, 0.9);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  transform-origin: bottom center;
}
.dark .glass-tooltip {
  background: rgba(0, 0, 0, 0.9);
  border-color: rgba(255, 255, 255, 0.1);
}

.glass-tooltip-arrow {
  background: rgba(0, 0, 0, 0.9);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

@keyframes tooltipFade {
  0% {
    opacity: 0;
    transform: scale(0.9) translateY(8px) rotateX(6deg);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0) rotateX(0deg);
  }
}

@keyframes shake {
  0%,
  100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}
.animate-shake { animation: shake 0.3s ease-in-out; }
</style>