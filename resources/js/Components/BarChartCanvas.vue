<template>
  <div class="bar-chart-wrapper perspective-1000">
    <!-- Elevated glass slab -->
    <div
      ref="containerRef"
      class="bar-chart-slab relative w-full rounded-2xl overflow-hidden"
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
          <rect x="20" y="180" width="40" height="20" rx="4" fill="url(#shimmer)" />
          <rect x="80" y="150" width="40" height="50" rx="4" fill="url(#shimmer)" />
          <rect x="140" y="120" width="40" height="80" rx="4" fill="url(#shimmer)" />
          <rect x="200" y="100" width="40" height="100" rx="4" fill="url(#shimmer)" />
          <rect x="260" y="140" width="40" height="60" rx="4" fill="url(#shimmer)" />
          <rect x="320" y="170" width="40" height="30" rx="4" fill="url(#shimmer)" />
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
        <!-- Etched grid lines -->
        <line
          v-for="(tick, i) in gridLines"
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
          v-for="(tick, i) in gridLines"
          :key="'yl' + i"
          :x="padding.left - 12"
          :y="tick.y + 4"
          :fill="isDark ? 'rgba(255,255,255,0.65)' : 'rgba(0,0,0,0.55)'"
          font-size="11"
          font-family="Inter, system-ui, -apple-system, sans-serif"
          text-anchor="end"
        >{{ tick.label }}</text>

        <!-- Bars -->
        <g v-for="(bar, i) in bars" :key="'bar' + i">
          <!-- Glow under bar -->
          <rect
            :x="bar.x"
            :y="bar.y"
            :width="bar.width"
            :height="bar.height"
            :fill="barColor"
            :opacity="0.15"
            rx="4"
            :filter="`blur(4px)`"
          />
          <!-- Bar -->
          <rect
            :ref="el => barRefs[i] = el"
            :x="bar.x"
            :y="bar.y"
            :width="bar.width"
            :height="bar.height"
            :fill="barColor"
            class="bar-rect transition-all duration-200 cursor-pointer"
            rx="4"
            @mouseenter="showTooltip($event, bar)"
            @mousemove="showTooltip($event, bar)"
            @mouseleave="hideTooltip"
          />
          <!-- Value on top -->
          <text
            :x="bar.x + bar.width / 2"
            :y="bar.y - 8"
            :fill="isDark ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.55)'"
            font-size="10"
            font-family="Inter, system-ui, -apple-system, sans-serif"
            text-anchor="middle"
          >{{ bar.value }}</text>
        </g>

        <!-- X-axis labels -->
        <text
          v-for="(bar, i) in bars"
          :key="'xl' + i"
          :x="bar.x + bar.width / 2"
          :y="svgHeight - padding.bottom + 20"
          :fill="isDark ? 'rgba(255,255,255,0.65)' : 'rgba(0,0,0,0.55)'"
          font-size="10"
          font-family="Inter, system-ui, -apple-system, sans-serif"
          text-anchor="middle"
        >{{ bar.label }}</text>
      </svg>
    </div>

    <!-- GlassTooltip -->
    <GlassTooltip
      :visible="tooltip.visible"
      :targetRef="tooltipTargetRef"
      :delay="0"
    >
      <p class="font-semibold text-gray-900 dark:text-white text-xs">{{ tooltip.label }}</p>
      <p class="text-gray-500 dark:text-white/60 text-xs mt-0.5">{{ tooltip.text }}</p>
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
  barColor: { type: String, default: '#3B82F6' },
  gridColor: { type: String, default: 'rgba(255,255,255,0.1)' },
  textColor: { type: String, default: 'rgba(255,255,255,0.7)' },
});

const containerRef = ref(null);
const svgRef = ref(null);
const svgWidth = ref(600);
const svgHeight = ref(300);
const barRefs = ref({});
const tooltipTargetRef = ref(null);
const tooltip = ref({ visible: false, x: 0, y: 0, label: '', text: '' });
const isDark = ref(document.documentElement.classList.contains('dark'));

let resizeObserver = null;
let themeObserver = null;
const padding = { top: 30, right: 20, bottom: 50, left: 60 };
const gridLinesCount = 5;

const hasData = computed(() => props.labels?.length > 0 && props.datasets[0]?.data?.length > 0);

// Compute bars layout
const bars = computed(() => {
  const labels = props.labels || [];
  const data = props.datasets[0]?.data || [];
  if (!labels.length || !data.length) return [];

  const maxVal = Math.max(...data, 1);
  const chartW = svgWidth.value - padding.left - padding.right;
  const chartH = svgHeight.value - padding.top - padding.bottom;
  const barWidth = Math.max(10, (chartW / labels.length) * 0.6);
  const gap = (chartW - barWidth * labels.length) / (labels.length + 1);

  return labels.map((label, i) => {
    const value = data[i] || 0;
    const barHeight = (value / maxVal) * chartH;
    return {
      x: padding.left + gap + i * (barWidth + gap),
      y: padding.top + chartH - barHeight,
      width: barWidth,
      height: barHeight,
      label,
      value,
    };
  });
});

// Grid lines positions and labels
const gridLines = computed(() => {
  const data = props.datasets[0]?.data || [];
  const maxVal = Math.max(...data, 1);
  const chartH = svgHeight.value - padding.top - padding.bottom;
  return Array.from({ length: gridLinesCount + 1 }, (_, i) => {
    const value = Math.round((maxVal / gridLinesCount) * (gridLinesCount - i));
    const y = padding.top + (chartH / gridLinesCount) * i;
    return { y, label: value };
  });
});

// Update dimensions on resize
function handleResize() {
  if (!containerRef.value) return;
  const rect = containerRef.value.getBoundingClientRect();
  svgWidth.value = rect.width;
  svgHeight.value = props.height;
}

// Tooltip handlers
function showTooltip(event, bar) {
  // Find the corresponding SVG rect element to use as target
  const idx = bars.value.indexOf(bar);
  if (idx >= 0 && barRefs.value[idx]) {
    tooltipTargetRef.value = barRefs.value[idx];
  } else {
    tooltipTargetRef.value = event.currentTarget;
  }
  tooltip.value = {
    visible: true,
    label: bar.label,
    text: `${bar.value}`,
  };
}

function hideTooltip() {
  tooltip.value.visible = false;
  tooltipTargetRef.value = null;
}

// Watch theme changes
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
.bar-chart-slab {
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
.dark .bar-chart-slab {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

.skeleton-overlay {
  pointer-events: none;
}

/* Bar hover effect */
.bar-rect {
  transition: opacity 0.2s ease, filter 0.2s ease;
}
.bar-rect:hover {
  opacity: 0.85;
  filter: brightness(1.15);
}

/* Responsive & accessibility */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .bar-chart-slab {
    transform: none !important;
  }
  .bar-rect {
    /* Touch devices get no hover, active is immediate */
  }
  .bar-rect:active {
    opacity: 0.75;
  }
}

@media (prefers-reduced-motion: reduce) {
  .bar-chart-slab {
    transition: none !important;
    transform: none !important;
  }
  .bar-rect {
    transition: none !important;
  }
}
</style>