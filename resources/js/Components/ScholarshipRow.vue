<template>
  <div class="perspective-1000 transform-style-3d">
    <div
      class="group relative flex items-center justify-between p-4 rounded-xl cursor-pointer transition-all duration-500 will-change-transform glass-row-3d"
      @click="$inertia.visit(route('scholarships.show', scholarship.id))"
    >
      <!-- Hover glow overlay -->
      <div class="absolute -inset-0.5 rounded-xl bg-gradient-to-r from-blue-500/20 to-indigo-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 blur-xl pointer-events-none" />

      <div class="relative z-10 flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <h3 class="text-gray-900 dark:text-white font-medium truncate transition-all duration-300 group-hover:bg-gradient-to-r group-hover:from-blue-400 group-hover:to-indigo-400 group-hover:bg-clip-text group-hover:text-transparent">
            {{ scholarship.title }}
          </h3>
          <button
            @click.stop="toggleBookmark"
            class="p-1 rounded-lg transition-all duration-200 hover:scale-110 active:scale-90"
            :class="bookmarked
              ? 'bg-yellow-500/10 border border-yellow-500/20 text-yellow-400'
              : 'text-gray-400 dark:text-white/60 hover:text-gray-900 dark:hover:text-white hover:bg-white/10 dark:hover:bg-white/5'"
            :title="bookmarked ? 'Remove bookmark' : 'Add bookmark'"
          >
            <svg class="w-4 h-4" :fill="bookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
            </svg>
          </button>
        </div>
        <p class="text-xs text-gray-500 dark:text-white/50 mt-0.5">
          {{ scholarship.provider }} · {{ scholarship.country }} · Deadline {{ scholarship.deadline }}
        </p>
      </div>

      <div class="relative z-10 flex items-center gap-4 ml-4">
        <p
          v-if="scholarship.amount"
          class="text-sm font-bold px-2 py-0.5 rounded-md bg-white/5 dark:bg-white/5 backdrop-blur-sm border border-white/10 dark:border-white/5 bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent"
        >
          ${{ scholarship.amount }}
        </p>
        <div
          v-if="matchScore != null"
          class="transition-transform duration-300 group-hover:scale-105"
        >
          <MatchGauge :score="matchScore" :size="36" />
        </div>
        <span v-else class="text-xs text-gray-400 dark:text-white/30">—</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import MatchGauge from '@/Components/MatchGauge.vue';

const props = defineProps({
  scholarship: { type: Object, required: true },
  bookmarked: { type: Boolean, default: false },
  matchScore: { type: Number, default: null },
});

const emit = defineEmits(['toggle-bookmark']);

function toggleBookmark() {
  emit('toggle-bookmark', props.scholarship.id);
}
</script>

<style scoped>
/* ============================================
   3D PHYSICAL GLASS ROW (LIST VIEW) – THEME-AWARE
   ============================================ */

.perspective-1000 {
  perspective: 1000px;
}

.transform-style-3d {
  transform-style: preserve-3d;
}

.will-change-transform {
  will-change: transform;
}

/* Elevated glass slab */
.glass-row-3d {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.5);
  transform: rotateY(2deg) rotateX(1deg);
}
.dark .glass-row-3d {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.glass-row-3d:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-2px) scale(1.01);
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.4);
}
.dark .glass-row-3d:hover {
  background: rgba(255, 255, 255, 0.1);
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

/* Mobile: disable 3D transforms */
@media (max-width: 768px) {
  .perspective-1000 {
    perspective: none !important;
  }
  .glass-row-3d {
    transform: none !important;
  }
  .glass-row-3d:hover {
    transform: none !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.3);
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .glass-row-3d,
  .glass-row-3d:hover {
    transition: none !important;
    transform: none !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.5);
  }
  .perspective-1000 {
    perspective: none !important;
  }
}
</style>