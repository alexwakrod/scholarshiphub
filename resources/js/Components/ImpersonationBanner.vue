<template>
  <div
    v-if="impersonating"
    class="sticky top-0 z-50 bg-yellow-400 dark:bg-yellow-500 text-black px-4 py-2 text-sm font-medium flex items-center justify-between"
  >
    <span>⚠ You are impersonating {{ impersonatedName }}. Actions are performed on their behalf.</span>
    <button
      @click="stopImpersonating"
      class="ml-4 px-3 py-1 rounded bg-black/20 hover:bg-black/30 transition-colors text-black"
    >
      Stop Impersonation
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();

const impersonating = computed(() => page.props.impersonating || false);
const impersonatedName = computed(() => page.props.impersonatedName || '');

async function stopImpersonating() {
  try {
    await axios.post('/admin/impersonate/stop');
    window.location.reload();
  } catch (e) {
    console.error('[ImpersonationBanner] stop error:', e);
  }
}
</script>