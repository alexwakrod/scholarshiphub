<template>
  <div v-if="activeJobs.length" class="fixed top-16 right-4 z-50 space-y-2 max-w-sm">
    <div
      v-for="job in activeJobs"
      :key="job.id"
      class="glass-overlay rounded-xl p-3 border border-gray-200 dark:border-white/20 shadow-xl"
    >
      <div class="flex items-center justify-between">
        <span class="text-sm text-gray-900 dark:text-white truncate">{{ job.label }}</span>
        <button @click="dismiss(job.id)" class="text-gray-500 dark:text-white/50 hover:text-gray-900 dark:hover:text-white ml-2">&times;</button>
      </div>
      <div class="h-1.5 bg-gray-200 dark:bg-white/10 rounded-full mt-2 overflow-hidden">
        <div
          class="h-full bg-blue-500 transition-all duration-500"
          :style="{ width: job.progress + '%' }"
        ></div>
      </div>
      <p class="text-xs text-gray-500 dark:text-white/40 mt-1">{{ job.status }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';

const activeJobs = ref([]);
let pollTimer = null;

async function fetchJobs() {
  try {
    const { data } = await axios.get('/api/jobs/active');
    activeJobs.value = data.filter(j => j.status === 'pending' || j.status === 'processing');
  } catch (e) {
    console.error('[JobProgressNotification] fetch error:', e);
  }
}

function dismiss(jobId) {
  activeJobs.value = activeJobs.value.filter(j => j.id !== jobId);
}

onMounted(() => {
  fetchJobs();
  pollTimer = setInterval(fetchJobs, 3000);
});

onBeforeUnmount(() => {
  if (pollTimer) clearInterval(pollTimer);
});
</script>