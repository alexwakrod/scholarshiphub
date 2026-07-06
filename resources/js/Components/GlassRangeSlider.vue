<template>
  <div class="relative w-full select-none" ref="sliderRef">
    <!-- Recessed track with ambient inner shadow -->
    <div
      class="glass-slider-track relative h-2 rounded-full cursor-pointer"
      @mousedown.stop="onTrackMouseDown"
      @touchstart.stop="onTrackTouchStart"
    >
      <!-- Glowing liquid fill (active range) -->
      <div
        class="glass-slider-fill absolute top-0 h-full rounded-full"
        :style="{ left: leftPercent + '%', width: (rightPercent - leftPercent) + '%' }"
      >
        <!-- Shimmer overlay -->
        <div class="glass-slider-shimmer absolute inset-0 rounded-full"></div>
      </div>

      <!-- Left Knob -->
      <div
        ref="leftThumb"
        class="glass-slider-knob group/left absolute top-1/2 -translate-y-1/2 w-5 h-5 rounded-full cursor-grab active:cursor-grabbing"
        :style="{ left: 'calc(' + leftPercent + '% - 10px)' }"
        @mousedown.stop="onThumbMouseDown($event, 'left')"
        @touchstart.stop="onThumbTouchStart($event, 'left')"
      >
        <!-- Haptic radiant area -->
        <span class="absolute inset-[-8px] bg-transparent"></span>
        <!-- Ephemeral tooltip (visible on hover/active) -->
        <div class="glass-slider-tooltip absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 transition-opacity duration-200 group-hover/left:opacity-100 group-active/left:opacity-100 pointer-events-none">
          <span class="glass-slider-tooltip-body">{{ leftValue }}</span>
          <div class="glass-slider-spire"></div>
        </div>
      </div>

      <!-- Right Knob -->
      <div
        ref="rightThumb"
        class="glass-slider-knob group/right absolute top-1/2 -translate-y-1/2 w-5 h-5 rounded-full cursor-grab active:cursor-grabbing"
        :style="{ left: 'calc(' + rightPercent + '% - 10px)' }"
        @mousedown.stop="onThumbMouseDown($event, 'right')"
        @touchstart.stop="onThumbTouchStart($event, 'right')"
      >
        <span class="absolute inset-[-8px] bg-transparent"></span>
        <div class="glass-slider-tooltip absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 transition-opacity duration-200 group-hover/right:opacity-100 group-active/right:opacity-100 pointer-events-none">
          <span class="glass-slider-tooltip-body">{{ rightValue }}</span>
          <div class="glass-slider-spire"></div>
        </div>
      </div>
    </div>

    <!-- Boundary labels -->
    <div class="flex justify-between text-xs text-gray-500 dark:text-white/40 mt-1.5 tracking-wide">
      <span>{{ min }}</span>
      <span>{{ max }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';

const props = defineProps({
  modelValue: { type: Array, default: () => [0, 100] },
  min: { type: Number, default: 0 },
  max: { type: Number, default: 100 },
  step: { type: Number, default: 1 },
  disabled: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);

const sliderRef = ref(null);
const leftThumb = ref(null);
const rightThumb = ref(null);
const dragging = ref(null);

function clamp(value) {
  return Math.min(props.max, Math.max(props.min, value));
}

function stepped(value) {
  return Math.round(value / props.step) * props.step;
}

const leftValue = computed(() => {
  const val = props.modelValue?.[0] ?? props.min;
  return stepped(clamp(val));
});
const rightValue = computed(() => {
  const val = props.modelValue?.[1] ?? props.max;
  return stepped(clamp(val));
});
const leftPercent = computed(() => ((leftValue.value - props.min) / (props.max - props.min)) * 100);
const rightPercent = computed(() => ((rightValue.value - props.min) / (props.max - props.min)) * 100);

function getValueFromClientX(clientX) {
  if (!sliderRef.value) return props.min;
  const rect = sliderRef.value.getBoundingClientRect();
  const x = clientX - rect.left;
  const pct = Math.min(1, Math.max(0, x / rect.width));
  return stepped(props.min + pct * (props.max - props.min));
}

function onTrackMouseDown(e) {
  if (props.disabled) return;
  dragging.value = 'track';
  document.addEventListener('mousemove', onMouseMove);
  document.addEventListener('mouseup', onMouseUp);
  const val = getValueFromClientX(e.clientX);
  updateClosestThumb(val);
}
function onThumbMouseDown(e, thumb) {
  if (props.disabled) return;
  e.stopPropagation();
  dragging.value = thumb;
  document.addEventListener('mousemove', onMouseMove);
  document.addEventListener('mouseup', onMouseUp);
}
function onTrackTouchStart(e) {
  if (props.disabled) return;
  dragging.value = 'track';
  document.addEventListener('touchmove', onTouchMove, { passive: false });
  document.addEventListener('touchend', onTouchEnd);
  const touch = e.touches[0];
  const val = getValueFromClientX(touch.clientX);
  updateClosestThumb(val);
}
function onThumbTouchStart(e, thumb) {
  if (props.disabled) return;
  e.stopPropagation();
  dragging.value = thumb;
  document.addEventListener('touchmove', onTouchMove, { passive: false });
  document.addEventListener('touchend', onTouchEnd);
}
function onMouseMove(e) {
  e.preventDefault();
  const val = getValueFromClientX(e.clientX);
  updateThumb(val);
}
function onTouchMove(e) {
  e.preventDefault();
  const touch = e.touches[0];
  const val = getValueFromClientX(touch.clientX);
  updateThumb(val);
}
function onMouseUp() {
  document.removeEventListener('mousemove', onMouseMove);
  document.removeEventListener('mouseup', onMouseUp);
  dragging.value = null;
}
function onTouchEnd() {
  document.removeEventListener('touchmove', onTouchMove);
  document.removeEventListener('touchend', onTouchEnd);
  dragging.value = null;
}

function updateClosestThumb(val) {
  const distLeft = Math.abs(val - leftValue.value);
  const distRight = Math.abs(val - rightValue.value);
  if (distLeft <= distRight) updateThumb(val, 'left');
  else updateThumb(val, 'right');
}

function updateThumb(val, forcedSide = null) {
  const side = forcedSide || dragging.value;
  if (!side || side === 'track') return;
  let newLeft = leftValue.value;
  let newRight = rightValue.value;
  if (side === 'left') {
    newLeft = clamp(val);
    if (newLeft > rightValue.value - props.step) newLeft = rightValue.value - props.step;
  } else {
    newRight = clamp(val);
    if (newRight < leftValue.value + props.step) newRight = leftValue.value + props.step;
  }
  emit('update:modelValue', [stepped(newLeft), stepped(newRight)]);
}

onBeforeUnmount(() => {
  document.removeEventListener('mousemove', onMouseMove);
  document.removeEventListener('mouseup', onMouseUp);
  document.removeEventListener('touchmove', onTouchMove);
  document.removeEventListener('touchend', onTouchEnd);
});
</script>

<style scoped>
/* ============================================================
   GLASS RANGE SLIDER – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* ---- Recessed track ---- */
.glass-slider-track {
  background: rgba(0, 0, 0, 0.08);
  box-shadow:
    inset 0 2px 4px rgba(0, 0, 0, 0.15),
    inset 0 -1px 0 rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.05);
}
.dark .glass-slider-track {
  background: rgba(255, 255, 255, 0.05);
  box-shadow:
    inset 0 2px 4px rgba(0, 0, 0, 0.4),
    inset 0 -1px 0 rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.08);
}

