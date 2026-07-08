<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-3xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          Import Scholarships
        </h1>
      </div>

      <!-- Liquid Step Indicator -->
      <div class="flex items-center gap-2">
        <div
          v-for="(step, idx) in steps"
          :key="idx"
          class="flex-1 h-2 rounded-full transition-all duration-500"
          :class="idx <= currentStep
            ? 'bg-gradient-to-r from-blue-400 to-indigo-400 shadow-[0_0_10px_rgba(59,130,246,0.4)]'
            : 'bg-gray-200 dark:bg-white/10'"
        ></div>
      </div>

      <!-- Step Content with Transition -->
      <transition name="step-slide" mode="out-in">
        <!-- Step 1: File Upload -->
        <div v-if="currentStep === 0" key="step1" class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)]">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <ArrowUpTrayIcon class="w-5 h-5 text-blue-400" />
            1. Choose CSV File
          </h2>
          <div class="glass-upload-zone group relative rounded-xl border-2 border-dashed border-gray-300 dark:border-white/10 p-8 text-center transition-all duration-300 hover:border-blue-400 dark:hover:border-blue-400/50 hover:bg-blue-500/5 dark:hover:bg-blue-400/5">
            <GlassFileUpload v-model="file" :multiple="false" accept=".csv" :uploadUrl="''" />
            <p class="text-xs text-gray-500 dark:text-white/40 mt-2">
              Max 10 MB. The CSV must contain columns: title, description, provider, country, deadline, amount (optional), source_url (optional).
            </p>
          </div>
          <div class="mt-6 flex justify-end">
            <button
              @click="nextStep"
              :disabled="!file.length"
              class="glass-btn-primary group relative px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-[#3b82f6]"
            >
              <span class="relative z-10 flex items-center gap-2">
                <ArrowRightIcon class="w-4 h-4" />
                Next
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
          </div>
        </div>

        <!-- Step 2: Column Mapping -->
        <div v-else-if="currentStep === 1" key="step2" class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)]">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
            <MapIcon class="w-5 h-5 text-blue-400" />
            2. Map Columns
          </h2>
          <p class="text-sm text-gray-500 dark:text-white/50 mb-4">Match CSV columns to the required fields.</p>

          <div v-if="csvHeaders.length" class="space-y-3">
            <!-- Required Fields -->
            <div class="mb-4">
              <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-white/40 mb-2">Required</h3>
              <div v-for="field in requiredFields" :key="field.key" class="flex items-center gap-3 mb-2">
                <label class="w-32 text-sm text-gray-700 dark:text-white/70 truncate">{{ field.label }} <span class="text-red-400">*</span></label>
                <div class="flex-1">
                  <GlassSelect
                    v-model="mapping[field.key]"
                    :options="csvHeaderOptions"
                    placeholder="Select column"
                  />
                </div>
              </div>
            </div>
            <!-- Optional Fields -->
            <div>
              <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-white/40 mb-2">Optional</h3>
              <div v-for="field in optionalFields" :key="field.key" class="flex items-center gap-3 mb-2">
                <label class="w-32 text-sm text-gray-700 dark:text-white/70 truncate">{{ field.label }}</label>
                <div class="flex-1">
                  <GlassSelect
                    v-model="mapping[field.key]"
                    :options="optionalHeaderOptions"
                    placeholder="Ignore"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Data Preview Table -->
          <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white/80 mb-2 flex items-center gap-1.5">
              <TableCellsIcon class="w-4 h-4 text-blue-400" />
              Data Preview (first 3 rows)
            </h3>
            <div class="overflow-x-auto custom-scrollbar glass-surface rounded-xl border border-gray-200/60 dark:border-white/5 max-h-48">
              <table class="w-full text-xs text-left">
                <thead class="glass-table-header sticky top-0 z-10 text-gray-500 dark:text-white/40 uppercase">
                  <tr>
                    <th v-for="h in csvHeaders" :key="h" class="px-3 py-2 font-semibold tracking-wider">{{ h }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200/30 dark:divide-white/5">
                  <tr v-for="(row, i) in previewRows" :key="i" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <td v-for="h in csvHeaders" :key="h" class="px-3 py-1.5 text-gray-700 dark:text-white/70">{{ row[h] }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="mt-6 flex justify-between">
            <button @click="currentStep = 0" class="glass-btn-back group relative px-4 py-2 rounded-xl bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 text-gray-700 dark:text-white/70 text-sm font-medium hover:bg-white/20 dark:hover:bg-white/10 transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
              <span class="flex items-center gap-1.5">
                <ArrowLeftIcon class="w-4 h-4" />
                Back
              </span>
            </button>
            <button
              @click="startImport"
              :disabled="!allRequiredMapped"
              class="glass-btn-primary group relative px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-[#3b82f6]"
            >
              <span class="relative z-10 flex items-center gap-2">
                <PlayIcon class="w-4 h-4" />
                Start Import
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
          </div>
        </div>

        <!-- Step 3: Progress -->
        <div v-else key="step3" class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)]">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <ArrowPathIcon class="w-5 h-5 text-blue-400 animate-spin" v-if="importing" />
            <CheckCircleIcon class="w-5 h-5 text-green-400" v-else-if="importDone" />
            <ClockIcon class="w-5 h-5 text-blue-400" v-else />
            3. Import Progress
          </h2>
          <div v-if="importing" class="space-y-4">
            <!-- Liquid Progress Bar -->
            <div class="h-2.5 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden shadow-inner">
              <div
                class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-500 progress-fill"
                :style="{ width: progress + '%' }"
              >
                <div class="absolute inset-0 bg-gradient-to-r from-blue-400/30 to-indigo-400/30 blur-sm"></div>
              </div>
            </div>
            <div class="flex justify-between text-xs text-gray-600 dark:text-white/50">
              <span>{{ processed }} / {{ total }} rows processed</span>
              <span>{{ progress }}%</span>
            </div>
            <p v-if="failedRows" class="text-xs text-red-400 flex items-center gap-1.5">
              <ExclamationCircleIcon class="w-3.5 h-3.5" />
              {{ failedRows }} rows failed
            </p>
          </div>
          <div v-if="importDone" class="text-sm text-green-400 flex items-center gap-2 mt-2">
            <CheckCircleIcon class="w-5 h-5" />
            Import completed.
          </div>
          <div class="mt-6">
            <button
              @click="resetWizard"
              class="glass-btn-back group relative px-4 py-2 rounded-xl bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 text-gray-700 dark:text-white/70 text-sm font-medium hover:bg-white/20 dark:hover:bg-white/10 transition-all duration-300 hover:-translate-y-0.5 active:scale-95"
            >
              <span class="flex items-center gap-1.5">
                <PlusCircleIcon class="w-4 h-4" />
                New Import
              </span>
            </button>
          </div>
        </div>
      </transition>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassFileUpload from '@/Components/GlassFileUpload.vue';
