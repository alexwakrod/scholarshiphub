<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-2xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          Feature Flags
        </h1>
      </div>

      <!-- Elevated Glass Card -->
      <div class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)]">
        <div class="space-y-1 divide-y divide-gray-200/30 dark:divide-white/5">
          <div
            v-for="(enabled, feature) in localFlags"
            :key="feature"
            class="glass-feature-row flex items-center justify-between p-4 rounded-xl transition-all duration-300 hover:bg-white/5 dark:hover:bg-white/5 stagger-item"
            :style="{ '--i': Object.keys(localFlags).indexOf(feature) }"
          >
            <div>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatFeatureName(feature) }}</p>
              <p class="text-xs text-gray-500 dark:text-white/40 mt-0.5 font-mono tracking-wide">{{ feature }}</p>
            </div>
            <div class="flex items-center gap-3">
              <GlassToggle
                :model-value="enabled"
                @update:model-value="(val) => toggleFlag(feature, val)"
              />
            </div>
          </div>
        </div>

        <!-- Reset Button with Tooltip -->
        <div class="mt-6 flex justify-end">
          <div class="relative inline-flex">
            <button
              @click="resetDefaults"
              class="glass-btn-reset group relative w-10 h-10 rounded-xl bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 text-gray-500 dark:text-white/40 hover:text-gray-900 dark:hover:text-white overflow-hidden transition-all duration-300 hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#3b82f6] flex items-center justify-center"
              ref="resetBtnRef"
              @mouseenter="showTooltip('reset', $event.currentTarget)"
              @mouseleave="hideTooltip"
            >
              <span class="relative z-10">
                <ArrowPathIcon class="w-5 h-5" />
              </span>
              <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity blur-sm"></div>
            </button>
            <GlassTooltip :visible="tooltipVisible && tooltipId === 'reset'" :targetRef="tooltipTargetRef" :delay="0">
              <span class="text-xs text-white">Reset to defaults</span>
            </GlassTooltip>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassToggle from '@/Components/GlassToggle.vue';
import GlassTooltip from '@/Components/GlassTooltip.vue';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  flags: { type: Object, required: true },
});

const localFlags = ref({ ...props.flags });

// Tooltip state
const tooltipVisible = ref(false);
const tooltipId = ref(null);
const tooltipTargetRef = ref(null);

function showTooltip(id, target) {
  tooltipId.value = id;
  tooltipTargetRef.value = target;
  tooltipVisible.value = true;
}
function hideTooltip() {
  tooltipVisible.value = false;
  tooltipTargetRef.value = null;
}

async function toggleFlag(feature, enabled) {
  // Optimistic update
  localFlags.value[feature] = enabled;

  try {
    const response = await axios.post('/admin/feature-flags/toggle', {
      feature,
      enabled,
    });
    // Sync with server response if needed
    if (response.data.flags) {
      localFlags.value = { ...response.data.flags };
    }
  } catch (error) {
    console.error('Toggle feature flag failed:', error);
    // Revert on error
    localFlags.value[feature] = !enabled;
  }
}

async function resetDefaults() {
  // Reset all to true (or fetch defaults from API). We'll toggle each to true.
  const updated = {};
  Object.keys(localFlags.value).forEach(k => {
    updated[k] = true;
  });
  localFlags.value = updated;
  try {
    await Promise.all(
      Object.entries(updated).map(([feature, enabled]) =>
        axios.post('/admin/feature-flags/toggle', { feature, enabled })
      )
    );
    // Refresh page to ensure Inertia shared props update? The shared props are read from cache on each request,
    // but the admin page may not refresh automatically. We'll trigger a reload.
    location.reload();
  } catch (error) {
    console.error('Reset feature flags failed:', error);
  }
}

function formatFeatureName(key) {
  return key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}
</script>

<style scoped>
/* ============================================================
   GLASS FEATURE FLAGS – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* Elevated glass slab for main container */
.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.05);
}

/* Feature row styling with stagger entrance */
.glass-feature-row {
  opacity: 0;
  animation: row-fade-in 0.35s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 50ms);
  transform: rotateY(0.5deg) rotateX(0.2deg);
  transition: background 0.2s ease, transform 0.2s ease;
}
.glass-feature-row:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-1px) scale(1.005);
  background: rgba(255, 255, 255, 0.08);
  z-index: 5;
}
.dark .glass-feature-row:hover {
  background: rgba(255, 255, 255, 0.04);
}

@keyframes row-fade-in {
  0% { opacity: 0; transform: rotateY(0.5deg) rotateX(0.2deg) translateY(8px); }
  100% { opacity: 1; transform: rotateY(0.5deg) rotateX(0.2deg) translateY(0); }
}

/* Reset button */
.glass-btn-reset {
  transform: rotateY(-1deg);
  will-change: transform;
}
.glass-btn-reset:hover {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.glass-btn-reset:active {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
}

/* Localized glow (unused for this small button but kept for consistency) */
.localized-glow {
  filter: blur(20px);
  opacity: 0;
  transition: opacity 0.4s ease;
}
.group:hover .localized-glow {
  opacity: 0.3;
}

/* Divider styling (already via Tailwind divide-y) */

/* Text readability */
.text-gray-900, .text-white {
  text-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.dark .text-gray-900,
.dark .text-white {
  text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

/* Mobile & accessibility overrides */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-feature-row,
  .glass-feature-row:hover,
  .glass-btn-reset,
  .glass-btn-reset:hover {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .glass-feature-row,
  .glass-btn-reset {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>