/* ---- Active liquid fill ---- */
.glass-slider-fill {
  background: linear-gradient(to right, #3b82f6, #6366f1);
  box-shadow:
    0 0 12px rgba(59, 130, 246, 0.6),
    inset 0 1px 0 rgba(255, 255, 255, 0.3);
  transition: width 0.15s ease, left 0.15s ease;
  will-change: left, width;
  overflow: hidden;
}

/* Shimmer animation */
.glass-slider-shimmer::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.3),
    transparent
  );
  animation: shimmer 2s infinite;
}
@keyframes shimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}

/* ---- Glass knobs ---- */
.glass-slider-knob {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 2px solid #3b82f6;
  box-shadow:
    0 4px 12px rgba(0, 0, 0, 0.15),
    0 0 0 1px rgba(59, 130, 246, 0.3);
  transform: rotateY(1deg) scale(1);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  will-change: transform;
}
.dark .glass-slider-knob {
  background: rgba(255, 255, 255, 0.15);
  border-color: #6366f1;
  box-shadow:
    0 4px 12px rgba(0, 0, 0, 0.4),
    0 0 0 1px rgba(99, 102, 241, 0.4);
}

.glass-slider-knob:hover {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
  box-shadow:
    0 6px 16px rgba(0, 0, 0, 0.2),
    0 0 12px rgba(59, 130, 246, 0.5);
}
.glass-slider-knob:active {
  transform: scale(0.95) translateY(1px);
  box-shadow:
    0 2px 6px rgba(0, 0, 0, 0.2),
    0 0 4px rgba(59, 130, 246, 0.5);
}

/* ---- Ephemeral tooltip ---- */
.glass-slider-tooltip {
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 0.5rem;
  padding: 0.25rem 0.5rem;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
  transform: translateY(-4px);
}
.dark .glass-slider-tooltip {
  background: rgba(0, 0, 0, 0.75);
  border-color: rgba(255, 255, 255, 0.1);
}

.glass-slider-tooltip-body {
  color: #fff;
  font-size: 0.75rem;
  font-weight: 600;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

/* Glass spire (triangle pointing down) */
.glass-slider-spire {
  position: absolute;
  bottom: -5px;
  left: 50%;
  margin-left: -5px;
  width: 10px;
  height: 10px;
  background: inherit;
  backdrop-filter: inherit;
  -webkit-backdrop-filter: inherit;
  border: inherit;
  border-top: none;
  border-left: none;
  transform: rotate(45deg);
  box-shadow: inherit;
}

/* ---- Boundary labels ---- */
.text-gray-500 {
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.dark .text-gray-500 {
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

/* ---- Reduced motion ---- */
@media (prefers-reduced-motion: reduce) {
  .glass-slider-knob,
  .glass-slider-knob:hover,
  .glass-slider-knob:active,
  .glass-slider-fill {
    transition: none !important;
    transform: none !important;
    animation: none !important;
  }
  .glass-slider-shimmer::before {
    animation: none;
  }
}

/* ---- Touch devices (no hover transforms) ---- */
@media (hover: none) and (pointer: coarse) {
  .glass-slider-knob:hover {
    transform: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(59, 130, 246, 0.3);
  }
  .dark .glass-slider-knob:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(99, 102, 241, 0.4);
  }
}
</style>