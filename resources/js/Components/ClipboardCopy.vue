<template>
  <button
    @click="copy"
    class="inline-flex items-center gap-1 text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white transition-colors text-xs"
    :title="'Copy ' + (label || 'to clipboard')"
  >
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
    </svg>
    <span v-if="label">{{ label }}</span>
    <span v-if="copied" class="text-green-500 dark:text-green-400 ml-1">Copied!</span>
  </button>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  text: { type: String, required: true },
  label: { type: String, default: '' },
});

const copied = ref(false);

async function copy() {
  try {
    await navigator.clipboard.writeText(props.text);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
  } catch (e) {
    console.error('[ClipboardCopy] copy failed:', e);
  }
}
</script>