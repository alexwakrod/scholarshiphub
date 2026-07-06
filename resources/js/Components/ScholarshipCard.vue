<template>
  <div class="perspective-1000 transform-style-3d">
    <div
      class="group relative rounded-2xl transition-all duration-500 will-change-transform"
      :class="[viewMode === 'grid' ? 'p-4' : 'p-3', 'glass-card-3d']"
    >
      <div class="absolute -inset-0.5 rounded-2xl bg-gradient-to-r from-blue-500/20 via-indigo-500/20 to-purple-500/20 opacity-0 group-hover:opacity-100 group-hover:blur-2xl transition-all duration-500 pointer-events-none" />

      <div class="relative">
        <!-- Main content – click navigates to scholarship -->
        <div @click="goToScholarship" class="cursor-pointer">
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold line-clamp-2 text-sm md:text-base transition-colors duration-300 text-gray-900 dark:text-white group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:from-blue-400 group-hover:to-indigo-400 group-hover:bg-clip-text">
                {{ scholarship.title }}
              </h3>
              <p class="text-xs text-gray-500 dark:text-white/30 mt-0.5 flex items-center gap-2">
                <span>{{ scholarship.provider }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-400 dark:bg-white/20"></span>
                <span>{{ scholarship.country }}</span>
              </p>
            </div>

            <div v-if="matchScore !== undefined && matchScore !== null" class="flex-shrink-0 transition-transform duration-300 group-hover:scale-105">
              <div class="relative w-12 h-12">
                <svg class="w-12 h-12 -rotate-90" viewBox="0 0 48 48">
                  <circle cx="24" cy="24" r="20" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="4" class="dark:stroke-white/5" />
                  <circle cx="24" cy="24" r="20" fill="none" :stroke="matchColor" stroke-width="4" stroke-linecap="round"
                    :stroke-dasharray="`${matchScore * 1.256} 125.6`" class="transition-all duration-700 ease-out" />
                </svg>
                <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-900 dark:text-white">{{ matchScore }}%</span>
              </div>
            </div>
          </div>

          <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="text-[10px] px-2 py-0.5 rounded-full glass-tag">{{ scholarship.category || 'General' }}</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full glass-tag">{{ scholarship.amount ? '$' + scholarship.amount : 'Varies' }}</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full backdrop-blur-sm border"
              :class="isDeadlineUrgent
                ? 'bg-red-500/10 border-red-500/30 text-red-400'
                : 'bg-white/10 dark:bg-white/5 border-gray-300 dark:border-white/10 text-gray-600 dark:text-white/50'">
              {{ formatDeadline(scholarship.deadline) }}
            </span>
          </div>

          <div class="relative z-10 mt-3 pt-3 border-t border-gray-200 dark:border-white/5 flex items-center">
            <span class="text-xs text-gray-500 dark:text-white/30 group-hover:text-blue-400 transition-colors flex items-center gap-1">
              <span>View Details</span>
              <svg class="w-3 h-3 group-hover:translate-x-1 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </span>
          </div>
        </div>

        <!-- Bookmark button – absolutely isolated -->
        <button
          data-bookmark-toggle
          @mousedown.prevent.stop
          @click.prevent.stop="toggleBookmark"
          class="absolute top-3 right-3 z-30 p-1.5 rounded-lg transition-all duration-300 hover:scale-110 active:scale-90"
          :class="bookmarked ? 'text-yellow-400' : 'text-gray-500 dark:text-white/20 hover:text-gray-900 dark:hover:text-white/40'"
        >
          <svg class="w-4 h-4" :fill="bookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  scholarship: { type: Object, required: true },
  bookmarked: { type: Boolean, default: false },
  matchScore: { type: Number, default: null },
  viewMode: { type: String, default: 'grid' },
});

const emit = defineEmits(['toggle-bookmark']);

const matchColor = computed(() => {
  if (props.matchScore >= 80) return '#10B981';
  if (props.matchScore >= 60) return '#F59E0B';
  return '#EF4444';
});

const isDeadlineUrgent = computed(() => {
  if (!props.scholarship.deadline) return false;
  const days = getDaysUntil(props.scholarship.deadline);
  return days <= 7 && days >= 0;
});

function goToScholarship() {
  router.visit(route('scholarships.show', props.scholarship.id));
}

function toggleBookmark() {
  emit('toggle-bookmark');
}

function formatDeadline(dateStr) {
  try {
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return dateStr;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  } catch { return dateStr; }
}

function getDaysUntil(dateStr) {
  try {
    const target = new Date(dateStr);
    const now = new Date();
    return Math.ceil((target - now) / (1000 * 60 * 60 * 24));
  } catch { return Infinity; }
}
</script>

<style scoped>
/* ============================================
   3D PHYSICAL GLASS TILE – THEME‑AWARE
   ============================================ */
.perspective-1000 { perspective: 1000px; }
.transform-style-3d { transform-style: preserve-3d; }
.will-change-transform { will-change: transform; }

.glass-card-3d {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.4);
  transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1),
              box-shadow 0.5s ease,
              background 0.3s ease;
}
.dark .glass-card-3d {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.glass-card-3d:hover {
  transform: translateY(-4px) rotateY(2deg) rotateX(1deg) scale(1.02);
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.5);
  background: rgba(255, 255, 255, 0.4);
}
.dark .glass-card-3d:hover {
  background: rgba(255, 255, 255, 0.1);
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

.glass-tag {
  background: rgba(0, 0, 0, 0.03);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  color: #4b5563;
}
.dark .glass-tag {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.6);
}

/* Mobile: disable 3D transforms and motion */
@media (max-width: 768px) {
  .perspective-1000 { perspective: none !important; }
  .glass-card-3d { transform: none !important; }
  .glass-card-3d:hover { transform: none !important; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.3); }
}

@media (prefers-reduced-motion: reduce) {
  .glass-card-3d, .glass-card-3d:hover { transition: none !important; transform: none !important; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.4); }
  .perspective-1000 { perspective: none !important; }
}
</style>