<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-6xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          Custom Reports
        </h1>
      </div>

      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar: saved reports list (Elevated Glass) -->
        <div class="w-full lg:w-64 flex-shrink-0 space-y-4">
          <div class="glass-elevated rounded-2xl p-4 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] max-h-[70vh] overflow-y-auto custom-scrollbar">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-white/40 mb-3">Saved Reports</h3>
            <div class="space-y-1">
              <button
                v-for="(report, idx) in reports"
                :key="report.id"
                @click="loadReport(report)"
                class="glass-sidebar-item block w-full text-left px-3 py-2 rounded-xl text-sm transition-all duration-300"
                :class="[
                  editingReport?.id === report.id
                    ? 'bg-blue-500/10 dark:bg-blue-400/20 text-blue-600 dark:text-blue-400 border-l-2 border-blue-500'
                    : 'text-gray-700 dark:text-white/70 hover:bg-white/10 dark:hover:bg-white/5'
                ]"
                :style="{ '--i': idx }"
              >
                {{ report.name }}
              </button>
            </div>
            <button @click="newReport" class="w-full mt-2 px-3 py-2 rounded-xl bg-blue-600/10 dark:bg-blue-400/20 text-blue-600 dark:text-blue-400 text-sm font-medium hover:bg-blue-600/20 transition-all duration-300">
              + New Report
            </button>
          </div>
        </div>

        <!-- Main builder area -->
        <div class="flex-1 space-y-6">
          <!-- Report config form (Elevated Glass) -->
          <div class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)]">
            <div class="space-y-5">
              <GlassInput v-model="form.name" placeholder="Report name" class="max-w-md" />
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Model</label>
                  <GlassSelect
                    v-model="form.model"
                    :options="[{value:'Scholarship',label:'Scholarships'},{value:'User',label:'Users'}]"
                    placeholder="Select model"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Chart Type</label>
                  <GlassSelect
                    v-model="form.chart_type"
                    :options="[{value:'table',label:'Table'},{value:'bar',label:'Bar Chart'},{value:'line',label:'Line Chart'}]"
                    placeholder="Table"
                  />
                </div>
              </div>

              <!-- Field selection checkboxes -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Fields</label>
                <div class="flex flex-wrap gap-3">
                  <GlassCheckbox
                    v-for="field in availableFields"
                    :key="field.key"
                    :modelValue="form.fields.includes(field.key)"
                    @update:modelValue="val => toggleField(field.key, val)"
                    :label="field.label"
                  />
                </div>
              </div>

              <!-- Filters -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Date From</label>
                  <GlassInput v-model="form.filters.date_from" type="date" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Date To</label>
                  <GlassInput v-model="form.filters.date_to" type="date" />
                </div>
              </div>

              <!-- Aggregation -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Aggregation</label>
                  <GlassSelect
                    v-model="form.aggregation.type"
                    :options="[{value:'',label:'None'},{value:'count',label:'Count'},{value:'avg',label:'Average'},{value:'sum',label:'Sum'}]"
                    placeholder="None"
                  />
                </div>
                <div v-if="form.aggregation.type && form.aggregation.type !== 'count'">
                  <label class="block text-sm font-medium text-gray-700 dark:text-white/70 mb-2">Field</label>
                  <GlassSelect
                    v-model="form.aggregation.field"
                    :options="aggregationFieldOptions"
                    placeholder="Select field"
                  />
                </div>
              </div>

              <!-- Action buttons -->
              <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200/30 dark:border-white/5">
                <button @click="previewReport" :disabled="!form.fields.length" class="glass-btn-preview group relative px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(99,102,241,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-indigo-400">
                  <span class="relative z-10 flex items-center gap-2">
                    <MagnifyingGlassIcon class="w-4 h-4" />
                    Preview
                  </span>
                  <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-purple-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
                </button>
                <button @click="saveReport" :disabled="!form.name || !form.fields.length" class="glass-btn-primary group relative px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-blue-400">
                  <span class="relative z-10 flex items-center gap-2">
                    <CheckCircleIcon v-if="editingReport" class="w-4 h-4" />
                    <PlusCircleIcon v-else class="w-4 h-4" />
                    {{ editingReport ? 'Update' : 'Save' }}
                  </span>
                  <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
                </button>
                <button v-if="editingReport" @click="deleteReport" class="glass-btn-danger group relative px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(239,68,68,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-red-400 ml-auto sm:ml-0">
                  <span class="relative z-10 flex items-center gap-2">
                    <TrashIcon class="w-4 h-4" />
                    Delete
                  </span>
                  <div class="absolute inset-0 bg-red-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
                </button>
              </div>
            </div>
          </div>

          <!-- Preview area (Elevated Glass) -->
          <div class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)]">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <TableCellsIcon class="w-5 h-5 text-blue-400" />
              Preview
            </h3>
            <div v-if="previewData !== null">
              <!-- Table -->
              <div v-if="form.chart_type === 'table'" class="overflow-x-auto custom-scrollbar glass-surface rounded-xl border border-gray-200/60 dark:border-white/5">
                <table class="w-full text-xs text-left">
                  <thead class="glass-table-header sticky top-0 z-10 text-gray-500 dark:text-white/40 uppercase">
                    <tr>
                      <th v-for="col in form.fields" :key="col" class="px-3 py-2 font-semibold tracking-wider">{{ col }}</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200/30 dark:divide-white/5">
                    <tr v-for="(row, idx) in previewData" :key="idx" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                      <td v-for="col in form.fields" :key="col" class="px-3 py-1.5 text-gray-700 dark:text-white/70">{{ row[col] }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Chart preview placeholder -->
              <div v-else class="text-sm text-gray-500 dark:text-white/40 py-8 text-center">
                <ChartBarIcon class="w-10 h-10 mx-auto mb-2 opacity-50" />
                Chart preview for {{ form.chart_type }} is not yet implemented. Data: {{ previewData }}
              </div>
            </div>
            <div v-else class="text-sm text-gray-500 dark:text-white/40 py-8 text-center">
              <MagnifyingGlassIcon class="w-10 h-10 mx-auto mb-2 opacity-50" />
              Configure and click Preview to see results.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassCheckbox from '@/Components/GlassCheckbox.vue';
import {
  MagnifyingGlassIcon,
  PlusCircleIcon,
  CheckCircleIcon,
  TrashIcon,
  TableCellsIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  reports: Array,
});

