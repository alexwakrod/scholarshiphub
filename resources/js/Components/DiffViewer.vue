<template>
  <div class="diff-viewer font-mono text-sm border border-white/10 rounded-xl overflow-hidden">
    <div v-for="(chunk, idx) in chunks" :key="idx" class="px-3 py-0.5" :class="chunkClass(chunk.type)">
      <span class="mr-2 select-none text-xs opacity-60">{{ chunkSymbol(chunk.type) }}</span>
      <span>{{ chunk.value }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { computeDiff } from '@/utils/diff.js';

const props = defineProps({
  oldText: { type: String, default: '' },
  newText: { type: String, default: '' },
});

const chunks = computed(() => computeDiff(props.oldText, props.newText));

function chunkClass(type) {
  switch (type) {
    case 'added': return 'bg-green-500/10 text-green-300';
    case 'removed': return 'bg-red-500/10 text-red-300';
    default: return 'text-white/70';
  }
}

function chunkSymbol(type) {
  switch (type) {
    case 'added': return '+';
    case 'removed': return '-';
    default: return ' ';
  }
}
</script>

<style scoped>
.diff-viewer {
  max-height: 600px;
  overflow-y: auto;
}
</style>