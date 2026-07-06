<template>
  <div ref="wrapperRef" class="relative overflow-x-auto custom-scrollbar glass-timeline-wrapper" @scroll="onScroll">
    <canvas
      ref="canvasRef"
      :width="canvasWidth"
      :height="canvasHeight"
      class="block canvas-cursor"
      style="touch-action: pan-y pinch-zoom"
      @mousemove="onMouseMove"
      @mouseleave="onMouseLeave"
      @mousedown="onMouseDown"
      @click="onClick"
      @touchstart="onTouchStart"
      @touchmove="onTouchMove"
      @touchend="onTouchEnd"
    ></canvas>
    <!-- Hero-depth Glass Tooltip with Spire -->
    <Teleport to="body">
      <transition name="tooltip-fade">
        <div
          v-if="tooltip.visible"
          class="fixed z-50 glass-tooltip-pane px-3 py-2 rounded-xl text-xs pointer-events-none shadow-2xl"
          :class="isDark ? 'bg-gray-900/90 border-white/10' : 'bg-white/90 border-gray-200/50'"
          :style="{ top: tooltip.y + 'px', left: tooltip.x + 'px', transform: 'rotateY(-2deg) rotateX(1deg)' }"
        >
          <div class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-3 h-3 rotate-45"
               :class="isDark ? 'bg-gray-900/90 border-b border-r border-white/10' : 'bg-white/90 border-b border-r border-gray-200/50'">
          </div>
          <p class="font-semibold text-gray-900 dark:text-white">{{ tooltip.label }}</p>
          <p class="text-gray-500 dark:text-white/70">{{ tooltip.date }}</p>
          <p v-if="tooltip.score !== null && tooltip.score !== undefined" class="text-indigo-400 dark:text-indigo-300 font-medium">+{{ tooltip.score }}</p>
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';

const props = defineProps({
  milestones: { type: Array, required: true },
});
const emit = defineEmits(['select']);

const canvasRef = ref(null);
const wrapperRef = ref(null);
const canvasWidth = ref(1200);
const canvasHeight = ref(180);
const tooltip = ref({ visible: false, x: 0, y: 0, label: '', date: '', score: null });
const hoveredIndex = ref(-1);
const isDark = ref(true);

// Swipe / navigation
const currentIndex = ref(0);

// Touch state
const touchStartX = ref(0);
const touchStartY = ref(0);
const touchStartTime = ref(0);
const isSwiping = ref(false);
const touchMoved = ref(false);

// Mouse drag state
const isDragging = ref(false);
const mouseStartX = ref(0);
const hasDragged = ref(false);

// Layout constants
const itemSpacing = 200;
const paddingX = 70;
const nodeRadius = 15;
const curveHeight = 55;
const maxLabelWidth = 170;

const totalWidth = computed(() => paddingX * 2 + (props.milestones.length - 1) * itemSpacing);
const nodePositions = computed(() =>
  props.milestones.map((_, i) => ({
    x: paddingX + i * itemSpacing,
    y: canvasHeight.value / 2 + (i % 2 === 0 ? -curveHeight : curveHeight),
  }))
);

function detectTheme() {
  isDark.value = document.documentElement.classList.contains('dark');
}

