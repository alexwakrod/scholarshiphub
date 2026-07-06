<template>
  <Teleport to="body">
    <div
      v-if="visible"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 dark:bg-black/90 backdrop-blur-sm"
      @click.self="close"
      @keydown.escape="close"
      @touchstart.prevent
      @touchmove.prevent
    >
      <button @click="close" class="absolute top-4 right-4 z-10 text-white/60 hover:text-white text-3xl">&times;</button>
      <img
        ref="imageRef"
        :src="src"
        class="max-w-full max-h-full object-contain cursor-grab"
        :style="imageStyle"
        @load="onImageLoad"
        @mousedown.stop="onMouseDown"
        @mousemove.stop="onMouseMove"
        @mouseup="onMouseUp"
        @mouseleave="onMouseUp"
        @touchstart.stop.prevent="onTouchStart"
        @touchmove.stop.prevent="onTouchMove"
        @touchend="onTouchEnd"
        @dblclick="toggleZoom"
      />
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  src: { type: String, required: true },
  visible: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const imageRef = ref(null);
const scale = ref(1);
const translateX = ref(0);
const translateY = ref(0);
const dragging = ref(false);
let startX = 0, startY = 0, startTransX = 0, startTransY = 0;
let lastPinchDist = 0;

const imageStyle = computed(() => ({
  transform: `translate(${translateX.value}px, ${translateY.value}px) scale(${scale.value})`,
  transition: dragging.value ? 'none' : 'transform 0.2s',
}));

function close() { emit('close'); resetTransform(); }
function resetTransform() { scale.value = 1; translateX.value = 0; translateY.value = 0; }
function toggleZoom() { if (scale.value > 1) resetTransform(); else scale.value = 2; }
function onImageLoad() { resetTransform(); }

function onMouseDown(e) { e.preventDefault(); dragging.value = true; startX = e.clientX; startY = e.clientY; startTransX = translateX.value; startTransY = translateY.value; }
function onMouseMove(e) { if (!dragging.value) return; const dx = e.clientX - startX; const dy = e.clientY - startY; translateX.value = startTransX + dx; translateY.value = startTransY + dy; }
function onMouseUp() { dragging.value = false; }

function onTouchStart(e) {
  if (e.touches.length === 1) {
    dragging.value = true; startX = e.touches[0].clientX; startY = e.touches[0].clientY; startTransX = translateX.value; startTransY = translateY.value;
  } else if (e.touches.length === 2) {
    lastPinchDist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
    dragging.value = false;
  }
}

function onTouchMove(e) {
  if (e.touches.length === 1 && dragging.value) {
    const dx = e.touches[0].clientX - startX; const dy = e.touches[0].clientY - startY; translateX.value = startTransX + dx; translateY.value = startTransY + dy;
  } else if (e.touches.length === 2) {
    const dist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
    const delta = dist / (lastPinchDist || 1); scale.value = Math.max(0.5, Math.min(5, scale.value * delta)); lastPinchDist = dist;
  }
}

function onTouchEnd() { dragging.value = false; }
</script>