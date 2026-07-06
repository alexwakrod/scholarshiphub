<template>
  <Teleport to="body">
    <div class="fixed bottom-6 right-6 z-4000 flex flex-col gap-2 pointer-events-none">
      <TransitionGroup name="toast-slide">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="glass-toast pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg border backdrop-blur-md shadow-lg"
          :class="toastTypeClass(toast.type)"
          role="alert"
          @touchstart="onTouchStart($event, toast.id)"
          @touchmove="onTouchMove($event, toast.id)"
          @touchend="onTouchEnd($event, toast.id)"
        >
          <span v-if="toast.type === 'success'" class="text-green-500 text-lg">✔</span>
          <span v-else-if="toast.type === 'error'" class="text-red-500 text-lg">✖</span>
          <span v-else-if="toast.type === 'warning'" class="text-amber-500 text-lg">⚠</span>
          <span v-else-if="toast.type === 'info'" class="text-blue-500 text-lg">ℹ</span>
          <p class="text-sm flex-1 text-gray-900 dark:text-white">{{ toast.message }}</p>
          <button @click="dismiss(toast.id)" class="ml-2 text-gray-500 dark:text-white/50 hover:text-gray-900 dark:hover:text-white text-lg leading-none">&times;</button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, onBeforeUnmount } from 'vue';

const toasts = ref([]);
let toastId = 0;
const touchState = reactive({});

function toastTypeClass(type) {
  switch (type) {
    case 'success': return 'border-green-500/50 bg-green-100 dark:bg-green-500/10';
    case 'error': return 'border-red-500/50 bg-red-100 dark:bg-red-500/10';
    case 'warning': return 'border-amber-500/50 bg-amber-100 dark:bg-amber-500/10';
    default: return 'border-blue-500/50 bg-blue-100 dark:bg-blue-500/10';
  }
}

function addToast(message, type = 'info', duration = 5000) {
  const id = ++toastId;
  toasts.value.push({ id, message, type });
  if (toasts.value.length > 5) {
    toasts.value.shift();
  }
  if (duration) {
    setTimeout(() => dismiss(id), duration);
  }
}

function dismiss(id) {
  toasts.value = toasts.value.filter(t => t.id !== id);
}

let startX = 0, startY = 0, currentToastId = null;

function onTouchStart(event, id) {
  const touch = event.touches[0];
  startX = touch.clientX;
  startY = touch.clientY;
  currentToastId = id;
  touchState[id] = { translateX: 0 };
}

function onTouchMove(event, id) {
  if (currentToastId !== id) return;
  const touch = event.touches[0];
  const dx = touch.clientX - startX;
  const dy = touch.clientY - startY;
  if (Math.abs(dx) > Math.abs(dy)) {
    event.preventDefault();
    touchState[id].translateX = dx;
  }
}

function onTouchEnd(event, id) {
  if (currentToastId !== id) return;
  const dx = touchState[id]?.translateX || 0;
  if (Math.abs(dx) > 50) {
    dismiss(id);
  }
  delete touchState[id];
  currentToastId = null;
}

defineExpose({ addToast, dismiss });
</script>

<style scoped>
.toast-slide-enter-active, .toast-slide-leave-active {
  transition: all 0.3s ease;
}
.toast-slide-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.toast-slide-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>