// ==================== DRAWING ====================
const draw = () => {
  detectTheme();
  const canvas = canvasRef.value;
  if (!canvas) return;
  canvasWidth.value = Math.max(totalWidth.value, 800);
  const ctx = canvas.getContext('2d');
  const w = canvas.width, h = canvas.height;
  ctx.clearRect(0, 0, w, h);

  const positions = nodePositions.value;
  const curveColor = isDark.value ? 'rgba(99,102,241,0.7)' : 'rgba(79,70,229,0.6)';
  const nodeFill = '#3b82f6';
  const nodeHoverFill = '#818cf8';
  const nodeStroke = '#ffffff';
  const labelColor = isDark.value ? '#e5e7eb' : '#1f2937';
  const dateColor = isDark.value ? 'rgba(255,255,255,0.5)' : 'rgba(0,0,0,0.5)';
  const scoreColor = isDark.value ? 'rgba(168,85,247,0.9)' : '#7c3aed';

  if (positions.length > 1) {
    ctx.beginPath();
    ctx.moveTo(positions[0].x, positions[0].y);
    for (let i = 1; i < positions.length; i++) {
      const prev = positions[i - 1];
      const curr = positions[i];
      const cp1x = prev.x + itemSpacing / 3;
      const cp2x = curr.x - itemSpacing / 3;
      ctx.bezierCurveTo(cp1x, prev.y, cp2x, curr.y, curr.x, curr.y);
    }
    ctx.strokeStyle = curveColor;
    ctx.lineWidth = 3;
    ctx.shadowColor = isDark.value ? 'rgba(99,102,241,0.6)' : 'rgba(79,70,229,0.4)';
    ctx.shadowBlur = 14;
    ctx.stroke();
    ctx.shadowColor = 'transparent';
    ctx.shadowBlur = 0;
  }

  positions.forEach((pos, i) => {
    const isHovered = i === hoveredIndex.value;
    const liftY = isHovered ? -4 : 0;
    const r = isHovered ? nodeRadius + 3 : nodeRadius;
    const shadowBlur = isHovered ? 16 : 6;

    ctx.beginPath();
    ctx.arc(pos.x, pos.y + liftY, r, 0, Math.PI * 2);
    ctx.fillStyle = 'transparent';
    ctx.shadowColor = isDark.value ? 'rgba(59,130,246,0.5)' : 'rgba(0,0,0,0.2)';
    ctx.shadowBlur = shadowBlur;
    ctx.fill();
    ctx.shadowColor = 'transparent';
    ctx.shadowBlur = 0;

    ctx.beginPath();
    ctx.arc(pos.x, pos.y + liftY, r, 0, Math.PI * 2);
    ctx.fillStyle = isHovered ? nodeHoverFill : nodeFill;
    ctx.fill();
    ctx.strokeStyle = nodeStroke;
    ctx.lineWidth = 2;
    ctx.stroke();
  });

  ctx.font = '600 12px Inter, system-ui, sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'top';
  positions.forEach((pos, i) => {
    const m = props.milestones[i];
    let label = m.label || `Step ${i + 1}`;
    const score = m.score;
    const date = m.date || '';

    if (ctx.measureText(label).width > maxLabelWidth) {
      while (ctx.measureText(label + '…').width > maxLabelWidth && label.length > 0) {
        label = label.slice(0, -1);
      }
      label += '…';
    }

    ctx.fillStyle = labelColor;
    ctx.shadowColor = isDark.value ? 'rgba(0,0,0,0.6)' : 'rgba(255,255,255,0.8)';
    ctx.shadowBlur = 2;
    ctx.fillText(label, pos.x, pos.y + nodeRadius + 12);

    ctx.font = '10px Inter, system-ui, sans-serif';
    ctx.fillStyle = dateColor;
    ctx.shadowBlur = 0;
    ctx.fillText(date, pos.x, pos.y + nodeRadius + 28);

    if (score !== undefined && score !== null) {
      ctx.font = 'bold 10px Inter, system-ui, sans-serif';
      ctx.fillStyle = scoreColor;
      ctx.fillText(`+${score}`, pos.x, pos.y + nodeRadius + 40);
    }
  });
};

onMounted(() => nextTick(draw));
watch(() => props.milestones, () => {
  currentIndex.value = 0;
  nextTick(draw);
}, { deep: true });

// ==================== MOUSE EVENTS (hover + drag) ====================
const HIT_RADIUS = nodeRadius + 10;

function getEventPos(event) {
  const canvas = canvasRef.value;
  const rect = canvas.getBoundingClientRect();
  const scaleX = canvas.width / rect.width;
  const scaleY = canvas.height / rect.height;
  return {
    x: (event.clientX - rect.left) * scaleX,
    y: (event.clientY - rect.top) * scaleY,
    clientX: event.clientX,
    clientY: event.clientY,
  };
}

function onMouseMove(event) {
  const { x, y, clientX, clientY } = getEventPos(event);
  const positions = nodePositions.value;

  // If dragging, handle swipe navigation
  if (isDragging.value) {
    const deltaX = clientX - mouseStartX.value;
    const threshold = 30;
    if (Math.abs(deltaX) > threshold) {
      if (deltaX < 0 && currentIndex.value < props.milestones.length - 1) {
        currentIndex.value++;
      } else if (deltaX > 0 && currentIndex.value > 0) {
        currentIndex.value--;
      }
      if (currentIndex.value >= 0 && currentIndex.value < props.milestones.length) {
        emit('select', props.milestones[currentIndex.value].id);
        centerOnMilestone(currentIndex.value);
      }
      // Reset drag state so user can swipe again
      mouseStartX.value = clientX;
      hasDragged.value = true;
    }
    // Hide tooltip while dragging
    tooltip.value.visible = false;
    hoveredIndex.value = -1;
    draw();
    return;
  }

  // Normal hover behavior
  let found = -1;
  for (let i = 0; i < positions.length; i++) {
    const dist = Math.hypot(x - positions[i].x, y - positions[i].y);
    if (dist <= HIT_RADIUS) { found = i; break; }
  }
  hoveredIndex.value = found;
  if (found >= 0) {
    const m = props.milestones[found];
    tooltip.value = { visible: true, x: clientX + 16, y: clientY - 60, label: m.label, date: m.date, score: m.score };
  } else {
    tooltip.value.visible = false;
  }
  draw();
}

