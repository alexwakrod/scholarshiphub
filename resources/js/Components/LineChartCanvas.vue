<template>
  <div class="line-chart-wrapper perspective-1000">
    <!-- Elevated glass slab -->
    <div
      ref="containerRef"
      class="line-chart-slab relative w-full rounded-2xl overflow-hidden"
      :style="{ height: height + 'px' }"
    >
      <!-- Skeleton shimmer while no data -->
      <div v-if="!hasData" class="skeleton-overlay absolute inset-0 z-10 flex items-center justify-center">
        <svg class="skeleton-chart w-3/4 h-3/4" viewBox="0 0 400 200" preserveAspectRatio="none">
          <defs>
            <linearGradient id="shimmer" x1="0" x2="1" y1="0" y2="1">
              <stop offset="0%" stop-color="rgba(255,255,255,0.03)" />
              <stop offset="50%" stop-color="rgba(255,255,255,0.12)" />
              <stop offset="100%" stop-color="rgba(255,255,255,0.03)" />
              <animate attributeName="x1" values="0;1;0" dur="2s" repeatCount="indefinite" />
            </linearGradient>
          </defs>
          <polyline points="20,180 100,140 180,120 260,100 340,80" fill="none" stroke="url(#shimmer)" stroke-width="3" />
          <circle cx="20" cy="180" r="4" fill="url(#shimmer)" />
          <circle cx="100" cy="140" r="4" fill="url(#shimmer)" />
          <circle cx="180" cy="120" r="4" fill="url(#shimmer)" />
          <circle cx="260" cy="100" r="4" fill="url(#shimmer)" />
          <circle cx="340" cy="80" r="4" fill="url(#shimmer)" />
        </svg>
      </div>

      <!-- SVG Chart -->
      <svg
        v-if="hasData"
        ref="svgRef"
        class="w-full h-full block"
        :viewBox="`0 0 ${svgWidth} ${svgHeight}`"
        preserveAspectRatio="none"
      >
        <!-- Horizontal grid lines -->
        <line
          v-for="(tick, i) in yTicks"
          :key="'g' + i"
          :x1="padding.left"
          :y1="tick.y"
          :x2="svgWidth - padding.right"
          :y2="tick.y"
          :stroke="isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)'"
          stroke-width="1"
          stroke-dasharray="4 4"
          stroke-linecap="round"
        />
        <!-- Y-axis labels -->
        <text
          v-for="(tick, i) in yTicks"
          :key="'yl' + i"
          :x="padding.left - 12"
          :y="tick.y + 4"
          :fill="isDark ? 'rgba(255,255,255,0.65)' : 'rgba(0,0,0,0.55)'"
          font-size="10"
          font-family="Inter, system-ui, -apple-system, sans-serif"
          text-anchor="end"
        >{{ tick.label }}</text>

        <!-- X-axis labels (show only few to avoid overlap) -->
        <text
          v-for="(label, i) in xLabels"
          :key="'xl' + i"
          :x="points[i]?.x"
          :y="svgHeight - padding.bottom + 20"
          :fill="isDark ? 'rgba(255,255,255,0.65)' : 'rgba(0,0,0,0.55)'"
          font-size="10"
          font-family="Inter, system-ui, -apple-system, sans-serif"
          text-anchor="middle"
          v-if="i % Math.ceil(labels.length / 8) === 0 || i === labels.length - 1"
        >{{ label }}</text>

        <!-- Line (polyline) -->
        <polyline
          :points="linePoints"
          fill="none"
          :stroke="lineColor"
          stroke-width="2.5"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="transition-all duration-300"
        />

        <!-- Data points (circles) -->
        <circle
          v-for="(pt, i) in points"
          :key="'pt' + i"
          :cx="pt.x"
          :cy="pt.y"
          r="4"
          :fill="lineColor"
          class="cursor-pointer transition-transform duration-200 hover:scale-150"
          @mouseenter="showTooltip($event, pt)"
          @mouseleave="hideTooltip"
        />
      </svg>
    </div>

    <!-- GlassTooltip for hover details -->
    <GlassTooltip
      :visible="tooltip.visible"
      :targetRef="tooltipTargetRef"
      :delay="0"
    >
      <p class="text-xs text-white">{{ tooltip.text }}</p>
    </GlassTooltip>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import GlassTooltip from '@/Components/GlassTooltip.vue';

