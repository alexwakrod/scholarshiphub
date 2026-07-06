<template>
  <div class="radar-chart-container perspective-1000">
    <div
      ref="containerRef"
      class="radar-chart-wrapper glass-elevated"
      :style="{
        maxWidth: size + 'px',
        width: '100%',
      }"
    >
      <!-- Skeleton loader when no categories (data empty) -->
      <div v-if="!categories.length" class="skeleton-overlay">
        <svg class="skeleton-radar" viewBox="0 0 200 200">
          <defs>
            <linearGradient id="shimmer" x1="0" x2="1" y1="0" y2="1">
              <stop offset="0%" stop-color="rgba(255,255,255,0.05)" />
              <stop offset="50%" stop-color="rgba(255,255,255,0.15)" />
              <stop offset="100%" stop-color="rgba(255,255,255,0.05)" />
            </linearGradient>
          </defs>
          <circle cx="100" cy="100" r="90" fill="none" stroke="url(#shimmer)" stroke-width="1" />
          <circle cx="100" cy="100" r="72" fill="none" stroke="url(#shimmer)" stroke-width="1" />
          <circle cx="100" cy="100" r="54" fill="none" stroke="url(#shimmer)" stroke-width="1" />
          <circle cx="100" cy="100" r="36" fill="none" stroke="url(#shimmer)" stroke-width="1" />
          <circle cx="100" cy="100" r="18" fill="none" stroke="url(#shimmer)" stroke-width="1" />
          <line x1="100" y1="10" x2="100" y2="190" stroke="url(#shimmer)" stroke-width="1" />
          <line x1="10" y1="100" x2="190" y2="100" stroke="url(#shimmer)" stroke-width="1" />
          <line x1="22" y1="22" x2="178" y2="178" stroke="url(#shimmer)" stroke-width="1" />
          <line x1="178" y1="22" x2="22" y2="178" stroke="url(#shimmer)" stroke-width="1" />
        </svg>
      </div>
      <canvas ref="canvasRef" class="radar-canvas" :class="{ 'opacity-0': !categories.length }"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';

const props = defineProps({
  categories: { type: Array, required: true },
  data: { type: Array, required: true },
  size: { type: Number, default: 300 },
  color: { type: String, default: 'rgba(59, 130, 246, 0.4)' },
  borderColor: { type: String, default: 'rgba(59, 130, 246, 0.8)' },
  labelColor: { type: String, default: '' },
});

const containerRef = ref(null);
const canvasRef = ref(null);
let animationFrame = null;
let resizeObserver = null;
let themeObserver = null;
let resizeScheduled = false;

const isDark = ref(document.documentElement.classList.contains('dark'));

function updateTheme() {
  isDark.value = document.documentElement.classList.contains('dark');
}

function startThemeObserver() {
  themeObserver = new MutationObserver(updateTheme);
  themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
}

function stopThemeObserver() {
  if (themeObserver) {
    themeObserver.disconnect();
    themeObserver = null;
  }
}