const editingReport = ref(null);
const previewData = ref(null);

const scholarshipFields = [
  { key: 'id', label: 'ID' }, { key: 'title', label: 'Title' }, { key: 'provider', label: 'Provider' },
  { key: 'country', label: 'Country' }, { key: 'amount', label: 'Amount' }, { key: 'deadline', label: 'Deadline' },
  { key: 'status', label: 'Status' }, { key: 'created_at', label: 'Created' },
];
const userFields = [
  { key: 'id', label: 'ID' }, { key: 'name', label: 'Name' }, { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role' }, { key: 'created_at', label: 'Joined' },
];

const availableFields = computed(() => form.model === 'Scholarship' ? scholarshipFields : userFields);
const aggregationFieldOptions = computed(() => availableFields.value.filter(f => f.key !== 'id'));

const form = reactive({
  name: '',
  model: 'Scholarship',
  fields: [],
  chart_type: 'table',
  filters: { date_from: '', date_to: '' },
  aggregation: { type: '', field: '' },
});

function toggleField(key, selected) {
  if (selected) form.fields.push(key);
  else form.fields = form.fields.filter(f => f !== key);
}

function newReport() {
  editingReport.value = null;
  form.name = '';
  form.model = 'Scholarship';
  form.fields = [];
  form.chart_type = 'table';
  form.filters = { date_from: '', date_to: '' };
  form.aggregation = { type: '', field: '' };
  previewData.value = null;
}

function loadReport(report) {
  editingReport.value = report;
  form.name = report.name;
  form.model = report.model;
  form.fields = report.fields || [];
  form.chart_type = report.chart_type || 'table';
  form.filters = report.filters || { date_from: '', date_to: '' };
  form.aggregation = report.aggregation || { type: '', field: '' };
  previewData.value = null;
}

async function previewReport() {
  try {
    const payload = {
      model: form.model,
      fields: form.fields,
      filters: form.filters,
      aggregation: form.aggregation,
    };
    const { data } = await axios.post('/admin/reports/preview', payload);
    previewData.value = data.data;
  } catch (e) {
    console.error('Preview error:', e);
  }
}

async function saveReport() {
  const url = editingReport.value
    ? `/admin/reports/${editingReport.value.id}`
    : '/admin/reports';
  const method = editingReport.value ? 'put' : 'post';
  try {
    await axios[method](url, form);
    router.reload();
  } catch (e) {
    console.error('Save error:', e);
  }
}

async function deleteReport() {
  if (!editingReport.value) return;
  try {
    await axios.delete(`/admin/reports/${editingReport.value.id}`);
    router.reload();
  } catch (e) {
    console.error('Delete error:', e);
  }
}
</script>

<style scoped>
/* ============================================================
   GLASS CUSTOM REPORTS – THEME‑AWARE & BLUEPRINT COMPLIANT
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

/* Sidebar items with stagger */
.glass-sidebar-item {
  opacity: 0;
  animation: item-fade-in 0.3s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 40ms);
  transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
}
.glass-sidebar-item:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateX(2px);
}

@keyframes item-fade-in {
  0% { opacity: 0; transform: translateY(8px); }
  100% { opacity: 1; transform: translateY(0); }
}

/* Action buttons */
.glass-btn-primary,
.glass-btn-preview,
.glass-btn-danger {
  transform: rotateY(-2deg);
  will-change: transform;
}
.glass-btn-primary:hover:not(:disabled),
.glass-btn-preview:hover:not(:disabled),
.glass-btn-danger:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.glass-btn-primary:active:not(:disabled),
.glass-btn-preview:active:not(:disabled),
.glass-btn-danger:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
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
  .glass-btn-preview,
  .glass-btn-danger,
  .glass-btn-primary:hover,
  .glass-btn-preview:hover,
  .glass-btn-danger:hover {
    transform: none !important;
  }
  .glass-sidebar-item:hover {
    transform: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .glass-sidebar-item,
  .glass-btn-primary,
  .glass-btn-preview,
  .glass-btn-danger {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>