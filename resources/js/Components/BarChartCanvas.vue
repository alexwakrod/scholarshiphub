<template>
  <div ref="containerRef" class="relative w-full" :style="{ height: height + 'px' }">
    <canvas
      ref="canvasRef"
      :width="canvasWidth"
      :height="canvasHeight"
      class="w-full h-full block"
      @mousemove="onMouseMove"
      @mouseleave="onMouseLeave"
    ></canvas>
    <div
      v-if="tooltip.visible"
      class="absolute z-10 pointer-events-none glass-tooltip px-2 py-1 rounded text-xs shadow"
      :class="isDark ? 'bg-black/85 text-white border border-white/20' : 'bg-white text-gray-900 border border-gray-200'"
      :style="{ top: tooltip.y + 'px', left: tooltip.x + 'px' }"
    >
      {{ tooltip.text }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick, computed } from 'vue';

const props = defineProps({
  labels: { type: Array, required: true },
  datasets: { type: Array, required: true },
  height: { type: Number, default: 300 },
  barColor: { type: String, default: '#3B82F6' },
  gridColor: { type: String, default: 'rgba(255,255,255,0.1)' },
  textColor: { type: String, default: 'rgba(255,255,255,0.7)' },
});

const canvasRef = ref(null);
const containerRef = ref(null);
const canvasWidth = ref(800);
const canvasHeight = ref(600);
const tooltip = ref({ visible: false, x: 0, y: 0, text: '' });
const isDark = ref(true);

let animationFrame = null;
const animationDuration = 600;
let resizeObserver = null;
const padding = { top: 30, right: 20, bottom: 60, left: 60 };

// Store computed bar positions for hit detection
const barPositions = ref([]);

function detectTheme() {
  isDark.value = document.documentElement.classList.contains('dark');
}

const drawChart = (progress = 1) => {
  detectTheme();
  const canvas = canvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const w = canvas.width, h = canvas.height;
  ctx.clearRect(0, 0, w, h);

  const labels = props.labels;
  const data = props.datasets[0]?.data || [];
  if (!labels.length || !data.length) return;

  const maxVal = Math.max(...data, 1);
  const chartW = w - padding.left - padding.right;
  const chartH = h - padding.top - padding.bottom;
  const barWidth = Math.max(8, (chartW / labels.length) * 0.6);
  const gap = (chartW - barWidth * labels.length) / (labels.length + 1);

  const gridColor = isDark.value ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)';
  const axisColor = isDark.value ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.5)';

  // Grid lines
  ctx.strokeStyle = gridColor;
  ctx.lineWidth = 1;
  ctx.setLineDash([4, 4]);
  const gridLines = 5;
  for (let i = 0; i <= gridLines; i++) {
    const y = padding.top + (chartH / gridLines) * i;
    ctx.beginPath();
    ctx.moveTo(padding.left, y);
    ctx.lineTo(padding.left + chartW, y);
    ctx.stroke();
    ctx.fillStyle = axisColor;
    ctx.font = '10px Inter, sans-serif';
    ctx.textAlign = 'right';
    ctx.fillText(Math.round((maxVal / gridLines) * (gridLines - i)), padding.left - 8, y + 3);
  }
  ctx.setLineDash([]);

  // Build bar positions array for tooltip
  const positions = [];
  // Bars
  labels.forEach((label, i) => {
    const value = data[i] || 0;
    const targetBarHeight = (value / maxVal) * chartH * progress;
    const x = padding.left + gap + i * (barWidth + gap);
    const y = padding.top + chartH - targetBarHeight;
    ctx.fillStyle = props.barColor;
    ctx.fillRect(x, y, barWidth, targetBarHeight);
    ctx.fillStyle = axisColor;
    ctx.font = '10px Inter, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(label, x + barWidth / 2, padding.top + chartH + 16);
    ctx.fillText(value, x + barWidth / 2, y - 6);

    positions.push({
      x,
      y,
      width: barWidth,
      height: targetBarHeight,
      label,
      value,
    });
  });
  barPositions.value = positions;
};

const animate = () => {
  const startTime = performance.now();
  const step = (timestamp) => {
    const elapsed = timestamp - startTime;
    const progress = Math.min(1, elapsed / animationDuration);
    drawChart(progress);
    if (progress < 1) animationFrame = requestAnimationFrame(step);
  };
  if (animationFrame) cancelAnimationFrame(animationFrame);
  animationFrame = requestAnimationFrame(step);
};

const handleResize = () => {
  if (!containerRef.value) return;
  const rect = containerRef.value.getBoundingClientRect();
  const dpr = window.devicePixelRatio || 1;
  canvasWidth.value = rect.width * dpr;
  canvasHeight.value = props.height * dpr;
  if (canvasRef.value) {
    canvasRef.value.style.width = rect.width + 'px';
    canvasRef.value.style.height = props.height + 'px';
  }
  drawChart(1);
};

onMounted(() => {
  nextTick(() => { handleResize(); animate(); });
  resizeObserver = new ResizeObserver(handleResize);
  if (containerRef.value) resizeObserver.observe(containerRef.value);
});

onBeforeUnmount(() => {
  if (animationFrame) cancelAnimationFrame(animationFrame);
  if (resizeObserver) resizeObserver.disconnect();
});

watch(() => [props.labels, props.datasets], () => { nextTick(() => { handleResize(); animate(); }); }, { deep: true });

// ---- Tooltip interaction ----
function getMousePos(event) {
  const canvas = canvasRef.value;
  if (!canvas) return null;
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
  const pos = getMousePos(event);
  if (!pos) return;
  const positions = barPositions.value;
  let found = false;
  for (const bar of positions) {
    if (
      pos.x >= bar.x &&
      pos.x <= bar.x + bar.width &&
      pos.y >= bar.y &&
      pos.y <= bar.y + bar.height
    ) {
      const containerRect = containerRef.value?.getBoundingClientRect();
      const offsetX = containerRect ? (event.clientX - containerRect.left) : pos.clientX;
      const offsetY = containerRect ? (event.clientY - containerRect.top) : pos.clientY;
      tooltip.value = {
        visible: true,
        x: offsetX + 12,
        y: offsetY - 30,
        text: `${bar.label}: ${bar.value}`,
      };
      found = true;
      break;
    }
  }
  if (!found) {
    tooltip.value.visible = false;
  }
}

function onMouseLeave() {
  tooltip.value.visible = false;
}
</script>