function drawChart(animate = true) {
  const canvas = canvasRef.value;
  const container = containerRef.value;
  if (!canvas || !container) return;

  const ctx = canvas.getContext('2d');
  const dpr = window.devicePixelRatio || 1;
  const containerWidth = container.clientWidth;
  const containerHeight = containerWidth;

  if (containerWidth === 0 || containerHeight === 0) return;

  const w = containerWidth * dpr;
  const h = containerHeight * dpr;

  if (canvas.width === w && canvas.height === h) return;

  canvas.width = w;
  canvas.height = h;
  canvas.style.width = containerWidth + 'px';
  canvas.style.height = containerHeight + 'px';

  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const count = props.categories.length;
  if (count < 3) return;

  const centerX = canvas.width / 2;
  const centerY = canvas.height / 2;
  const radius = Math.min(canvas.width, canvas.height) / 2 - 40 * dpr;
  const angleStep = (Math.PI * 2) / count;

  const dark = isDark.value;
  const labelFill = props.labelColor || (dark ? 'rgba(255,255,255,0.75)' : 'rgba(0,0,0,0.7)');
  const gridStroke = dark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';
  const axisStroke = dark ? 'rgba(255,255,255,0.15)' : 'rgba(0,0,0,0.15)';

  function getPoint(index, r) {
    const angle = -Math.PI / 2 + index * angleStep;
    return { x: centerX + r * Math.cos(angle), y: centerY + r * Math.sin(angle) };
  }

  const rings = 5;
  for (let ring = 1; ring <= rings; ring++) {
    const r = (radius / rings) * ring;
    ctx.beginPath();
    for (let i = 0; i < count; i++) {
      const p = getPoint(i, r);
      if (i === 0) ctx.moveTo(p.x, p.y);
      else ctx.lineTo(p.x, p.y);
    }
    ctx.closePath();
    ctx.strokeStyle = gridStroke;
    ctx.lineWidth = 1 * dpr;
    ctx.stroke();
  }

  for (let i = 0; i < count; i++) {
    const p = getPoint(i, radius);
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.lineTo(p.x, p.y);
    ctx.strokeStyle = axisStroke;
    ctx.lineWidth = 1 * dpr;
    ctx.stroke();

    const labelP = getPoint(i, radius + 18 * dpr);
    ctx.fillStyle = labelFill;
    ctx.font = `600 ${11 * dpr}px system-ui, -apple-system, sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    // subtle text shadow for legibility
    ctx.shadowColor = dark ? 'rgba(0,0,0,0.5)' : 'rgba(255,255,255,0.3)';
    ctx.shadowBlur = 2 * dpr;
    ctx.fillText(props.categories[i], labelP.x, labelP.y);
    ctx.shadowColor = 'transparent';
    ctx.shadowBlur = 0;
  }

  const maxVal = 100;
  const targetValues = props.data.map(v => Math.min(Math.max(v, 0), maxVal));
  const drawData = (progress) => {
    const points = [];
    for (let i = 0; i < count; i++) {
      const r = (targetValues[i] / maxVal) * radius * progress;
      points.push(getPoint(i, r));
    }
    ctx.beginPath();
    for (let i = 0; i < points.length; i++) {
      if (i === 0) ctx.moveTo(points[i].x, points[i].y);
      else ctx.lineTo(points[i].x, points[i].y);
    }
    ctx.closePath();
    ctx.fillStyle = props.color;
    ctx.shadowColor = props.borderColor;
    ctx.shadowBlur = 8 * dpr;
    ctx.fill();
    ctx.shadowColor = 'transparent';
    ctx.strokeStyle = props.borderColor;
    ctx.lineWidth = 2.5 * dpr;
    ctx.stroke();

    points.forEach(p => {
      ctx.beginPath();
      ctx.arc(p.x, p.y, 4.5 * dpr, 0, Math.PI * 2);
      ctx.fillStyle = props.borderColor;
      ctx.shadowColor = props.borderColor;
      ctx.shadowBlur = 6 * dpr;
      ctx.fill();
      ctx.shadowColor = 'transparent';
    });
  };

  if (animate) {
    let progress = 0;
    const duration = 800;
    const startTime = performance.now();
    function step(time) {
      const elapsed = time - startTime;
      progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      for (let ring = 1; ring <= rings; ring++) {
        const r = (radius / rings) * ring;
        ctx.beginPath();
        for (let i = 0; i < count; i++) {
          const p = getPoint(i, r);
          if (i === 0) ctx.moveTo(p.x, p.y);
          else ctx.lineTo(p.x, p.y);
        }
        ctx.closePath();
        ctx.strokeStyle = gridStroke;
        ctx.lineWidth = 1 * dpr;
        ctx.stroke();
      }
      for (let i = 0; i < count; i++) {
        const p = getPoint(i, radius);
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.lineTo(p.x, p.y);
        ctx.strokeStyle = axisStroke;
        ctx.lineWidth = 1 * dpr;
        ctx.stroke();
        const labelP = getPoint(i, radius + 18 * dpr);
        ctx.fillStyle = labelFill;
        ctx.font = `600 ${11 * dpr}px system-ui, -apple-system, sans-serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.shadowColor = dark ? 'rgba(0,0,0,0.5)' : 'rgba(255,255,255,0.3)';
        ctx.shadowBlur = 2 * dpr;
        ctx.fillText(props.categories[i], labelP.x, labelP.y);
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
      }
      drawData(eased);
      if (progress < 1) {
        animationFrame = requestAnimationFrame(step);
      }
    }
    if (animationFrame) cancelAnimationFrame(animationFrame);
    animationFrame = requestAnimationFrame(step);
  } else {
    drawData(1);
  }
}

