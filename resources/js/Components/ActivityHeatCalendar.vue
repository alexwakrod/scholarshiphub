<template>
  <div class="overflow-x-auto custom-scrollbar">
    <div class="flex gap-1">
      <div
        v-for="(cell, idx) in cells"
        :key="idx"
        class="w-3 h-3 rounded-sm"
        :style="{ backgroundColor: cell.color }"
        :title="cell.tooltip"
      ></div>
    </div>
    <div class="flex justify-between text-xs text-gray-500 dark:text-white/40 mt-1">
      <span>{{ months[0] }}</span>
      <span v-for="m in months.slice(1)" :key="m">{{ m }}</span>
    </div>
    <div class="flex justify-end mt-1 gap-1 text-xs text-gray-500 dark:text-white/40">
      <span>Less</span>
      <div v-for="(col, idx) in legendColors" :key="idx" class="w-3 h-3 rounded-sm" :style="{ backgroundColor: col }" />
      <span>More</span>
    </div>
    <p v-if="error" class="text-red-500 dark:text-red-400 text-xs mt-2">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  data: { type: Array, default: null },
  year: { type: Number, default: new Date().getFullYear() },
  fetchUrl: { type: String, default: '/admin/activity-heatmap' },
});

const fetchedData = ref([]);
const error = ref('');

const legendColors = [
  'rgba(0,0,0,0.05)',
  'rgba(59,130,246,0.3)',
  'rgba(59,130,246,0.55)',
  'rgba(59,130,246,0.8)',
  'rgba(59,130,246,1)',
];

const effectiveData = computed(() => (props.data !== null ? props.data : fetchedData.value));

const dataMap = computed(() => {
  const map = {};
  effectiveData.value.forEach(d => { map[d.date] = d.count; });
  return map;
});

const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

const cells = computed(() => {
  const start = new Date(props.year, 0, 1);
  const end = new Date(props.year, 11, 31);
  const cells = [];
  let current = new Date(start);
  while (current <= end) {
    const dateStr = current.toISOString().split('T')[0];
    const count = dataMap.value[dateStr] || 0;
    cells.push({ color: getColor(count), tooltip: `${dateStr}: ${count} events` });
    current.setDate(current.getDate() + 1);
  }
  return cells;
});

function getColor(count) {
  if (count <= 0) return legendColors[0];
  if (count <= 3) return legendColors[1];
  if (count <= 7) return legendColors[2];
  if (count <= 15) return legendColors[3];
  return legendColors[4];
}

onMounted(async () => {
  if (props.data !== null) return;
  try {
    const { data } = await axios.get(props.fetchUrl);
    if (Array.isArray(data)) fetchedData.value = data;
  } catch (e) {
    error.value = 'Failed to load activity data.';
    console.error('ActivityHeatCalendar fetch error:', e);
  }
});
</script>