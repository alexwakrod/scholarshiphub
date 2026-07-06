<template>
  <AppLayout>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-white">Import Scholarships</h1>

      <!-- Upload form -->
      <GlassCard variant="elevated" class="p-6">
        <form @submit.prevent="uploadFile" class="space-y-4">
          <div>
            <label class="block text-sm text-white/70 mb-2">Choose CSV file</label>
            <GlassFileUpload v-model="files" :multiple="false" accept=".csv" />
            <p class="text-xs text-white/40 mt-1">Max 10 MB. The CSV must contain columns: title, description, provider, country, deadline, amount (optional), source_url (optional).</p>
          </div>
          <div v-if="previewData.length" class="space-y-2">
            <h3 class="text-sm font-medium text-white">Preview (first 5 rows)</h3>
            <div class="overflow-x-auto glass-surface rounded-lg max-h-48">
              <table class="w-full text-xs text-left">
                <thead class="text-white/50 uppercase">
                  <tr>
                    <th v-for="h in previewHeaders" :key="h" class="px-2 py-1">{{ h }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, i) in previewData" :key="i" class="border-t border-white/5">
                    <td v-for="h in previewHeaders" :key="h" class="px-2 py-1 text-white/70">{{ row[h] }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <button
              type="submit"
              :disabled="files.length === 0 || uploading"
              class="px-6 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
            >
              {{ uploading ? 'Starting...' : 'Start Import' }}
            </button>
            <button
              v-if="files.length"
              @click="clearFile"
              type="button"
              class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm hover:bg-white/20"
            >
              Clear
            </button>
          </div>
        </form>
      </GlassCard>

      <!-- Import history -->
      <GlassCard variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Import History</h2>
        <div v-if="imports.length === 0" class="text-white/50 text-sm">No previous imports.</div>
        <div v-else class="space-y-3">
          <div
            v-for="job in imports"
            :key="job.id"
            class="p-4 rounded-lg bg-white/5 flex items-center justify-between"
          >
            <div>
              <p class="text-white text-sm">Import #{{ job.id }}</p>
              <p class="text-xs text-white/50">Status: {{ job.status }} | Rows: {{ job.processed_rows }}/{{ job.total_rows }} | Failed: {{ job.failed_rows }}</p>
            </div>
            <button
              @click="checkStatus(job.id)"
              class="text-xs px-3 py-1 rounded bg-white/10 text-white hover:bg-white/20"
            >
              Refresh
            </button>
          </div>
        </div>
      </GlassCard>

      <!-- Progress modal for current import -->
      <GlassModal v-model="showProgress" @close="showProgress = false">
        <div class="space-y-4">
          <h3 class="text-xl font-bold text-white">Import Progress</h3>
          <div v-if="currentJob">
            <div class="flex justify-between text-sm text-white/70">
              <span>{{ currentJob.processed_rows }} / {{ currentJob.total_rows }} rows</span>
              <span>{{ currentJob.progress }}%</span>
            </div>
            <div class="h-2 bg-white/10 rounded-full mt-2 overflow-hidden">
              <div
                class="h-full bg-blue-500 transition-all"
                :style="{ width: currentJob.progress + '%' }"
              ></div>
            </div>
            <p class="text-xs text-white/50 mt-2">Status: {{ currentJob.status }}</p>
            <p v-if="currentJob.status === 'completed'" class="text-green-400 text-sm mt-1">Import completed successfully.</p>
            <p v-if="currentJob.status === 'failed'" class="text-red-400 text-sm mt-1">Import failed.</p>
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
import GlassCard from '@/Components/GlassCard.vue';
import GlassFileUpload from '@/Components/GlassFileUpload.vue';
import GlassModal from '@/Components/GlassModal.vue';

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
    // First preview? We'll just send and get job ID
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
        // Update list
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