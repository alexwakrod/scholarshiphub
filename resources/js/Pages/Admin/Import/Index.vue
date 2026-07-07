<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          Import Scholarships
        </h1>
        <!-- No extra buttons; the start import button is already prominent -->
      </div>

      <!-- Upload Form (Elevated Glass) -->
      <div class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] stagger-item" style="--i:0">
        <form @submit.prevent="uploadFile" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">
              <ArrowUpTrayIcon class="w-4 h-4 inline-block mr-1.5" />
              Choose CSV file
            </label>
            <div class="glass-upload-zone group relative rounded-xl border-2 border-dashed border-gray-300 dark:border-white/10 p-8 text-center transition-all duration-300 hover:border-blue-400 dark:hover:border-blue-400/50 hover:bg-blue-500/5 dark:hover:bg-blue-400/5">
              <GlassFileUpload v-model="files" :multiple="false" accept=".csv" />
              <p class="text-xs text-gray-500 dark:text-white/40 mt-2">
                Max 10 MB. The CSV must contain columns: title, description, provider, country, deadline, amount (optional), source_url (optional).
              </p>
            </div>
          </div>

          <!-- Preview Table (Recessed Surface) -->
          <div v-if="previewData.length" class="space-y-2">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white/80">
              <TableCellsIcon class="w-4 h-4 inline-block mr-1.5" />
              Preview (first 5 rows)
            </h3>
            <div class="overflow-x-auto custom-scrollbar glass-surface rounded-xl border border-gray-200/60 dark:border-white/5 max-h-48">
              <table class="w-full text-xs text-left">
                <thead class="glass-table-header sticky top-0 z-10 text-gray-500 dark:text-white/40 uppercase">
                  <tr>
                    <th v-for="h in previewHeaders" :key="h" class="px-3 py-2 font-semibold tracking-wider">{{ h }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200/30 dark:divide-white/5">
                  <tr v-for="(row, i) in previewData" :key="i" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <td v-for="h in previewHeaders" :key="h" class="px-3 py-1.5 text-gray-700 dark:text-white/70">{{ row[h] }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-4 pt-2">
            <button
              type="submit"
              :disabled="files.length === 0 || uploading"
              class="glass-btn-primary group relative px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-[#3b82f6]"
            >
              <span class="relative z-10 flex items-center gap-2">
                <span v-if="uploading" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
                <ArrowUpTrayIcon v-else class="w-4 h-4" />
                {{ uploading ? 'Starting...' : 'Start Import' }}
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
            <button
              v-if="files.length"
              @click="clearFile"
              type="button"
              class="glass-btn-clear group relative px-4 py-2 rounded-xl bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 text-gray-700 dark:text-white/70 text-sm font-medium hover:bg-white/20 dark:hover:bg-white/10 transition-all duration-300 hover:-translate-y-0.5 active:scale-95"
            >
              <XMarkIcon class="w-4 h-4 inline-block mr-1" />
              Clear
            </button>
          </div>
        </form>
      </div>

      <!-- Import History (Elevated Glass) -->
      <div class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] stagger-item" style="--i:1">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          <ClockIcon class="w-5 h-5 inline-block mr-1.5" />
          Import History
        </h2>
        <div v-if="imports.length === 0" class="glass-empty-state rounded-xl p-8 text-center text-sm text-gray-500 dark:text-white/40">
          <InboxIcon class="w-8 h-8 mx-auto mb-2 opacity-50" />
          No previous imports.
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="job in imports"
            :key="job.id"
            class="glass-history-card p-4 rounded-xl bg-white/5 dark:bg-white/5 border border-gray-200/30 dark:border-white/5 flex items-center justify-between transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg"
          >
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Import #{{ job.id }}</p>
              <p class="text-xs text-gray-500 dark:text-white/40 mt-1">
                Status:
                <span
                  class="status-pill inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ml-1"
                  :class="{
                    'bg-green-500/10 text-green-400 border border-green-500/30': job.status === 'completed',
                    'bg-red-500/10 text-red-400 border border-red-500/30': job.status === 'failed',
                    'bg-blue-500/10 text-blue-400 border border-blue-500/30': job.status === 'pending' || job.status === 'processing'
                  }"
                >
                  {{ job.status }}
                </span>
                | Rows: {{ job.processed_rows }}/{{ job.total_rows }} | Failed: {{ job.failed_rows }}
              </p>
            </div>
            <!-- Refresh button with tooltip -->
            <div class="relative inline-flex">
              <button
                @click="checkStatus(job.id)"
                class="glass-icon-btn group relative w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 dark:text-white/40 hover:text-blue-500 dark:hover:text-blue-400 transition-colors"
                ref="refreshBtnRef"
                @mouseenter="showTooltip('refresh', $event.currentTarget)"
                @mouseleave="hideTooltip"
              >
                <ArrowPathIcon class="w-4 h-4" />
              </button>
              <GlassTooltip :visible="tooltipVisible && tooltipId === 'refresh'" :targetRef="tooltipTargetRef" :delay="0">
                <span class="text-xs text-white">Refresh Status</span>
              </GlassTooltip>
            </div>
          </div>
        </div>
      </div>

      <!-- Progress Modal (Ultra-Hero) -->
      <GlassModal v-model="showProgress" @close="showProgress = false">
        <div class="space-y-5">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">Import Progress</h3>
          <div v-if="currentJob">
            <div class="flex justify-between text-sm text-gray-700 dark:text-white/70 mb-2">
              <span>{{ currentJob.processed_rows }} / {{ currentJob.total_rows }} rows</span>
              <span>{{ currentJob.progress }}%</span>
            </div>
            <!-- Liquid progress bar -->
            <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-500 progress-fill"
                :style="{ width: currentJob.progress + '%' }"
              >
                <div class="absolute inset-0 bg-gradient-to-r from-blue-400/30 to-indigo-400/30 blur-sm"></div>
              </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-white/40 mt-3">Status: {{ currentJob.status }}</p>
            <p v-if="currentJob.status === 'completed'" class="text-green-400 text-sm mt-2 flex items-center gap-1.5">
              <CheckCircleIcon class="w-4 h-4" />
              Import completed successfully.
            </p>
            <p v-if="currentJob.status === 'failed'" class="text-red-400 text-sm mt-2 flex items-center gap-1.5">
              <XCircleIcon class="w-4 h-4" />
              Import failed.
            </p>
          </div>
        </div>
      </GlassModal>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassFileUpload from '@/Components/GlassFileUpload.vue';
