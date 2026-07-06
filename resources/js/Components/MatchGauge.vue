<template>
  <div class="relative inline-flex items-center justify-center" :style="{ width: size + 'px', height: size + 'px' }">
    <canvas ref="canvasRef" :width="canvasSize" :height="canvasSize"></canvas>
    <div
      class="absolute inset-0 flex items-center justify-center font-bold"
      :class="displayScore > 0 ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-white/30'"
      :style="{ fontSize: size * 0.25 + 'px' }"
    >
      {{ displayScore }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';

const props = defineProps({
  score: { type: Number, default: 0 },
  size: { type: Number, default: 80 },
});

const canvasRef = ref(null);
const canvasSize = ref(props.size * (window.devicePixelRatio || 1));
const displayScore = ref(0);

let animationFrame = null;

const getColor = (value) => {
  if (value >= 70) return '#10B981';
  if (value >= 50) return '#F59E0B';
  return '#EF4444';
};

function isDarkMode() {
  try {
    return document.documentElement.classList.contains('dark');
  } catch { return true; }
}

const drawGauge = (progress) => {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const w = canvas.width;
  const h = canvas.height;
  ctx.clearRect(0, 0, w, h);

  const centerX = w / 2;
  const centerY = h / 2;
  const radius = Math.min(w, h) / 2 * 0.8;
  const lineWidth = radius * 0.25;
  const startAngle = -Math.PI * 0.75;
  const endAngle = Math.PI * 0.75;
  const currentAngle = startAngle + (endAngle - startAngle) * (progress * (props.score / 100));

  // Background arc
  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, startAngle, endAngle);
  ctx.strokeStyle = isDarkMode() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)';
  ctx.lineWidth = lineWidth;
  ctx.lineCap = 'round';
  ctx.stroke();

  // Colored arc
  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, startAngle, currentAngle);
  ctx.strokeStyle = getColor(props.score);
  ctx.lineWidth = lineWidth;
  ctx.stroke();

  // Tick marks
  const tickColor = isDarkMode() ? 'rgba(255,255,255,0.4)' : 'rgba(0,0,0,0.25)';
  for (let i = 0; i <= 10; i++) {
    const tickAngle = startAngle + (endAngle - startAngle) * (i / 10);
    const innerRadius = radius - lineWidth / 2 - 2;
    const outerRadius = radius + lineWidth / 2 + 2;
    const x1 = centerX + innerRadius * Math.cos(tickAngle);
    const y1 = centerY + innerRadius * Math.sin(tickAngle);
    const x2 = centerX + outerRadius * Math.cos(tickAngle);
    const y2 = centerY + outerRadius * Math.sin(tickAngle);
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.strokeStyle = tickColor;
    ctx.lineWidth = 1;
    ctx.stroke();
  }
};

const animateScore = () => {
  const target = props.score;
  const startTime = performance.now();
  const duration = 600;
  const step = (timestamp) => {
    const elapsed = timestamp - startTime;
    const progress = Math.min(1, elapsed / duration);
    displayScore.value = Math.round(target * progress);
    drawGauge(progress);
    if (progress < 1) {
      animationFrame = requestAnimationFrame(step);
    }
  };
  if (animationFrame) cancelAnimationFrame(animationFrame);
  animationFrame = requestAnimationFrame(step);
};

onMounted(() => {
  nextTick(() => {
    canvasSize.value = props.size * (window.devicePixelRatio || 1);
    if (canvasRef.value) {
      canvasRef.value.style.width = props.size + 'px';
      canvasRef.value.style.height = props.size + 'px';
    }
    animateScore();
  });
});

watch(() => props.score, () => {
  if (animationFrame) cancelAnimationFrame(animationFrame);
  animateScore();
});
</script>