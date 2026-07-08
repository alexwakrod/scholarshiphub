<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-[1400px] mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          Audit Logs
        </h1>
      </div>

      <!-- Filters (Elevated Glass) -->
      <div class="glass-elevated rounded-2xl p-4 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)]">
        <div class="flex flex-wrap items-center gap-3">
          <GlassInput v-model="filters.user_id" placeholder="User ID" class="w-24" type="number" />
          <GlassSelect v-model="filters.action" :options="actionOptions" placeholder="Action" />
          <GlassInput v-model="filters.subject_type" placeholder="Subject type" class="w-44" />
          <GlassInput v-model="filters.date_from" type="date" placeholder="From" />
          <GlassInput v-model="filters.date_to" type="date" placeholder="To" />
          <div class="relative inline-flex">
            <button @click="applyFilters" class="glass-btn-filter group relative w-10 h-10 rounded-xl bg-blue-600 text-white overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#3b82f6] flex items-center justify-center"
              ref="filterBtnRef"
              @mouseenter="showTooltip('filter', $event.currentTarget)"
              @mouseleave="hideTooltip">
              <span class="relative z-10"><FunnelIcon class="w-5 h-5" /></span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
            <GlassTooltip :visible="tooltipVisible && tooltipId === 'filter'" :targetRef="tooltipTargetRef" :delay="0">
              <span class="text-xs text-white">Apply Filters</span>
            </GlassTooltip>
          </div>
          <div class="relative inline-flex">
            <button @click="resetFilters" class="glass-btn-clear group relative w-10 h-10 rounded-xl bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 text-gray-500 dark:text-white/40 hover:text-gray-900 dark:hover:text-white overflow-hidden transition-all duration-300 hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#3b82f6] flex items-center justify-center"
              ref="clearBtnRef"
              @mouseenter="showTooltip('clear', $event.currentTarget)"
              @mouseleave="hideTooltip">
              <span class="relative z-10"><XMarkIcon class="w-5 h-5" /></span>
              <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity blur-sm"></div>
            </button>
            <GlassTooltip :visible="tooltipVisible && tooltipId === 'clear'" :targetRef="tooltipTargetRef" :delay="0">
              <span class="text-xs text-white">Clear Filters</span>
            </GlassTooltip>
          </div>
        </div>
      </div>

      <!-- Elevated Glass Table Container -->
      <div class="glass-elevated rounded-2xl border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] overflow-hidden">
        <div class="sticky top-0 z-10 glass-table-header px-4 py-3 border-b border-gray-200/30 dark:border-white/5">
          <div class="grid grid-cols-12 gap-4 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-white/40">
            <span class="col-span-1">ID</span>
            <span class="col-span-2">User</span>
            <span class="col-span-1">Action</span>
            <span class="col-span-2">Subject</span>
            <span class="col-span-3">Description</span>
            <span class="col-span-2">Date</span>
            <span class="col-span-1 text-center">Diff</span>
          </div>
        </div>

        <div class="divide-y divide-gray-200/30 dark:divide-white/5">
          <div
            v-for="(log, idx) in logs.data"
            :key="log.id"
            class="glass-table-row grid grid-cols-12 gap-4 px-4 py-3 items-center transition-all duration-300 hover:bg-white/5 dark:hover:bg-white/5"
            :style="{ '--i': idx }"
          >
            <span class="col-span-1 text-sm text-gray-500 dark:text-white/40 tabular-nums">{{ log.id }}</span>
            <span class="col-span-2 text-sm font-medium text-gray-900 dark:text-white truncate">{{ log.user?.name || 'System' }}</span>
            <span class="col-span-1">
              <span class="status-pill inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="{
                  'bg-green-500/10 text-green-400 border border-green-500/30': log.action === 'created',
                  'bg-blue-500/10 text-blue-400 border border-blue-500/30': log.action === 'updated',
                  'bg-red-500/10 text-red-400 border border-red-500/30': log.action === 'deleted',
                  'bg-gray-400/10 text-gray-400 border border-gray-400/30': !['created','updated','deleted'].includes(log.action)
                }">
                {{ log.action }}
              </span>
            </span>
            <span class="col-span-2 text-sm text-gray-600 dark:text-white/70 truncate">
              {{ log.subject_type ? log.subject_type.split('\\').pop() + ' #' + log.subject_id : '—' }}
            </span>
            <span class="col-span-3 text-sm text-gray-600 dark:text-white/70 truncate">{{ log.description }}</span>
            <span class="col-span-2 text-sm text-gray-500 dark:text-white/40">{{ formatDate(log.created_at) }}</span>
            <span class="col-span-1 flex justify-center">
              <div class="relative inline-flex">
                <button
                  v-if="log.properties && (log.properties.old || log.properties.new)"
                  @click="selectedDiff = log; diffOpen = true"
                  class="glass-icon-btn group relative w-8 h-8 rounded-lg flex items-center justify-center text-blue-400 hover:text-blue-300 transition-colors"
                  @mouseenter="showTooltip(`diff-${log.id}`, $event.currentTarget)"
                  @mouseleave="hideTooltip"
                >
                  <EyeIcon class="w-4 h-4" />
                </button>
                <GlassTooltip v-if="log.properties && (log.properties.old || log.properties.new)" :visible="tooltipVisible && tooltipId === `diff-${log.id}`" :targetRef="tooltipTargetRef" :delay="0">
                  <span class="text-xs text-white">View Diff</span>
                </GlassTooltip>
                <span v-else class="text-sm text-gray-500 dark:text-white/40">—</span>
              </div>
            </span>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="!logs.data.length" class="text-center py-16">
          <div class="glass-empty-state rounded-xl p-8 inline-flex flex-col items-center gap-3">
            <ClipboardDocumentListIcon class="w-10 h-10 text-gray-400 dark:text-white/20" />
            <span class="text-sm text-gray-500 dark:text-white/40">No logs found.</span>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="logs.links" class="flex items-center justify-center gap-1">
        <button
          v-for="link in logs.links"
          :key="link.label"
          :href="link.url || '#'"
          :disabled="!link.url"
          class="glass-pagination-btn relative px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 dark:text-white/50 hover:text-gray-900 dark:hover:text-white/80 disabled:opacity-30 disabled:cursor-not-allowed transition-all duration-300 hover:bg-white/10 dark:hover:bg-white/5 active:scale-95"
          :class="{ 'bg-blue-500/10 dark:bg-blue-400/20 text-blue-600 dark:text-blue-400 shadow-[0_0_10px_rgba(59,130,246,0.2)]': link.active }"
          v-html="link.label"
        ></button>
      </div>

      <!-- Diff Modal (Ultra-Hero) -->
      <GlassModal v-model="diffOpen" @close="diffOpen = false">
        <div v-if="selectedDiff" class="space-y-5">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <ArrowsRightLeftIcon class="w-5 h-5 text-blue-400" />
            Change Details
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Old Values -->
            <div class="glass-surface rounded-xl p-4 border border-gray-200/60 dark:border-white/5">
              <h4 class="text-xs font-semibold uppercase tracking-wider text-red-400 mb-2">Before</h4>
              <pre class="whitespace-pre-wrap text-xs text-gray-700 dark:text-white/70 max-h-64 overflow-y-auto custom-scrollbar">{{ JSON.stringify(selectedDiff.properties.old, null, 2) }}</pre>
            </div>
            <!-- New Values -->
            <div class="glass-surface rounded-xl p-4 border border-gray-200/60 dark:border-white/5">
              <h4 class="text-xs font-semibold uppercase tracking-wider text-green-400 mb-2">After</h4>
              <pre class="whitespace-pre-wrap text-xs text-gray-700 dark:text-white/70 max-h-64 overflow-y-auto custom-scrollbar">{{ JSON.stringify(selectedDiff.properties.new, null, 2) }}</pre>
            </div>
          </div>
        </div>
      </GlassModal>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassModal from '@/Components/GlassModal.vue';
