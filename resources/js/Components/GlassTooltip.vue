<template>
  <Teleport to="body">
    <div
      v-if="computedVisible"
      ref="tooltipRef"
      class="fixed z-3000 glass-tooltip text-sm max-w-xs p-3 rounded-xl pointer-events-none transition-opacity duration-200"
      :class="[
        computedVisible ? 'opacity-100' : 'opacity-0',
        spireDirection === 'up' ? 'spire-up' : 'spire-down'
      ]"
      :style="tooltipStyle"
      role="tooltip"
    >
      <!-- Glass Spire (physical tether) -->
      <div class="glass-spire" aria-hidden="true"></div>

      <!-- Content slot -->
      <div class="tooltip-content">
        <slot />
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  visible: { type: Boolean, default: undefined },
  targetRef: { type: Object, default: null },
  delay: { type: Number, default: 300 },
});

const tooltipRef = ref(null);
const tooltipStyle = ref({ top: '0px', left: '0px' });
const spireDirection = ref('up'); // 'up' = tooltip above target (spire at bottom), 'down' = below target (spire at top)

const internalVisible = ref(false);
let showTimeout = null;
let hideTimeout = null;

const computedVisible = computed(() => {
  return props.visible !== undefined ? props.visible : internalVisible.value;
});

function positionTooltip() {
  if (!tooltipRef.value || !props.targetRef) return;
  const targetRect = props.targetRef.getBoundingClientRect();
  const tooltipRect = tooltipRef.value.getBoundingClientRect();
  const vw = window.innerWidth;
  const vh = window.innerHeight;
  const arrowSize = 8; // matches spire dimensions

  let top = targetRect.bottom + arrowSize + 4; // default: below target, spire at top of tooltip
  let left = targetRect.left + targetRect.width / 2 - tooltipRect.width / 2;
  let direction = 'down'; // tooltip below target → spire points up

  // Check vertical space
  if (top + tooltipRect.height > vh - 8) {
    // Not enough space below, flip above
    top = targetRect.top - tooltipRect.height - arrowSize - 4;
    direction = 'up'; // tooltip above target → spire points down
  }

  // Horizontal bounds
  if (left < 8) left = 8;
  if (left + tooltipRect.width > vw - 8) left = vw - tooltipRect.width - 8;

  tooltipStyle.value = { top: `${top}px`, left: `${left}px` };
  spireDirection.value = direction;
}

function scheduleShow() {
  clearTimeout(showTimeout);
  showTimeout = setTimeout(() => {
    internalVisible.value = true;
    nextTick(positionTooltip);
  }, props.delay);
}

function scheduleHide() {
  clearTimeout(showTimeout);
  clearTimeout(hideTimeout);
  hideTimeout = setTimeout(() => {
    internalVisible.value = false;
  }, 0);
}

let touchTimer = null;
function onTouchStart() {
  clearTimeout(touchTimer);
  touchTimer = setTimeout(() => {
    internalVisible.value = true;
    nextTick(positionTooltip);
  }, 500);
}
function onTouchEnd() {
  clearTimeout(touchTimer);
  if (internalVisible.value) {
    // keep until tap elsewhere
  } else {
    internalVisible.value = false;
  }
}

function handleDocumentClick(event) {
  if (props.targetRef && !props.targetRef.contains(event.target)) {
    internalVisible.value = false;
  }
}

onMounted(() => {
  if (props.targetRef) {
    const el = props.targetRef;
    el.addEventListener('mouseenter', scheduleShow);
    el.addEventListener('mouseleave', scheduleHide);
    el.addEventListener('touchstart', onTouchStart, { passive: true });
    el.addEventListener('touchend', onTouchEnd, { passive: true });
    document.addEventListener('click', handleDocumentClick, true);
  }
});

onBeforeUnmount(() => {
  if (props.targetRef) {
    const el = props.targetRef;
    el.removeEventListener('mouseenter', scheduleShow);
    el.removeEventListener('mouseleave', scheduleHide);
    el.removeEventListener('touchstart', onTouchStart);
    el.removeEventListener('touchend', onTouchEnd);
  }
  document.removeEventListener('click', handleDocumentClick, true);
  clearTimeout(showTimeout);
  clearTimeout(hideTimeout);
  clearTimeout(touchTimer);
});

watch(() => props.visible, (val) => {
  if (val) nextTick(positionTooltip);
});
</script>

<style scoped>
/* ============================================================
   GLASS TOOLTIP – HERO DEPTH WITH SPIRE & THEME AWARENESS
   ============================================================ */

/* --- Glass Tooltip Base --- */
.glass-tooltip {
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  box-shadow:
    0 16px 48px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  color: #fff;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  letter-spacing: 0.01em;
  isolation: isolate;
  contain: paint;
  transition: opacity 0.2s ease;
}

/* --- Inner Rim Highlight --- */
.glass-tooltip::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.12) 0%,
    transparent 40%
  );
  pointer-events: none;
  z-index: 1;
}

/* --- Glass Spire (Arrow) --- */
.glass-spire {
  position: absolute;
  width: 16px;
  height: 16px;
  background: inherit;
  backdrop-filter: inherit;
  -webkit-backdrop-filter: inherit;
  border: inherit;
  border-radius: 0 0 0 4px; /* only one corner for rotation */
  transform: rotate(45deg);
  z-index: 0;
  box-shadow: inherit;
}

/* Spire positioned at top (tooltip below target) */
.glass-tooltip.spire-down .glass-spire {
  top: -8px;
  left: 50%;
  margin-left: -8px;
  border-right: none;
  border-bottom: none;
  border-top-left-radius: 4px;
  border-bottom-right-radius: 0;
}

/* Spire positioned at bottom (tooltip above target) */
.glass-tooltip.spire-up .glass-spire {
  bottom: -8px;
  left: 50%;
  margin-left: -8px;
  border-left: none;
  border-top: none;
  border-bottom-right-radius: 4px;
  border-top-left-radius: 0;
}

/* --- Content Area --- */
.tooltip-content {
  position: relative;
  z-index: 2;
  padding: 0.25rem;
  font-weight: 500;
  line-height: 1.5;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

/* Typography hierarchy */
.tooltip-content :deep(strong) {
  color: #fff;
  font-weight: 600;
}
.tooltip-content :deep(span) {
  color: rgba(255, 255, 255, 0.7);
  font-weight: 400;
}

/* --- Theme: Light Mode --- */
@media (prefers-color-scheme: light) {
  .glass-tooltip {
    background: rgba(255, 255, 255, 0.9);
    border-color: rgba(0, 0, 0, 0.08);
    box-shadow:
      0 16px 48px rgba(0, 0, 0, 0.12),
      inset 0 1px 0 rgba(255, 255, 255, 0.4);
    color: #1f2937;
  }
  .glass-tooltip::before {
    background: linear-gradient(
      135deg,
      rgba(255, 255, 255, 0.6) 0%,
      transparent 40%
    );
  }
  .tooltip-content {
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
  }
  .tooltip-content :deep(strong) {
    color: #111827;
  }
  .tooltip-content :deep(span) {
    color: rgba(0, 0, 0, 0.6);
  }
}

/* Dark mode is default */

/* --- Reduced Motion --- */
@media (prefers-reduced-motion: reduce) {
  .glass-tooltip {
    transition: none !important;
  }
}

/* --- Viewport edge safety (already handled by script) --- */
</style>