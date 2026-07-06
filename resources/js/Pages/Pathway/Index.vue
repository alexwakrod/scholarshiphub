<template>
  <AppLayout>
    <div class="relative min-h-screen overflow-hidden bg-gray-100 dark:bg-gray-950 p-4 md:p-6">
      <!-- Animated Background Particles (Surface layer) -->
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
      <div class="relative max-w-5xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
            My <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Strategic Pathway</span>
          </h1>
        </div>

        <!-- Empty State (Diplomatic Glass Panel) -->
        <div v-if="!pathway" class="perspective-1000">
          <GlassCard
            variant="elevated"
            class="p-8 rounded-3xl transform -rotate-y-2 hover:rotate-y-0 hover:-translate-y-1 transition-all duration-500 text-center"
          >
            <div class="text-gray-500 dark:text-white/50 mb-6">
              <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-white/10 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              <p class="text-lg font-medium text-gray-900 dark:text-white">No pathway generated</p>
              <p class="text-sm text-gray-500 dark:text-white/40 mt-1">Generate a personalised strategic plan based on your profile and top scholarships.</p>
            </div>
            <button
              @click="generate"
              :disabled="generating"
              class="group relative px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-blue-400 inline-flex items-center gap-2"
            >
              <svg v-if="generating" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              <span class="relative z-10">{{ generating ? 'Generating...' : 'Generate Pathway' }}</span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl"></div>
            </button>
            <p v-if="errorMsg" class="mt-4 text-sm text-red-400 flex items-center justify-center gap-2 animate-shake">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ errorMsg }}
            </p>
            <p v-if="successMsg" class="mt-4 text-sm text-emerald-400 flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              {{ successMsg }}
            </p>
          </GlassCard>
        </div>

        <!-- Pathway display (Staggered Entrance) -->
        <transition-group v-else name="staggered-list" appear tag="div" class="space-y-6">
          <!-- Explanation card (Strategy) -->
          <GlassCard
            key="strategy"
            variant="elevated"
            class="p-6 md:p-8 rounded-3xl transform -rotate-y-1 transition-all duration-500 hover:rotate-y-0 hover:-translate-y-1 hover:shadow-2xl"
            :style="{ transitionDelay: '0ms' }"
          >
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2.5">
              <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              Overall Strategy
            </h2>
            <div class="glass-divider mb-4"></div>
            <p class="text-sm text-gray-700 dark:text-white/70 whitespace-pre-wrap leading-relaxed content-glass">
              {{ pathway.ai_explanation }}
            </p>
          </GlassCard>

          <!-- Timeline card (Milestones) -->
          <GlassCard
            key="milestones"
            variant="elevated"
            class="p-6 md:p-8 rounded-3xl transform -rotate-y-1 transition-all duration-500 hover:rotate-y-0 hover:-translate-y-1 hover:shadow-2xl"
            :style="{ transitionDelay: '100ms' }"
          >
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2.5">
              <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              Milestones
            </h2>
            <TimelineCanvas
              v-if="pathway.milestone_sequence && pathway.milestone_sequence.length"
              :milestones="milestones"
              @select="onSelectMilestone"
            />
            <div v-else class="text-gray-500 dark:text-white/40 text-sm py-4 text-center">No milestones found.</div>
          </GlassCard>

          <!-- Selected Milestone Detail card -->
          <GlassCard
            v-if="selectedMilestone"
            key="detail"
            variant="elevated"
            class="p-6 md:p-8 rounded-3xl transform -rotate-y-1 transition-all duration-500 hover:rotate-y-0 hover:-translate-y-1 hover:shadow-2xl"
            :style="{ transitionDelay: '200ms' }"
          >
            <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2.5">
              <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ selectedMilestone.label }}
            </h3>
            <div class="glass-divider mb-4"></div>
            <div class="text-sm text-gray-700 dark:text-white/70 space-y-2">
              <p class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400 dark:text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-gray-500 dark:text-white/40">Date:</span> {{ selectedMilestone.date }}
              </p>
              <p v-if="selectedMilestone.score !== undefined" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400 dark:text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <span class="text-gray-500 dark:text-white/40">Score impact:</span> {{ selectedMilestone.score }}
              </p>
            </div>
          </GlassCard>

          <!-- Regenerate button (secondary action) -->
          <div key="regenerate" class="text-center" :style="{ transitionDelay: '300ms' }">
            <button
              @click="generate"
              :disabled="generating"
              class="group relative px-6 py-3 rounded-xl bg-white/10 dark:bg-white/5 border border-gray-300 dark:border-white/10 text-gray-700 dark:text-white/70 text-sm font-medium transition-all duration-300 hover:bg-white/20 dark:hover:bg-white/10 hover:shadow-lg active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2"
            >
              <svg v-if="generating" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span>{{ generating ? 'Regenerating...' : 'Regenerate Pathway' }}</span>
            </button>
            <p v-if="errorMsg" class="mt-4 text-sm text-red-400 flex items-center justify-center gap-2 animate-shake">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ errorMsg }}
            </p>
            <p v-if="successMsg" class="mt-4 text-sm text-emerald-400 flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              {{ successMsg }}
            </p>
          </div>
        </transition-group>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import TimelineCanvas from '@/Components/TimelineCanvas.vue';

const page = usePage();
const props = defineProps({
  pathway: Object,
});

const generating = ref(false);
const errorMsg = ref('');
const successMsg = ref(page.props.flash?.success || '');
const selectedMilestone = ref(null);

const milestones = props.pathway?.milestone_sequence
  ? props.pathway.milestone_sequence.map((m, idx) => ({
      id: m.id || `milestone-${idx}`,
      label: m.label,
      date: m.date,
      score: m.score,
    }))
  : [];

async function generate() {
  generating.value = true;
  errorMsg.value = '';
  successMsg.value = '';
  try {
    await axios.post('/pathway/generate');
    location.reload();
  } catch (err) {
    console.error('Pathway generation error:', err);
    const msg = err.response?.data?.message || 'Failed to generate pathway.';
    errorMsg.value = msg;
  } finally {
    generating.value = false;
  }
}

function onSelectMilestone(milestoneId) {
  const found = milestones.find(m => m.id === milestoneId);
  if (found) {
    selectedMilestone.value = found;
  }
}
</script>

<style scoped>
/* ================================================================
   PREMIUM GLASS PATHWAY – THEME‑AWARE & BLUEPRINT COMPLIANT
   ================================================================ */

/* Z‑Plane Glass Depths */
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

/* Glass Divider */
.glass-divider {
  height: 1px;
  background: linear-gradient(to right, transparent, rgba(0,0,0,0.1), transparent);
}
.dark .glass-divider {
  background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
}

/* Content Glass (reduced blur for readability) */
.content-glass {
  backdrop-filter: blur(4px);
}

/* Shake animation for errors */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}
.animate-shake {
  animation: shake 0.3s ease-in-out;
}

/* Staggered list entrance */
.staggered-list-enter-active {
  transition: all 0.4s ease;
}
.staggered-list-leave-active {
  transition: all 0.3s ease;
}
.staggered-list-enter-from {
  opacity: 0;
  transform: translateY(20px);
}
.staggered-list-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Background animations */
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

/* Mobile and reduced motion overrides */
@media (max-width: 768px) {
  .perspective-1000 { perspective: none; }
  .-rotate-y-1, .-rotate-y-2 { transform: none !important; }
}

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation: none !important;
    transition: none !important;
  }
  .-rotate-y-1, .-rotate-y-2, .rotate-y-0 { transform: none !important; }
}
</style>