const props = defineProps({
  labels: { type: Array, required: true },
  datasets: { type: Array, required: true },
  height: { type: Number, default: 300 },
  lineColor: { type: String, default: '#3B82F6' },
  gridColor: { type: String, default: 'rgba(255,255,255,0.1)' },
  textColor: { type: String, default: 'rgba(255,255,255,0.7)' },
});

const containerRef = ref(null);
const svgRef = ref(null);
const svgWidth = ref(600);
const svgHeight = ref(300);
const tooltip = ref({ visible: false, text: '' });
const tooltipTargetRef = ref(null);
const isDark = ref(document.documentElement.classList.contains('dark'));

let resizeObserver = null;
let themeObserver = null;
const padding = { top: 30, right: 20, bottom: 50, left: 60 };
const gridLinesCount = 5;

const hasData = computed(() => props.labels?.length > 0 && props.datasets[0]?.data?.length > 0);

// Chart data
const chartData = computed(() => props.datasets[0]?.data || []);
const labels = computed(() => props.labels || []);

// Scale calculations
const minVal = computed(() => Math.min(...chartData.value, 0));
const maxVal = computed(() => Math.max(...chartData.value, 1));
const range = computed(() => maxVal.value - minVal.value || 1);

const chartW = computed(() => svgWidth.value - padding.left - padding.right);
const chartH = computed(() => svgHeight.value - padding.top - padding.bottom);
const stepX = computed(() => labels.value.length > 1 ? chartW.value / (labels.value.length - 1) : 0);

// Y-axis ticks
const yTicks = computed(() => {
  return Array.from({ length: gridLinesCount + 1 }, (_, i) => {
    const value = Math.round(maxVal.value - (range.value / gridLinesCount) * i);
    const y = padding.top + (chartH.value / gridLinesCount) * i;
    return { y, label: value };
  });
});

// Data points (with real coordinates)
const points = computed(() => {
  const data = chartData.value;
  const labelsArr = labels.value;
  const pointsArr = [];
  for (let i = 0; i < data.length; i++) {
    const x = padding.left + i * stepX.value;
    const normalizedValue = (data[i] - minVal.value) / range.value;
    const y = padding.top + chartH.value - normalizedValue * chartH.value;
    pointsArr.push({ x, y, value: data[i], label: labelsArr[i] });
  }
  return pointsArr;
});

// Polyline points string
const linePoints = computed(() => {
  return points.value.map(p => `${p.x},${p.y}`).join(' ');
});

// X-axis labels (show a limited set)
const xLabels = computed(() => {
  return labels.value.filter((_, i) =>
    i % Math.ceil(labels.value.length / 8) === 0 || i === labels.value.length - 1
  );
});

// Resize handler
function handleResize() {
  if (!containerRef.value) return;
  const rect = containerRef.value.getBoundingClientRect();
  svgWidth.value = rect.width;
  svgHeight.value = props.height;
}

// Tooltip
function showTooltip(event, pt) {
  tooltipTargetRef.value = event.currentTarget;
  tooltip.value = {
    visible: true,
    text: `${pt.label}: ${pt.value}`,
  };
}

function hideTooltip() {
  tooltip.value.visible = false;
  tooltipTargetRef.value = null;
}

// Theme observer
function handleThemeChange() {
  isDark.value = document.documentElement.classList.contains('dark');
}

onMounted(() => {
  handleResize();
  resizeObserver = new ResizeObserver(handleResize);
  if (containerRef.value) resizeObserver.observe(containerRef.value);

  themeObserver = new MutationObserver(handleThemeChange);
  themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

onBeforeUnmount(() => {
  if (resizeObserver) resizeObserver.disconnect();
  if (themeObserver) themeObserver.disconnect();
});
</script>

<style scoped>
.perspective-1000 {
  perspective: 1000px;
}

/* Elevated glass slab */
.line-chart-slab {
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.5);
  isolation: isolate;
  transition: background 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}
.dark .line-chart-slab {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

.skeleton-overlay {
  pointer-events: none;
}

/* Responsive & accessibility */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .line-chart-slab {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .line-chart-slab {
    transition: none !important;
    transform: none !important;
  }
}
</style>