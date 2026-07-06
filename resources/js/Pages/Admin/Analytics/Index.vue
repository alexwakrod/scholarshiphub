<template>
  <AppLayout>
    <div class="p-6 space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Analytics Dashboard</h1>
        <div class="flex items-center gap-3">
          <FullScreenToggle />
          <button
            @click="annotationsEnabled = !annotationsEnabled"
            class="px-3 py-1 rounded-lg text-xs"
            :class="annotationsEnabled ? 'bg-yellow-600 text-white' : 'bg-white/10 text-white/70'"
          >
            {{ annotationsEnabled ? 'Annotations ON' : 'Annotations OFF' }}
          </button>
          <button
            @click="resetLayout"
            class="px-3 py-1 rounded-lg bg-white/10 text-white text-xs hover:bg-white/20"
          >
            Reset Layout
          </button>
        </div>
      </div>

      <!-- Draggable widget grid -->
      <DashboardWidgetGrid
        :initialWidgets="widgets"
        :columns="3"
        :rowHeight="220"
        layoutSaveUrl="/admin/dashboard/layout"
        @layout-changed="onLayoutChanged"
      >
        <!-- Widget: Key Metrics -->
        <template #stats>
          <div class="grid grid-cols-2 gap-2">
            <div class="p-3 rounded-lg bg-white/5 text-center">
              <p class="text-xl font-bold text-white">{{ totalUsers }}</p>
              <p class="text-xs text-white/50">Total Users</p>
            </div>
            <div class="p-3 rounded-lg bg-white/5 text-center">
              <p class="text-xl font-bold text-white">{{ totalStudents }}</p>
              <p class="text-xs text-white/50">Students</p>
            </div>
            <div class="p-3 rounded-lg bg-white/5 text-center">
              <p class="text-xl font-bold text-white">{{ activeScholarships }}</p>
              <p class="text-xs text-white/50">Active Scholarships</p>
            </div>
            <div class="p-3 rounded-lg bg-white/5 text-center">
              <p class="text-xl font-bold text-white">{{ totalAiReviews }}</p>
              <p class="text-xs text-white/50">AI Reviews</p>
            </div>
          </div>
        </template>

        <!-- Widget: Registrations Chart -->
        <template #registrations_chart>
          <div class="relative">
            <BarChartCanvas
              v-if="registrationChartData.labels.length"
              :labels="registrationChartData.labels"
              :datasets="[registrationChartData.dataset]"
              :height="180"
              bar-color="#3B82F6"
            />
            <ChartAnnotationOverlay
              chartId="registrations_chart"
              :enabled="annotationsEnabled"
              :saveUrl="annotationsUrl"
            />
            <div v-if="!registrationChartData.labels.length" class="text-white/50 text-sm">No data.</div>
          </div>
        </template>

        <!-- Widget: AI Reviews Chart -->
        <template #reviews_chart>
          <div class="relative">
            <!-- Vue template tag groups elements without adding extra HTML nodes -->
            <template v-if="reviewsChartData.labels.length">
              <LineChartCanvas
                :labels="reviewsChartData.labels"
                :datasets="[reviewsChartData.dataset]"
                :height="180"
                line-color="#10B981"
              />
              <ChartAnnotationOverlay
                chartId="reviews_chart"
                :enabled="annotationsEnabled"
                :saveUrl="annotationsUrl"
              />
            </template>
    
            <div v-else class="text-white/50 text-sm">No data.</div>
          </div>
        </template>
        <!-- Widget: Score Distribution -->
        <template #score_distribution>
          <BarChartCanvas
            v-if="scoreDistLabels.length"
            :labels="scoreDistLabels"
            :datasets="[{ data: scoreDistValues }]"
            :height="180"
            bar-color="#8B5CF6"
          />
          <div v-else class="text-white/50 text-sm">No data.</div>
        </template>

        <!-- Widget: Top Countries -->
        <template #top_countries>
          <BarChartCanvas
            v-if="topCountriesLabels.length"
            :labels="topCountriesLabels"
            :datasets="[{ data: topCountriesCounts }]"
            :height="180"
            bar-color="#F59E0B"
          />
          <div v-else class="text-white/50 text-sm">No data.</div>
        </template>

        <!-- Widget: Avg Match by Provider -->
        <template #avg_match_provider>
          <BarChartCanvas
            v-if="avgMatchLabels.length"
            :labels="avgMatchLabels"
            :datasets="[{ data: avgMatchValues }]"
            :height="180"
            bar-color="#EC4899"
          />
          <div v-else class="text-white/50 text-sm">No data.</div>
        </template>

        <!-- Widget: Activity Heatmap -->
        <template #activity_heatmap>
          <ActivityHeatCalendar
            v-if="activityHeatmapReady"
            :data="activityHeatmapData"
            :year="currentYear"
          />
          <div v-else class="text-white/50 text-sm">Loading activity...</div>
        </template>

        <!-- Widget: Activity Feed -->
        <template #activity_feed>
          <ActivityFeed title="Recent Activity" :limit="8" :poll-interval="30000" />
        </template>
      </DashboardWidgetGrid>
    </div>

    <!-- Quick Actions Sidebar -->
    <QuickActionsSidebar />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DashboardWidgetGrid from '@/Components/DashboardWidgetGrid.vue';