import GlassTooltip from '@/Components/GlassTooltip.vue';
import {
  FunnelIcon,
  XMarkIcon,
  EyeIcon,
  ArrowsRightLeftIcon,
  ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  logs: Object,
  filters: Object,
  actions: Array,
});

const defaultFilters = {
  user_id: props.filters?.user_id || '',
  action: props.filters?.action || '',
  subject_type: props.filters?.subject_type || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
};

const filters = reactive({ ...defaultFilters });
const actionOptions = props.actions.map(a => ({ value: a, label: a }));

const diffOpen = ref(false);
const selectedDiff = ref(null);

// Tooltip state
const tooltipVisible = ref(false);
const tooltipId = ref(null);
const tooltipTargetRef = ref(null);

function showTooltip(id, target) {
  tooltipId.value = id;
  tooltipTargetRef.value = target;
  tooltipVisible.value = true;
}
function hideTooltip() {
  tooltipVisible.value = false;
  tooltipTargetRef.value = null;
}

function applyFilters() {
  const query = {};
  Object.entries(filters).forEach(([k, v]) => { if (v) query[k] = v; });
  router.get(route('admin.audit-logs.index'), query, { preserveState: true, replace: true });
}

function resetFilters() {
  Object.assign(filters, { user_id: '', action: '', subject_type: '', date_from: '', date_to: '' });
  applyFilters();
}

