<template>
  <AppLayout>
    <div class="relative min-h-screen overflow-hidden bg-gray-100 dark:bg-gray-950 p-4 md:p-6">
      <!-- Animated Background Particles -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 right-1/4 w-96 h-96 rounded-full bg-blue-600/5 dark:bg-blue-600/10 blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 left-1/4 w-[500px] h-[500px] rounded-full bg-indigo-600/5 dark:bg-indigo-600/10 blur-3xl animate-pulse-slow" style="animation-delay: 1s"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-purple-600/3 dark:bg-purple-600/5 blur-3xl"></div>
        <div class="absolute top-20 right-10 w-3 h-3 rounded-full bg-blue-400/20 dark:bg-blue-400/30 animate-float"></div>
        <div class="absolute bottom-40 right-1/4 w-4 h-4 rounded-full bg-indigo-400/20 dark:bg-indigo-400/30 animate-float" style="animation-delay: 0.7s"></div>
        <div class="absolute top-1/3 left-10 w-2 h-2 rounded-full bg-purple-400/20 dark:bg-purple-400/30 animate-float" style="animation-delay: 1.4s"></div>
        <div class="absolute bottom-20 left-1/3 w-3 h-3 rounded-full bg-pink-400/20 dark:bg-pink-400/30 animate-float" style="animation-delay: 2s"></div>
      </div>

      <!-- Glass Reflection Overlay -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-gray-400/30 dark:via-white/30 to-transparent"></div>
        <div class="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-gray-400/10 dark:via-white/10 to-transparent"></div>
        <div class="absolute top-1/4 -right-1/4 w-1/2 h-64 bg-gradient-to-l from-blue-500/5 dark:from-blue-500/5 to-transparent blur-3xl rotate-12"></div>
      </div>

      <!-- Page Content -->
      <div class="relative max-w-7xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
            My <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Bookmarks</span>
          </h1>
          <span class="text-xs text-gray-500 dark:text-white/40 bg-gray-200/60 dark:bg-white/5 px-2 py-0.5 rounded-full">
            {{ bookmarks.length }}
          </span>
        </div>

        <!-- Empty State (Diplomatic Glass Panel) -->
        <div v-if="bookmarks.length === 0" class="perspective-1000">
          <GlassCard
            variant="elevated"
            class="p-8 rounded-3xl transform -rotate-y-2 hover:rotate-y-0 hover:-translate-y-1 transition-all duration-500 text-center"
          >
            <EmptyState
              title="No bookmarks yet"
              description="Start exploring scholarships and bookmark your favorites."
            />
            <div class="mt-8 flex justify-center">
              <Link
                :href="route('scholarships.index')"
                class="group relative px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 inline-flex items-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="relative z-10">Explore Scholarships</span>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
              </Link>
            </div>
          </GlassCard>
        </div>

        <!-- Bookmark Cards Grid with Staggered Entrance -->
        <transition-group v-else name="staggered-list" appear tag="div" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="(b, index) in bookmarks"
            :key="b.id"
            class="perspective-1000"
            :style="{ transitionDelay: `${index * 80}ms` }"
          >
            <GlassCard
              variant="elevated"
              class="p-4 rounded-3xl transform -rotate-y-1 transition-all duration-500 hover:rotate-y-0 hover:-translate-y-1 hover:shadow-2xl h-full flex flex-col justify-between group"
            >
              <div class="relative z-10">
                <p class="text-gray-900 dark:text-white font-semibold truncate text-shadow-glass">
                  {{ b.title }}
                </p>
                <p class="text-xs text-gray-500 dark:text-white/70 mt-1">
                  {{ b.provider }} · {{ b.country }}
                </p>
                <p class="text-xs text-gray-400 dark:text-white/40 mt-1">
                  Deadline: {{ b.deadline }}
                </p>
                <p v-if="b.amount" class="text-sm text-amber-400 dark:text-amber-300 font-medium mt-2">
                  ${{ b.amount }}
                </p>
                <span
                  class="inline-block text-[10px] px-2.5 py-0.5 rounded-full font-medium mt-2 tracking-wider uppercase glass-badge"
                  :class="b.status === 'active'
                    ? 'glass-badge-active'
                    : 'glass-badge-inactive'"
                >
                  {{ b.status }}
                </span>
              </div>

              <div class="glass-divider my-3"></div>

              <div class="flex gap-2 actions-bar">
                <Link
                  :href="route('scholarships.show', b.id)"
                  class="flex-1 text-center px-3 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 group/btn"
                >
                  <span class="relative z-10">View</span>
                  <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover/btn:opacity-30 transition-opacity blur-xl"></div>
                </Link>
                <button
                  @click="removeBookmark(b.id)"
                  class="px-3 py-2 rounded-xl bg-red-600/20 dark:bg-red-500/10 border border-red-500/30 text-red-400 dark:text-red-300 text-sm font-medium transition-all duration-300 hover:bg-red-600/30 hover:shadow-[0_0_20px_rgba(239,68,68,0.3)] active:scale-95"
                  title="Remove bookmark"
                >
                  <span>Remove</span>
                </button>
              </div>
            </GlassCard>
          </div>
        </transition-group>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import EmptyState from '@/Components/EmptyState.vue';