function scheduleResize() {
  if (resizeScheduled) return;
  resizeScheduled = true;
  requestAnimationFrame(() => {
    resizeScheduled = false;
    if (containerRef.value) {
      drawChart(false);
    }
  });
}

function startResizeObserver() {
  if (!containerRef.value) return;
  resizeObserver = new ResizeObserver(() => {
    scheduleResize();
  });
  resizeObserver.observe(containerRef.value);
}

function stopResizeObserver() {
  if (resizeObserver) {
    resizeObserver.disconnect();
    resizeObserver = null;
  }
}

watch(
  () => [props.categories, props.data, props.size, props.color, props.borderColor, props.labelColor],
  () => {
    if (animationFrame) cancelAnimationFrame(animationFrame);
    nextTick(() => drawChart(true));
  },
  { deep: true }
);

watch(isDark, () => {
  if (animationFrame) cancelAnimationFrame(animationFrame);
  nextTick(() => drawChart(false));
});

onMounted(() => {
  nextTick(() => {
    drawChart(true);
    startResizeObserver();
  });
  startThemeObserver();
});

onBeforeUnmount(() => {
  if (animationFrame) cancelAnimationFrame(animationFrame);
  stopResizeObserver();
  stopThemeObserver();
});
</script>

<style scoped>
/* ============================================================
   GLASS RADAR CHART – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* Outer perspective container */
.perspective-1000 {
  perspective: 1000px;
}

/* Elevated glass slab wrapper */
.radar-chart-wrapper {
  position: relative;
  aspect-ratio: 1 / 1;
  overflow: hidden;
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.5);
  border-radius: 1.25rem;
  transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1),
              box-shadow 0.4s ease,
              background 0.3s ease;
  will-change: transform;
  transform: rotateY(-2deg) rotateX(0.5deg);
  isolation: isolate;
}

.dark .radar-chart-wrapper {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

/* Inner rim highlight */
.radar-chart-wrapper::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.15) 0%,
    transparent 40%
  );
  pointer-events: none;
  z-index: 2;
}

.dark .radar-chart-wrapper::before {
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.05) 0%,
    transparent 40%
  );
}

/* Hover: flatten, lift, expand */
.radar-chart-wrapper:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-4px) scale(1.02);
  box-shadow:
    0 16px 48px rgba(0, 0, 0, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.4);
  z-index: 10;
}

.dark .radar-chart-wrapper:hover {
  background: rgba(255, 255, 255, 0.08);
  box-shadow:
    0 16px 48px rgba(0, 0, 0, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

/* Active: press down */
.radar-chart-wrapper:active {
  transform: scale(0.97) translateY(2px);
  transition-duration: 0.1s;
}

/* Canvas fills the square */
.radar-canvas {
  display: block;
  width: 100%;
  height: 100%;
  position: relative;
  z-index: 1;
}

/* Skeleton overlay */
.skeleton-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3;
}

.skeleton-radar {
  width: 60%;
  height: 60%;
  animation: shimmer 2s infinite linear;
}

@keyframes shimmer {
  0% { opacity: 0.4; }
  50% { opacity: 0.8; }
  100% { opacity: 0.4; }
}

/* Reduced motion & mobile overrides */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .radar-chart-wrapper,
  .radar-chart-wrapper:hover,
  .radar-chart-wrapper:active {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .radar-chart-wrapper,
  .radar-chart-wrapper:hover,
  .radar-chart-wrapper:active {
    transition: none !important;
    transform: none !important;
  }
  .skeleton-radar {
    animation: none !important;
  }
}
</style>