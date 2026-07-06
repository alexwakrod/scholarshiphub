<template>
  <div
    ref="containerRef"
    class="relative overflow-hidden"
    @touchstart="onTouchStart"
    @touchmove="onTouchMove"
    @touchend="onTouchEnd"
  >
    <!-- Refresh indicator -->
    <div
      class="absolute left-0 right-0 flex justify-center transition-transform"
      :style="{ transform: `translateY(${pullDistance - refreshThreshold}px)` }"
    >
      <div
        class="w-8 h-8 rounded-full border-2 border-blue-400 border-t-transparent animate-spin"
        :class="{ 'opacity-0': !refreshing }"
        v-if="refreshing"
      ></div>
      <svg
        v-else
        class="w-6 h-6 text-gray-500 dark:text-white/50 transition-transform"
        :class="{ 'rotate-180': pullDistance >= refreshThreshold }"
        fill="none" stroke="currentColor" viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
      </svg>
    </div>

    <!-- Content slot -->
    <div
      class="transition-transform"
      :style="{ transform: `translateY(${pullDistance}px)` }"
    >
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  onRefresh: { type: Function, required: true },
  refreshThreshold: { type: Number, default: 60 },
});

const containerRef = ref(null);
const pullDistance = ref(0);
const refreshing = ref(false);
let startY = 0;
let currentY = 0;
let pulling = false;

function onTouchStart(e) {
  if (refreshing.value) return;
  if (containerRef.value.scrollTop > 0) {
    pulling = false;
    return;
  }
  startY = e.touches[0].clientY;
  currentY = startY;
  pulling = true;
}

function onTouchMove(e) {
  if (!pulling || refreshing.value) return;
  currentY = e.touches[0].clientY;
  const diff = currentY - startY;
  if (diff > 0) {
    pullDistance.value = Math.min(diff * 0.5, props.refreshThreshold * 2);
    e.preventDefault();
  }
}

async function onTouchEnd() {
  if (!pulling || refreshing.value) return;
  pulling = false;
  if (pullDistance.value >= props.refreshThreshold) {
    refreshing.value = true;
    try {
      await props.onRefresh();
    } catch (e) {
      console.error('[PullToRefresh] refresh failed:', e);
    } finally {
      refreshing.value = false;
    }
  }
  pullDistance.value = 0;
}
</script>