const page = usePage();

const props = defineProps({
  bookmarks: Array,
});

// Local reactive copy of bookmarks
const bookmarks = ref(props.bookmarks || []);

// Keep in sync with prop updates
watch(() => props.bookmarks, (newVal) => {
  bookmarks.value = newVal || [];
});

async function removeBookmark(id) {
  try {
    await axios.post(`/bookmarks/${id}`);
    // Remove from local list immediately
    bookmarks.value = bookmarks.value.filter(b => b.id !== id);
    // Sync global bookmarkedIds for other pages
    if (page.props.bookmarkedIds) {
      const index = page.props.bookmarkedIds.indexOf(id);
      if (index > -1) page.props.bookmarkedIds.splice(index, 1);
    }
  } catch (e) {
    console.error('removeBookmark error', e);
  }
}
</script>

<style scoped>
/* Glass Depths */
.glass-surface {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4);
}
.dark .glass-surface {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.06);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.6);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

/* 3D Perspective */
.perspective-1000 { perspective: 1000px; }
.-rotate-y-1 { transform: rotateY(-1deg) rotateX(0.5deg); }
.-rotate-y-2 { transform: rotateY(-2deg) rotateX(1deg); }
.rotate-y-0 { transform: rotateY(0deg); }

/* Divider */
.glass-divider {
  height: 1px;
  background: linear-gradient(to right, transparent, rgba(0,0,0,0.1), transparent);
}
.dark .glass-divider {
  background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
}

/* Text shadow */
.text-shadow-glass {
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}
.dark .text-shadow-glass {
  text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

/* Status Badges */
.glass-badge {
  letter-spacing: 0.05em;
  backdrop-filter: blur(4px);
  border: 1px solid;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.glass-badge-active {
  background: rgba(16, 185, 129, 0.15);
  border-color: rgba(16, 185, 129, 0.3);
  color: #10b981;
  box-shadow: 0 0 12px rgba(16, 185, 129, 0.2);
}
.glass-badge-inactive {
  background: rgba(239, 68, 68, 0.15);
  border-color: rgba(239, 68, 68, 0.3);
  color: #ef4444;
  box-shadow: 0 0 12px rgba(239, 68, 68, 0.2);
}

/* Staggered list */
.staggered-list-enter-active { transition: all 0.4s ease; }
.staggered-list-leave-active { transition: all 0.3s ease; }
.staggered-list-enter-from { opacity: 0; transform: translateY(20px); }
.staggered-list-leave-to { opacity: 0; transform: translateY(-10px); }

@keyframes float {
  0%, 100% { transform: translateY(0px) scale(1); opacity: 0.3; }
  50% { transform: translateY(-20px) scale(1.2); opacity: 0.8; }
}
.animate-float { animation: float 6s ease-in-out infinite; }

@keyframes pulse-slow {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.1); }
}
.animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }

@media (max-width: 768px) {
  .perspective-1000 { perspective: none; }
  .-rotate-y-1, .-rotate-y-2 { transform: none !important; }
}

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { animation: none !important; transition: none !important; }
  .-rotate-y-1, .-rotate-y-2, .rotate-y-0 { transform: none !important; }
}
</style>