function formatDate(dateStr) {
  try {
    return new Date(dateStr).toLocaleString();
  } catch { return dateStr; }
}
</script>

<style scoped>
/* ============================================================
   GLASS AUDIT LOGS – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.05);
}

.glass-table-header {
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}
.dark .glass-table-header {
  background: rgba(0, 0, 0, 0.5);
}

.glass-table-row {
  opacity: 0;
  animation: row-fade-in 0.35s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 30ms);
  transform: rotateY(0.5deg) rotateX(0.2deg);
  transition: background 0.2s ease, transform 0.2s ease;
}
.glass-table-row:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-1px) scale(1.005);
  background: rgba(255, 255, 255, 0.08);
  z-index: 5;
}
.dark .glass-table-row:hover {
  background: rgba(255, 255, 255, 0.04);
}

@keyframes row-fade-in {
  0% { opacity: 0; transform: rotateY(0.5deg) rotateX(0.2deg) translateY(8px); }
  100% { opacity: 1; transform: rotateY(0.5deg) rotateX(0.2deg) translateY(0); }
}

.glass-btn-filter {
  transform: rotateY(-2deg);
  will-change: transform;
}
.glass-btn-filter:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.glass-btn-filter:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
}

.glass-btn-clear {
  transform: rotateY(-1deg);
  will-change: transform;
}
.glass-btn-clear:hover {
  transform: rotateY(0deg) translateY(-1px) scale(1.01);
}

.glass-icon-btn {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid transparent;
  transition: all 0.2s ease;
}
.glass-icon-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.15);
  transform: translateY(-1px);
}
.dark .glass-icon-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
}

.status-pill {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  transition: all 0.2s ease;
}
.status-pill:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.glass-pagination-btn {
  transform: rotateY(-1deg);
  will-change: transform;
  transition: all 0.2s ease;
}
.glass-pagination-btn:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-1px) scale(1.02);
}
.glass-pagination-btn:active:not(:disabled) {
  transform: scale(0.95);
}

.glass-surface {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.dark .glass-surface {
  background: rgba(0, 0, 0, 0.2);
}

.glass-empty-state {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.dark .glass-empty-state {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.06);
}

.localized-glow {
  filter: blur(20px);
  opacity: 0;
  transition: opacity 0.4s ease;
}
.group:hover .localized-glow {
  opacity: 0.3;
}

.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.15);
  border-radius: 999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.15);
}

@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-table-row,
  .glass-table-row:hover,
  .glass-btn-filter,
  .glass-btn-clear,
  .glass-icon-btn,
  .glass-pagination-btn {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>