<template>
  <div v-if="visible" class="fixed bottom-24 left-1/2 -translate-x-1/2 z-4000 pointer-events-auto">
    <div class="glass-overlay px-4 py-3 rounded-xl flex items-center gap-3 shadow-lg border border-gray-200 dark:border-white/10">
      <span class="text-sm text-gray-900 dark:text-white">{{ message }}</span>
      <button @click="handleUndo" class="text-blue-500 dark:text-blue-400 font-medium text-sm hover:underline">Undo</button>
      <button @click="dismiss" class="text-gray-500 dark:text-white/50 hover:text-gray-900 dark:hover:text-white">&times;</button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
  undoAction: { type: Function, default: null },
  message: { type: String, default: '' },
  visible: { type: Boolean, default: false },
  timeout: { type: Number, default: 5000 },
});

const emit = defineEmits(['dismiss', 'undo']);

let timer = null;

watch(() => props.visible, (val) => {
  if (val) startTimer();
  else clearTimer();
});

function startTimer() {
  clearTimer();
  if (props.timeout > 0) {
    timer = setTimeout(() => { emit('dismiss'); }, props.timeout);
  }
}

function clearTimer() {
  if (timer) { clearTimeout(timer); timer = null; }
}

function handleUndo() {
  clearTimer();
  emit('undo');
  emit('dismiss');
}

function dismiss() {
  clearTimer();
  emit('dismiss');
}

onBeforeUnmount(() => clearTimer());
</script>