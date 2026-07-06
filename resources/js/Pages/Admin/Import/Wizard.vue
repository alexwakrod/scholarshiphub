<template>
  <AppLayout>
    <div class="p-6 max-w-3xl mx-auto">
      <h1 class="text-2xl font-bold text-white mb-6">Import Scholarships</h1>

      <!-- Step indicator -->
      <div class="flex items-center gap-2 mb-8">
        <div
          v-for="(step, idx) in steps"
          :key="idx"
          class="flex-1 h-2 rounded-full"
          :class="idx <= currentStep ? 'bg-blue-500' : 'bg-white/10'"
        ></div>
      </div>

      <!-- Step 1: File Upload -->
      <GlassCard v-if="currentStep === 0" variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">1. Choose CSV File</h2>
        <GlassFileUpload v-model="file" :multiple="false" accept=".csv" :uploadUrl="''" />
        <div class="mt-4 flex justify-end">
          <button
            @click="nextStep"
            :disabled="!file.length"
            class="px-6 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </GlassCard>

      <!-- Step 2: Preview & Mapping -->
      <GlassCard v-if="currentStep === 1" variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">2. Map Columns</h2>
        <p class="text-sm text-white/60 mb-4">Match CSV columns to the required fields.</p>

        <div v-if="csvHeaders.length" class="space-y-3">
          <div
            v-for="field in requiredFields"
            :key="field.key"
            class="flex items-center gap-3"
          >
            <label class="w-32 text-sm text-white/70">{{ field.label }} <span class="text-red-400">*</span></label>
            <GlassSelect
              v-model="mapping[field.key]"
              :options="csvHeaderOptions"
              placeholder="Select column"
            />
          </div>

          <!-- Optional fields -->
          <div
            v-for="field in optionalFields"
            :key="field.key"
            class="flex items-center gap-3"
          >
            <label class="w-32 text-sm text-white/70">{{ field.label }}</label>
            <GlassSelect
              v-model="mapping[field.key]"
              :options="optionalHeaderOptions"
              placeholder="Ignore"
            />
          </div>
        </div>

        <div class="mt-6">
          <h3 class="text-sm font-medium text-white mb-2">Data Preview (first 3 rows)</h3>
          <div class="overflow-x-auto glass-surface rounded-lg max-h-48">
            <table class="w-full text-xs text-left">
              <thead class="text-white/50 uppercase">
                <tr>
                  <th v-for="h in csvHeaders" :key="h" class="px-2 py-1">{{ h }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, i) in previewRows" :key="i" class="border-t border-white/5">
                  <td v-for="h in csvHeaders" :key="h" class="px-2 py-1 text-white/70">{{ row[h] }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-4 flex justify-between">
          <button @click="currentStep = 0" class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm">Back</button>
          <button
            @click="startImport"
            :disabled="!allRequiredMapped"
            class="px-6 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
          >
            Start Import
          </button>
        </div>
      </GlassCard>

      <!-- Step 3: Progress -->
      <GlassCard v-if="currentStep === 2" variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">3. Import Progress</h2>
        <div v-if="importing">
          <div class="h-2 bg-white/10 rounded-full overflow-hidden mb-2">
            <div class="h-full bg-blue-500 transition-all" :style="{ width: progress + '%' }"></div>
          </div>
          <p class="text-sm text-white/50">{{ processed }} / {{ total }} rows processed</p>
          <p v-if="failedRows" class="text-sm text-red-400 mt-1">{{ failedRows }} rows failed</p>
        </div>
        <div v-if="importDone" class="text-green-400 text-sm">Import completed.</div>
        <div class="mt-4">
          <button
            @click="resetWizard"
            class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm hover:bg-white/20"
          >
            New Import
          </button>
        </div>
      </GlassCard>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassFileUpload from '@/Components/GlassFileUpload.vue';

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
    // Auto‑map if header names match exactly (case insensitive)
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
    // Poll progress
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