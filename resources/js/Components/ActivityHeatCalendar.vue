<template>
  <div ref="wrapperRef" class="overflow-x-auto custom-scrollbar">
    <!-- SVG Heatmap -->
    <svg :width="svgWidth" height="42" viewBox="0 0 1200 42" preserveAspectRatio="xMinYMid meet" class="block">
      <!-- Day cells -->
      <rect
        v-for="(cell, idx) in cells"
        :key="idx"
        :x="cell.x"
        y="4"
        width="2.5"
        height="10"
        :rx="1"
        :fill="cell.color"
        class="cursor-pointer transition-colors duration-200 hover:brightness-125"
        @mouseenter="showTooltip($event, cell)"
        @mouseleave="hideTooltip"
      />
      <!-- Month labels -->
      <text
        v-for="m in monthLabels"
        :key="m.label"
        :x="m.x"
        y="30"
        fill="currentColor"
        class="text-[10px] font-medium"
        :class="isDark ? 'text-white/40' : 'text-gray-500'"
        text-anchor="start"
      >{{ m.label }}</text>
    </svg>

    <!-- Legend -->
    <div class="flex justify-end mt-1 gap-1 text-xs" :class="isDark ? 'text-white/40' : 'text-gray-500'">
      <span>Less</span>
      <div
        v-for="(col, idx) in legendColors"
        :key="idx"
        class="w-3 h-3 rounded-sm"
        :style="{ backgroundColor: col }"
      />
      <span>More</span>
    </div>

    <p v-if="error" class="text-red-500 dark:text-red-400 text-xs mt-2">{{ error }}</p>

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
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import GlassTooltip from '@/Components/GlassTooltip.vue';

const props = defineProps({
  data: { type: Array, default: null },       // pre‑fetched array of { date, count }
  year: { type: Number, default: new Date().getFullYear() },
  fetchUrl: { type: String, default: '/admin/activity-heatmap' },
});

const wrapperRef = ref(null);
const fetchedData = ref([]);
const error = ref('');

const isDark = ref(document.documentElement.classList.contains('dark'));

// Theme‑aware color scale
const legendColors = [
  'rgba(0,0,0,0.04)',     // 0
  'rgba(59,130,246,0.25)',
  'rgba(59,130,246,0.45)',
  'rgba(59,130,246,0.7)',
  'rgba(59,130,246,1)',
];

const effectiveData = computed(() => (props.data !== null ? props.data : fetchedData.value));

const dataMap = computed(() => {
  const map = {};
  effectiveData.value.forEach(d => { map[d.date] = d.count; });
  return map;
});

const svgWidth = computed(() => {
  const days = cells.value.length;
  return Math.max(days * 3.5 + 40, 1200); // each cell 2.5px + gap 1px ≈ 3.5px
});

// Cell positions (one per day, horizontal line)
const cells = computed(() => {
  const start = new Date(props.year, 0, 1);
  const end = new Date(props.year, 11, 31);
  const cells = [];
  let current = new Date(start);
  let x = 40; // starting X after month label area
  while (current <= end) {
    const dateStr = current.toISOString().split('T')[0];
    const count = dataMap.value[dateStr] || 0;
    cells.push({
      x,
      y: 4,
      color: getColor(count),
      date: dateStr,
      count,
    });
    x += 3.5;
    current.setDate(current.getDate() + 1);
  }
  return cells;
});

// Month labels (approximate mid‑point of each month)
const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const monthLabels = computed(() => {
  const labels = [];
  for (let m = 0; m < 12; m++) {
    const monthStart = new Date(props.year, m, 1);
    const monthEnd = new Date(props.year, m + 1, 0);
    const midDate = new Date(props.year, m, 15);
    const dayOfYear = Math.floor((midDate - new Date(props.year, 0, 1)) / (1000 * 60 * 60 * 24));
    const x = 40 + dayOfYear * 3.5;
    labels.push({ label: monthNames[m], x });
  }
  return labels;
});

function getColor(count) {
  if (count <= 0) return legendColors[0];
  if (count <= 3) return legendColors[1];
  if (count <= 7) return legendColors[2];
  if (count <= 15) return legendColors[3];
  return legendColors[4];
}

// Tooltip logic
const tooltip = ref({ visible: false, text: '' });
const tooltipTargetRef = ref(null);

function showTooltip(event, cell) {
  tooltipTargetRef.value = event.currentTarget;
  tooltip.value = {
    visible: true,
    text: `${cell.date}: ${cell.count} events`,
  };
}

function hideTooltip() {
  tooltip.value.visible = false;
  tooltipTargetRef.value = null;
}

// Theme observer
let themeObserver = null;
onMounted(async () => {
  // Detect dark mode
  isDark.value = document.documentElement.classList.contains('dark');
  themeObserver = new MutationObserver(() => {
    isDark.value = document.documentElement.classList.contains('dark');
  });
  themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

  // Fetch data if not provided as prop
  if (props.data !== null) return;
  try {
    const { data } = await axios.get(props.fetchUrl);
    if (Array.isArray(data)) fetchedData.value = data;
  } catch (e) {
    error.value = 'Failed to load activity data.';
    console.error('ActivityHeatCalendar fetch error:', e);
  }
});

import { onBeforeUnmount } from 'vue';
onBeforeUnmount(() => {
  if (themeObserver) themeObserver.disconnect();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.15);
  border-radius: 999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.15);
}
</style>