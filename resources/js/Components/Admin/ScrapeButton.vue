<template>
  <button
    @click="triggerScrape"
    :disabled="loading"
    class="group relative px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(16,185,129,0.3)] hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed"
  >
    <span class="relative z-10 flex items-center gap-2">
      <span v-if="loading" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
      <span v-else>AI Scrape Scholarships</span>
      <svg v-if="!loading" class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
    </span>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
  </button>
  <p v-if="message" class="mt-2 text-xs" :class="error ? 'text-red-400' : 'text-green-400'">{{ message }}</p>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const loading = ref(false);
const message = ref('');
const error = ref(false);

async function triggerScrape() {
  loading.value = true;
  message.value = '';
  error.value = false;
  try {
    await axios.post('/admin/scrape');
    message.value = 'Scraping job started. New scholarships will appear soon.';
  } catch (e) {
    error.value = true;
    message.value = e.response?.data?.message || 'Failed to start scraping.';
  } finally {
    loading.value = false;
    setTimeout(() => { message.value = ''; }, 5000);
  }
}
</script>