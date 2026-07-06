<template>
  <div v-if="offline" class="bg-yellow-400 dark:bg-yellow-500 text-black text-sm px-4 py-2 text-center font-medium" role="alert">
    You are offline. Last synced: {{ lastSynced ? new Date(lastSynced).toLocaleString() : 'never' }}
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const offline = ref(!navigator.onLine);
const lastSynced = ref(localStorage.getItem('last_synced') || null);

function handleOnline() {
  offline.value = false;
  const now = Date.now().toString();
  localStorage.setItem('last_synced', now);
  lastSynced.value = now;
}

function handleOffline() {
  offline.value = true;
}

onMounted(() => {
  window.addEventListener('online', handleOnline);
  window.addEventListener('offline', handleOffline);
  if (!offline.value) {
    const now = Date.now().toString();
    localStorage.setItem('last_synced', now);
    lastSynced.value = now;
  }
});

onBeforeUnmount(() => {
  window.removeEventListener('online', handleOnline);
  window.removeEventListener('offline', handleOffline);
});
</script>