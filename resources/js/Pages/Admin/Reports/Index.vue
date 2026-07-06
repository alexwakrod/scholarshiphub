<template>
  <AppLayout>
    <div class="p-6 max-w-5xl mx-auto">
      <h1 class="text-2xl font-bold text-white mb-6">Custom Reports</h1>

      <div class="flex gap-6">
        <!-- Sidebar: saved reports list -->
        <div class="w-64 flex-shrink-0 space-y-4">
          <GlassCard class="p-4 space-y-2 max-h-[70vh] overflow-y-auto custom-scrollbar">
            <h3 class="text-sm font-semibold text-white/70 uppercase mb-2">Saved Reports</h3>
            <button
              v-for="report in reports"
              :key="report.id"
              @click="loadReport(report)"
              class="block w-full text-left px-3 py-2 rounded-lg text-sm hover:bg-white/10 transition-colors"
              :class="{ 'bg-white/20': editingReport?.id === report.id }"
            >
              {{ report.name }}
            </button>
            <button @click="newReport" class="w-full px-3 py-2 rounded-lg bg-blue-600/20 text-blue-300 text-sm hover:bg-blue-600/30">
              + New Report
            </button>
          </GlassCard>
        </div>

        <!-- Main builder area -->
        <div class="flex-1 space-y-6">
          <!-- Report config form -->
          <GlassCard variant="elevated" class="p-6 space-y-4">
            <GlassInput v-model="form.name" placeholder="Report name" class="max-w-md" />
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-white/70 mb-1">Model</label>
                <GlassSelect
                  v-model="form.model"
                  :options="[{value:'Scholarship',label:'Scholarships'},{value:'User',label:'Users'}]"
                  placeholder="Select model"
                />
              </div>
              <div>
                <label class="block text-sm text-white/70 mb-1">Chart Type</label>
                <GlassSelect
                  v-model="form.chart_type"
                  :options="[{value:'table',label:'Table'},{value:'bar',label:'Bar Chart'},{value:'line',label:'Line Chart'}]"
                  placeholder="Table"
                />
              </div>
            </div>

            <!-- Field selection checkboxes -->
            <div>
              <label class="block text-sm text-white/70 mb-2">Fields</label>
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
            <div class="grid grid-cols-2 gap-4">
              <GlassInput v-model="form.filters.date_from" type="date" label="Date From" />
              <GlassInput v-model="form.filters.date_to" type="date" label="Date To" />
            </div>

            <!-- Aggregation -->
            <div class="grid grid-cols-3 gap-4 items-end">
              <div>
                <label class="block text-sm text-white/70 mb-1">Aggregation</label>
                <GlassSelect
                  v-model="form.aggregation.type"
                  :options="[{value:'',label:'None'},{value:'count',label:'Count'},{value:'avg',label:'Average'},{value:'sum',label:'Sum'}]"
                  placeholder="None"
                />
              </div>
              <div v-if="form.aggregation.type && form.aggregation.type !== 'count'">
                <label class="block text-sm text-white/70 mb-1">Field</label>
                <GlassSelect
                  v-model="form.aggregation.field"
                  :options="aggregationFieldOptions"
                  placeholder="Select field"
                />
              </div>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-3 pt-4">
              <button @click="previewReport" :disabled="!form.fields.length" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700 disabled:opacity-50">
                Preview
              </button>
              <button @click="saveReport" :disabled="!form.name || !form.fields.length" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50">
                {{ editingReport ? 'Update' : 'Save' }}
              </button>
              <button v-if="editingReport" @click="deleteReport" class="px-4 py-2 rounded-lg bg-red-600/80 text-white text-sm hover:bg-red-700">
                Delete
              </button>
            </div>
          </GlassCard>

          <!-- Preview area -->
          <GlassCard variant="elevated" class="p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Preview</h3>
            <div v-if="previewData !== null">
              <!-- Table -->
              <div v-if="form.chart_type === 'table'" class="overflow-x-auto glass-surface rounded-lg">
                <table class="w-full text-sm text-left">
                  <thead class="text-xs text-white/50 uppercase border-b border-white/10">
                    <tr>
                      <th v-for="col in form.fields" :key="col" class="px-4 py-2">{{ col }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, idx) in previewData" :key="idx" class="border-b border-white/5">
                      <td v-for="col in form.fields" :key="col" class="px-4 py-2 text-white/70">{{ row[col] }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Chart preview placeholder -->
              <div v-else class="text-white/50 text-sm">
                Chart preview for {{ form.chart_type }} — data: {{ previewData }}
              </div>
            </div>
            <div v-else class="text-white/50 text-sm">Configure and click Preview to see results.</div>
          </GlassCard>
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
import GlassCard from '@/Components/GlassCard.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassCheckbox from '@/Components/GlassCheckbox.vue';

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