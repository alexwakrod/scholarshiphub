<template>
  <AppLayout>
    <div class="p-4 md:p-6 space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          Admin <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Dashboard</span>
        </h1>
        <div class="flex items-center gap-3">
          <FullScreenToggle />
          <span v-if="lastUpdated" class="text-xs text-gray-500 dark:text-white/30">
            Updated {{ lastUpdated }}
          </span>
        </div>
      </div>

      <!-- Quick Stats Cards (Elevated Glass) -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="glass-elevated rounded-2xl p-5 text-center transform -rotate-y-1 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300"
        >
          <p class="text-3xl font-bold text-gray-900 dark:text-white tabular-nums">{{ stat.value }}</p>
          <p class="text-xs text-gray-500 dark:text-white/40 mt-1 uppercase tracking-wide">{{ stat.label }}</p>
        </div>
      </div>

      <!-- Charts Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Registrations Chart -->
        <GlassCard variant="elevated" class="p-5 rounded-2xl">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white/80 uppercase tracking-wide mb-4">Registrations (12 Months)</h3>
          <BarChartCanvas
            v-if="registrationChartData.labels.length"
            :labels="registrationChartData.labels"
            :datasets="[registrationChartData.dataset]"
            :height="220"
            bar-color="#3B82F6"
          />
          <div v-else class="text-gray-500 dark:text-white/40 text-sm py-8 text-center">No data</div>
        </GlassCard>

        <!-- AI Reviews Chart -->
        <GlassCard variant="elevated" class="p-5 rounded-2xl">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white/80 uppercase tracking-wide mb-4">AI Reviews (30 Days)</h3>
          <LineChartCanvas
            v-if="reviewsChartData.labels.length"
            :labels="reviewsChartData.labels"
            :datasets="[reviewsChartData.dataset]"
            :height="220"
            line-color="#10B981"
          />
          <div v-else class="text-gray-500 dark:text-white/40 text-sm py-8 text-center">No data</div>
        </GlassCard>

        <!-- Score Distribution -->
        <GlassCard variant="elevated" class="p-5 rounded-2xl">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white/80 uppercase tracking-wide mb-4">Match Score Distribution</h3>
          <BarChartCanvas
            v-if="scoreDistLabels.length"
            :labels="scoreDistLabels"
            :datasets="[{ data: scoreDistValues }]"
            :height="220"
            bar-color="#8B5CF6"
          />
          <div v-else class="text-gray-500 dark:text-white/40 text-sm py-8 text-center">No data</div>
        </GlassCard>

        <!-- Top Countries -->
        <GlassCard variant="elevated" class="p-5 rounded-2xl">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white/80 uppercase tracking-wide mb-4">Top Scholarship Countries</h3>
          <BarChartCanvas
            v-if="topCountriesLabels.length"
            :labels="topCountriesLabels"
            :datasets="[{ data: topCountriesCounts }]"
            :height="220"
            bar-color="#F59E0B"
          />
          <div v-else class="text-gray-500 dark:text-white/40 text-sm py-8 text-center">No data</div>
        </GlassCard>
      </div>

      <!-- Activity Section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <GlassCard variant="elevated" class="p-5 rounded-2xl lg:col-span-2">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white/80 uppercase tracking-wide mb-4">Activity Heatmap</h3>
          <ActivityHeatCalendar
            v-if="activityHeatmapReady"
            :data="activityHeatmapData"
            :year="currentYear"
          />
          <div v-else class="text-gray-500 dark:text-white/40 text-sm py-8 text-center">Loading...</div>
        </GlassCard>

        <GlassCard variant="elevated" class="p-5 rounded-2xl">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white/80 uppercase tracking-wide mb-4">Recent Activity</h3>
          <ActivityFeed title="" :limit="8" :poll-interval="30000" />
        </GlassCard>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import BarChartCanvas from '@/Components/BarChartCanvas.vue';
import LineChartCanvas from '@/Components/LineChartCanvas.vue';
import ActivityHeatCalendar from '@/Components/ActivityHeatCalendar.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import FullScreenToggle from '@/Components/FullScreenToggle.vue';

const props = defineProps({
  totalUsers: Number,
  totalStudents: Number,
  totalAdmins: Number,
  activeScholarships: Number,
  expiredScholarships: Number,
  totalAiReviews: Number,
  totalApplications: Number,
  averageMatchScore: Number,
  registrations: Array,
  reviewsOverTime: Array,
  scoreDistribution: Object,
  topCountries: Array,
  avgMatchByProvider: Array,
});

// Local reactive state – initialised from Inertia props
const data = reactive({
  totalUsers: props.totalUsers ?? 0,
  activeScholarships: props.activeScholarships ?? 0,
  totalAiReviews: props.totalAiReviews ?? 0,
  averageMatchScore: props.averageMatchScore ?? 0,
  registrations: props.registrations ?? [],
  reviewsOverTime: props.reviewsOverTime ?? [],
  scoreDistribution: props.scoreDistribution ?? {},
  topCountries: props.topCountries ?? [],
});

const lastUpdated = ref('');

const stats = computed(() => [
  { label: 'Total Users', value: data.totalUsers },
  { label: 'Active Scholarships', value: data.activeScholarships },
  { label: 'AI Reviews', value: data.totalAiReviews },
  { label: 'Avg Match Score', value: (data.averageMatchScore ?? 0) + '%' },
]);

// Chart data (reactive via the `data` object)
const registrationChartData = computed(() => ({
  labels: data.registrations?.map(r => r.month) || [],
  dataset: { data: data.registrations?.map(r => r.count) || [], label: 'Registrations' },
}));

const reviewsChartData = computed(() => ({
  labels: data.reviewsOverTime?.map(r => r.day) || [],
  dataset: { data: data.reviewsOverTime?.map(r => r.count) || [], label: 'Reviews' },
}));

const scoreDistLabels = computed(() => Object.keys(data.scoreDistribution || {}));
const scoreDistValues = computed(() => Object.values(data.scoreDistribution || {}));
const topCountriesLabels = computed(() => data.topCountries?.map(c => c.country) || []);
const topCountriesCounts = computed(() => data.topCountries?.map(c => c.count) || []);

const currentYear = ref(new Date().getFullYear());
const activityHeatmapData = ref([]);
const activityHeatmapReady = ref(false);

let pollTimer = null;

async function fetchStats() {
  try {
    const response = await axios.get('/admin/dashboard/stats');
    Object.assign(data, response.data);
    lastUpdated.value = new Date().toLocaleTimeString();
  } catch (e) {
    console.error('Failed to fetch admin stats:', e);
  }
}

async function loadActivityHeatmap() {
  try {
    const { data: heatmap } = await axios.get(`/admin/activity-heatmap?year=${currentYear.value}`);
    activityHeatmapData.value = heatmap;
  } catch (e) {
    console.error('Failed to load activity heatmap:', e);
  } finally {
    activityHeatmapReady.value = true;
  }
}

onMounted(() => {
  // Initial fetch in case props were empty
  fetchStats();
  loadActivityHeatmap();
  // Poll every 30 seconds
  pollTimer = setInterval(fetchStats, 30000);
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
});
</script>

<style scoped>
.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}
</style>