function onMouseDown(event) {
  isDragging.value = true;
  hasDragged.value = false;
  mouseStartX.value = event.clientX;
  // Prevent text selection
  event.preventDefault();
  // Listen to window events to capture outside canvas
  window.addEventListener('mousemove', onMouseMove);
  window.addEventListener('mouseup', onMouseUp);
}

function onMouseUp() {
  isDragging.value = false;
  window.removeEventListener('mousemove', onMouseMove);
  window.removeEventListener('mouseup', onMouseUp);
  // If no significant drag, allow click to happen
  if (!hasDragged.value) {
    // The click event will fire after mouseup, which will handle selection
  }
  hasDragged.value = false;
}

function onMouseLeave() {
  hoveredIndex.value = -1;
  tooltip.value.visible = false;
  draw();
}

// Override onClick to not conflict with drag; the native click will fire only if no drag
function onClick(event) {
  // If we dragged, ignore click
  if (hasDragged.value) return;
  const { x, y } = getEventPos(event);
  const positions = nodePositions.value;
  for (let i = 0; i < positions.length; i++) {
    if (Math.hypot(x - positions[i].x, y - positions[i].y) <= HIT_RADIUS) {
      currentIndex.value = i;
      emit('select', props.milestones[i].id);
      break;
    }
  }
}

// ==================== TOUCH EVENTS ====================
function onTouchStart(event) {
  if (event.touches.length !== 1) return;
  const touch = event.touches[0];
  touchStartX.value = touch.clientX;
  touchStartY.value = touch.clientY;
  touchStartTime.value = Date.now();
  isSwiping.value = false;
  touchMoved.value = false;
}

function onTouchMove(event) {
  if (event.touches.length !== 1) return;
  const touch = event.touches[0];
  const deltaX = touch.clientX - touchStartX.value;
  const deltaY = touch.clientY - touchStartY.value;

  if (!isSwiping.value && Math.abs(deltaX) > 10 && Math.abs(deltaX) > Math.abs(deltaY)) {
    isSwiping.value = true;
    event.preventDefault();
  }

  if (isSwiping.value) {
    event.preventDefault();
  }
  touchMoved.value = true;
}

function onTouchEnd(event) {
  if (!touchMoved.value || !isSwiping.value) return;

  const deltaX = (event.changedTouches[0]?.clientX || 0) - touchStartX.value;
  const elapsed = Date.now() - touchStartTime.value;
  const SWIPE_THRESHOLD = 30;
  const VELOCITY_THRESHOLD = 0.5;

  if (Math.abs(deltaX) > SWIPE_THRESHOLD || (Math.abs(deltaX) / elapsed) > VELOCITY_THRESHOLD) {
    if (deltaX < 0 && currentIndex.value < props.milestones.length - 1) {
      currentIndex.value++;
    } else if (deltaX > 0 && currentIndex.value > 0) {
      currentIndex.value--;
    }
    if (currentIndex.value >= 0 && currentIndex.value < props.milestones.length) {
      emit('select', props.milestones[currentIndex.value].id);
      centerOnMilestone(currentIndex.value);
    }
  }
  isSwiping.value = false;
  touchMoved.value = false;
}

function centerOnMilestone(index) {
  const wrapper = wrapperRef.value;
  if (!wrapper || !props.milestones.length) return;
  const nodeX = paddingX + index * itemSpacing;
  const wrapperWidth = wrapper.clientWidth;
  const scrollTarget = nodeX - wrapperWidth / 2;
  wrapper.scrollTo({ left: scrollTarget, behavior: 'smooth' });
}

function onScroll() {}

// Clean up global listeners
onBeforeUnmount(() => {
  window.removeEventListener('mousemove', onMouseMove);
  window.removeEventListener('mouseup', onMouseUp);
});
</script>

<style scoped>
.glass-timeline-wrapper {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.6);
  border-radius: 1rem;
  padding: 1rem;
}
.dark .glass-timeline-wrapper {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.06);
}
.canvas-cursor { cursor: default; }
.glass-tooltip-pane {
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid;
  will-change: transform, opacity;
  transition: transform 0.2s ease, opacity 0.2s ease;
  max-width: 220px;
}
.tooltip-fade-enter-active, .tooltip-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.tooltip-fade-enter-from, .tooltip-fade-leave-to {
  opacity: 0;
  transform: rotateY(-4deg) rotateX(2deg) translateY(4px);
}
.custom-scrollbar::-webkit-scrollbar { height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 999px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); }
@media (max-width: 768px) { .glass-tooltip-pane { transform: none !important; } }
@media (prefers-reduced-motion: reduce) {
  .glass-tooltip-pane, .glass-tooltip-pane:hover { transition: none !important; transform: none !important; }
  .tooltip-fade-enter-active, .tooltip-fade-leave-active { transition: none !important; }
}
</style>