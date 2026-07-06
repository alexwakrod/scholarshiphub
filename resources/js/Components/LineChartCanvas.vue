<template>
  <div ref="containerRef" class="relative w-full" :style="{ height: height + 'px' }">
    <canvas ref="canvasRef" :width="canvasWidth" :height="canvasHeight" class="w-full h-full block"></canvas>
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
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';

const props = defineProps({
  labels: { type: Array, required: true },
  datasets: { type: Array, required: true },
  height: { type: Number, default: 300 },
  lineColor: { type: String, default: '#3B82F6' },
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
const animationDuration = 800;
let resizeObserver = null;
const padding = { top: 30, right: 20, bottom: 60, left: 60 };

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
  const minVal = Math.min(...data, 0);
  const range = maxVal - minVal || 1;
  const chartW = w - padding.left - padding.right;
  const chartH = h - padding.top - padding.bottom;
  const pointCount = labels.length;
  const stepX = pointCount > 1 ? chartW / (pointCount - 1) : 0;

  const gridColor = isDark.value ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)';
  const axisColor = isDark.value ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.5)';

  // Grid
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
    ctx.fillText(Math.round(maxVal - (range / gridLines) * i), padding.left - 8, y + 3);
  }
  ctx.setLineDash([]);

  // Data points
  const visibleCount = Math.ceil(pointCount * progress);
  if (visibleCount < 2) return;

  const points = [];
  for (let i = 0; i < visibleCount; i++) {
    const x = padding.left + i * stepX;
    const normalizedValue = (data[i] - minVal) / range;
    const y = padding.top + chartH - normalizedValue * chartH;
    points.push({ x, y });
  }

  // Line
  ctx.beginPath();
  ctx.moveTo(points[0].x, points[0].y);
  for (let i = 1; i < points.length; i++) ctx.lineTo(points[i].x, points[i].y);
  ctx.strokeStyle = props.lineColor;
  ctx.lineWidth = 2;
  ctx.stroke();

  // Dots
  points.forEach(p => {
    ctx.beginPath();
    ctx.arc(p.x, p.y, 3, 0, Math.PI * 2);
    ctx.fillStyle = props.lineColor;
    ctx.fill();
  });

  // Labels
  labels.forEach((label, i) => {
    if (i % Math.ceil(labels.length / 10) === 0 || i === labels.length - 1) {
      const x = padding.left + i * stepX;
      ctx.fillStyle = axisColor;
      ctx.font = '10px Inter, sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(label, x, padding.top + chartH + 16);
    }
  });
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
</script>