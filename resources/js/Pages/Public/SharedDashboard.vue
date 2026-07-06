<template>
  <div class="min-h-screen bg-gray-900 text-white p-6">
    <div class="max-w-6xl mx-auto">
      <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold">{{ dashboardName }}</h1>
        <p class="text-xs text-white/40">Shared dashboard · Read‑only</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <!-- Stats widget -->
        <GlassCard v-if="widgets.includes('stats') && widgetData.stats" class="p-6">
          <h3 class="text-sm font-semibold text-white/70 uppercase mb-4">Key Metrics</h3>
          <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-3 rounded-lg bg-white/5">
              <p class="text-2xl font-bold">{{ widgetData.stats.activeScholarships }}</p>
              <p class="text-xs text-white/50">Active Scholarships</p>
            </div>
            <div class="text-center p-3 rounded-lg bg-white/5">
              <p class="text-2xl font-bold">{{ widgetData.stats.totalStudents }}</p>
              <p class="text-xs text-white/50">Students</p>
            </div>
            <div class="text-center p-3 rounded-lg bg-white/5">
              <p class="text-2xl font-bold">{{ widgetData.stats.totalAiReviews }}</p>
              <p class="text-xs text-white/50">AI Reviews</p>
            </div>
          </div>
        </GlassCard>

        <!-- Registrations chart -->
        <GlassCard v-if="widgets.includes('registrations_chart') && widgetData.registrations" class="p-6">
          <h3 class="text-sm font-semibold text-white/70 uppercase mb-4">Registrations (Last 12 Months)</h3>
          <BarChartCanvas
            :labels="widgetData.registrations.labels"
            :datasets="[{ data: widgetData.registrations.values }]"
            :height="200"
            bar-color="#3B82F6"
          />
        </GlassCard>

        <!-- AI Reviews chart -->
        <GlassCard v-if="widgets.includes('reviews_chart') && widgetData.reviews" class="p-6">
          <h3 class="text-sm font-semibold text-white/70 uppercase mb-4">AI Reviews (Last 30 Days)</h3>
          <LineChartCanvas
            :labels="widgetData.reviews.labels"
            :datasets="[{ data: widgetData.reviews.values }]"
            :height="200"
            line-color="#10B981"
          />
        </GlassCard>

        <!-- Score Distribution -->
        <GlassCard v-if="widgets.includes('score_distribution') && widgetData.scoreDistribution" class="p-6">
          <h3 class="text-sm font-semibold text-white/70 uppercase mb-4">Match Score Distribution</h3>
          <BarChartCanvas
            :labels="widgetData.scoreDistribution.labels"
            :datasets="[{ data: widgetData.scoreDistribution.values }]"
            :height="200"
            bar-color="#8B5CF6"
          />
        </GlassCard>

        <!-- Top Countries -->
        <GlassCard v-if="widgets.includes('top_countries') && widgetData.topCountries" class="p-6">
          <h3 class="text-sm font-semibold text-white/70 uppercase mb-4">Top Scholarship Countries</h3>
          <BarChartCanvas
            :labels="widgetData.topCountries.labels"
            :datasets="[{ data: widgetData.topCountries.values }]"
            :height="200"
            bar-color="#F59E0B"
          />
        </GlassCard>

        <!-- Avg Match by Provider -->
        <GlassCard v-if="widgets.includes('avg_match_provider') && widgetData.avgMatchByProvider" class="p-6">
          <h3 class="text-sm font-semibold text-white/70 uppercase mb-4">Average Match Score by Provider</h3>
          <BarChartCanvas
            :labels="widgetData.avgMatchByProvider.labels"
            :datasets="[{ data: widgetData.avgMatchByProvider.values }]"
            :height="200"
            bar-color="#EC4899"
          />
        </GlassCard>
      </div>

      <div class="mt-12 text-center text-xs text-white/30">
        Powered by ScholarshipHub
      </div>
    </div>
  </div>
</template>

<script setup>
import GlassCard from '@/Components/GlassCard.vue';
import BarChartCanvas from '@/Components/BarChartCanvas.vue';
import LineChartCanvas from '@/Components/LineChartCanvas.vue';

defineProps({
  dashboardName: String,
  widgets: Array,
  widgetData: Object,
});
</script>