import {
  ArrowUpTrayIcon,
  ArrowRightIcon,
  ArrowLeftIcon,
  MapIcon,
  TableCellsIcon,
  PlayIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationCircleIcon,
  PlusCircleIcon,
} from '@heroicons/vue/24/outline';

const currentStep = ref(0);
const file = ref([]);
const csvHeaders = ref([]);
const previewRows = ref([]);
const mapping = reactive({});
const importing = ref(false);
const progress = ref(0);
const processed = ref(0);
const total = ref(0);
const failedRows = ref(0);
const importDone = ref(false);

const requiredFields = [
  { key: 'title', label: 'Title' },
  { key: 'description', label: 'Description' },
  { key: 'provider', label: 'Provider' },
  { key: 'country', label: 'Country' },
  { key: 'deadline', label: 'Deadline' },
];
const optionalFields = [
  { key: 'amount', label: 'Amount' },
  { key: 'source_url', label: 'Source URL' },
];

const csvHeaderOptions = computed(() => csvHeaders.value.map(h => ({ value: h, label: h })));
const optionalHeaderOptions = computed(() => [
  { value: '', label: 'Ignore' },
  ...csvHeaders.value.map(h => ({ value: h, label: h })),
]);

const allRequiredMapped = computed(() =>
  requiredFields.every(f => mapping[f.key])
);

async function nextStep() {
  if (!file.value.length) return;
  const formData = new FormData();
  formData.append('file', file.value[0]);
  try {
    const { data } = await axios.post('/admin/import/preview', formData);
    csvHeaders.value = data.headers;
    previewRows.value = data.rows;
    requiredFields.forEach(f => {
      const match = data.headers.find(h => h.toLowerCase() === f.key.toLowerCase());
      mapping[f.key] = match || '';
    });
    optionalFields.forEach(f => {
      const match = data.headers.find(h => h.toLowerCase() === f.key.toLowerCase());
      mapping[f.key] = match || '';
    });
    currentStep.value = 1;
  } catch (e) {
    console.error('Preview failed:', e);
  }
}

async function startImport() {
  if (!allRequiredMapped.value) return;
  const formData = new FormData();
  formData.append('file', file.value[0]);
  formData.append('mapping', JSON.stringify(mapping));
  importing.value = true;
  currentStep.value = 2;

  try {
    const { data } = await axios.post('/admin/import/start', formData, {
      onUploadProgress: (event) => {
        progress.value = Math.round((event.loaded / event.total) * 50);
      },
    });
    const jobId = data.jobId;
    const poll = setInterval(async () => {
      const { data: status } = await axios.get(`/admin/import/status/${jobId}`);
      processed.value = status.processed_rows;
      total.value = status.total_rows;
      failedRows.value = status.failed_rows;
      progress.value = status.total_rows ? Math.round((status.processed_rows / status.total_rows) * 100) : 0;
      if (status.status === 'completed' || status.status === 'failed') {
        clearInterval(poll);
        importing.value = false;
        importDone.value = true;
      }
    }, 1000);
  } catch (e) {
    console.error('Import failed:', e);
    importing.value = false;
  }
}

function resetWizard() {
  currentStep.value = 0;
  file.value = [];
  csvHeaders.value = [];
  previewRows.value = [];
  Object.keys(mapping).forEach(k => delete mapping[k]);
  importing.value = false;
  importDone.value = false;
}
</script>

<style scoped>
/* ============================================================
   GLASS IMPORT WIZARD – THEME‑AWARE & BLUEPRINT COMPLIANT
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

/* Back button */
.glass-btn-back {
  transform: rotateY(-1deg);
  will-change: transform;
}
.glass-btn-back:hover {
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

/* Step slide transition */
.step-slide-enter-active,
.step-slide-leave-active {
  transition: all 0.35s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.step-slide-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.98);
}
.step-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px) scale(0.98);
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
  .glass-btn-back,
  .glass-btn-primary:hover,
  .glass-btn-back:hover {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .step-slide-enter-active,
  .step-slide-leave-active,
  .glass-btn-primary,
  .glass-btn-back,
  .progress-fill::after {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>