import BarChartCanvas from '@/Components/BarChartCanvas.vue';
import LineChartCanvas from '@/Components/LineChartCanvas.vue';
import ActivityHeatCalendar from '@/Components/ActivityHeatCalendar.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import FullScreenToggle from '@/Components/FullScreenToggle.vue';
import QuickActionsSidebar from '@/Components/QuickActionsSidebar.vue';
import ChartAnnotationOverlay from '@/Components/ChartAnnotationOverlay.vue';

const props = defineProps({
  totalUsers: Number,
  totalStudents: Number,
  totalAdmins: Number,
  activeScholarships: Number,
  expiredScholarships: Number,
  totalAiReviews: Number,
  totalApplications: Number,
  registrations: Array,
  reviewsOverTime: Array,
  scoreDistribution: Object,
  topCountries: Array,
  avgMatchByProvider: Array,
});

const annotationsEnabled = ref(false);
const annotationsUrl = '/admin/analytics/annotations';

// Registration chart data
const registrationChartData = computed(() => ({
  labels: props.registrations?.map(r => r.month) || [],
  dataset: { data: props.registrations?.map(r => r.count) || [], label: 'Registrations' },
}));

const reviewsChartData = computed(() => ({
  labels: props.reviewsOverTime?.map(r => r.day) || [],
  dataset: { data: props.reviewsOverTime?.map(r => r.count) || [], label: 'Reviews' },
}));

const scoreDistLabels = computed(() => Object.keys(props.scoreDistribution || {}));
const scoreDistValues = computed(() => Object.values(props.scoreDistribution || {}));
const topCountriesLabels = computed(() => props.topCountries?.map(c => c.country) || []);
const topCountriesCounts = computed(() => props.topCountries?.map(c => c.count) || []);
const avgMatchLabels = computed(() => props.avgMatchByProvider?.map(p => p.provider) || []);
const avgMatchValues = computed(() => props.avgMatchByProvider?.map(p => parseFloat(p.avg_score).toFixed(1)) || []);

const currentYear = ref(new Date().getFullYear());
const activityHeatmapData = ref([]);
const activityHeatmapReady = ref(false);

onMounted(async () => {
  try {
    const { data } = await axios.get(`/admin/activity-heatmap?year=${currentYear.value}`);
    activityHeatmapData.value = data;
  } catch (e) {
    console.error('Failed to load activity heatmap:', e);
  } finally {
    activityHeatmapReady.value = true;
  }
});

// Widget layout state
const widgets = ref([
  { widget_id: 'stats', title: 'Key Metrics', width: 1, height: 1, visible: true },
  { widget_id: 'registrations_chart', title: 'Registrations', width: 2, height: 1, visible: true },
  { widget_id: 'reviews_chart', title: 'AI Reviews', width: 2, height: 1, visible: true },
  { widget_id: 'score_distribution', title: 'Score Distribution', width: 2, height: 1, visible: true },
  { widget_id: 'top_countries', title: 'Top Countries', width: 1, height: 1, visible: true },
  { widget_id: 'avg_match_provider', title: 'Avg Match by Provider', width: 2, height: 1, visible: true },
  { widget_id: 'activity_heatmap', title: 'Activity Heatmap', width: 3, height: 1, visible: true },
  { widget_id: 'activity_feed', title: 'Recent Activity', width: 2, height: 1, visible: true },
]);

// Load saved layout from backend
onMounted(async () => {
  try {
    const { data } = await axios.get('/admin/dashboard/layout');
    if (data && data.length) {
      widgets.value = data;
    }
  } catch (e) {
    console.error('Failed to load dashboard layout:', e);
  }
});

function onLayoutChanged(layout) {
  widgets.value = layout;
}

function resetLayout() {
  widgets.value = widgets.value.map(w => ({ ...w, width: 1, height: 1, visible: true }));
  axios.put('/admin/dashboard/layout', { widgets: widgets.value });
}
</script>