import GlassModal from '@/Components/GlassModal.vue';
import GlassTooltip from '@/Components/GlassTooltip.vue';
import {
  ArrowUpTrayIcon,
  XMarkIcon,
  TableCellsIcon,
  ClockIcon,
  InboxIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  imports: { type: Array, default: () => [] },
});

const files = ref([]);
const uploading = ref(false);
const previewData = ref([]);
const previewHeaders = ref([]);
const showProgress = ref(false);
const currentJob = ref(null);
const imports = ref(props.imports);

let statusInterval = null;

// Tooltip state (cosmetic, does not alter logic)
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

function clearFile() {
  files.value = [];
  previewData.value = [];
  previewHeaders.value = [];
}

async function uploadFile() {
  if (files.value.length === 0) return;
  uploading.value = true;
  const formData = new FormData();
  formData.append('file', files.value[0]);

  try {
    const response = await axios.post('/admin/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    const { jobId } = response.data;
    showProgress.value = true;
    currentJob.value = { id: jobId, status: 'pending', progress: 0, total_rows: 0, processed_rows: 0, failed_rows: 0 };
    pollStatus(jobId);
  } catch (error) {
    console.error('Upload failed:', error);
  } finally {
    uploading.value = false;
  }
}

function pollStatus(jobId) {
  statusInterval = setInterval(async () => {
    try {
      const { data } = await axios.get(`/admin/import/status/${jobId}`);
      currentJob.value = data;
      if (data.status === 'completed' || data.status === 'failed') {
        clearInterval(statusInterval);
        imports.value.unshift(data);
      }
    } catch (err) {
      console.error('Status poll error:', err);
      clearInterval(statusInterval);
    }
  }, 2000);
}

async function checkStatus(jobId) {
  showProgress.value = true;
  currentJob.value = { id: jobId, status: 'loading', progress: 0, total_rows: 0, processed_rows: 0, failed_rows: 0 };
  clearInterval(statusInterval);
  pollStatus(jobId);
}
</script>

<style scoped>
/* ============================================================
   GLASS IMPORT INDEX – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* Elevated glass slab */
.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.05);
}

/* Surface glass for preview table */
.glass-surface {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.dark .glass-surface {
  background: rgba(0, 0, 0, 0.2);
}

/* Sticky table header */
.glass-table-header {
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}
.dark .glass-table-header {
  background: rgba(0, 0, 0, 0.5);
}

/* Upload zone glass */
.glass-upload-zone {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  transition: border-color 0.3s ease, background 0.3s ease;
}
.dark .glass-upload-zone {
  background: rgba(255, 255, 255, 0.03);
}

/* Primary button */
.glass-btn-primary {
  transform: rotateY(-2deg);
  will-change: transform;
}
.glass-btn-primary:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.glass-btn-primary:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
}

/* Clear button */
.glass-btn-clear {
  transform: rotateY(-1deg);
  will-change: transform;
}
.glass-btn-clear:hover {
  transform: rotateY(0deg) translateY(-1px) scale(1.01);
}

/* Localized glow */
.localized-glow {
  filter: blur(20px);
  opacity: 0;
  transition: opacity 0.4s ease;
}
.group:hover .localized-glow {
  opacity: 0.3;
}

/* Icon buttons */
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

/* History cards */
.glass-history-card {
  transform: rotateY(0.5deg);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.glass-history-card:hover {
  transform: rotateY(0deg) translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

/* Status pills */
.status-pill {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  transition: all 0.2s ease;
}

/* Liquid progress fill */
.progress-fill {
  position: relative;
  overflow: hidden;
}
.progress-fill::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  animation: shimmer 1.5s infinite;
}
@keyframes shimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}

/* Empty state */
.glass-empty-state {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.dark .glass-empty-state {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.06);
}

/* Staggered entrance */
.stagger-item {
  opacity: 0;
  animation: fade-in-up 0.4s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 80ms);
}
@keyframes fade-in-up {
  0% { opacity: 0; transform: translateY(12px); }
  100% { opacity: 1; transform: translateY(0); }
}

/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.15);
  border-radius: 999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.15);
}

/* Mobile & accessibility overrides */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-btn-primary,
  .glass-btn-clear,
  .glass-history-card,
  .glass-btn-primary:hover,
  .glass-btn-clear:hover,
  .glass-history-card:hover {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .stagger-item,
  .glass-btn-primary,
  .glass-btn-clear,
  .glass-history-card,
  .progress-fill::after {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>