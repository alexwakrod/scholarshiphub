<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-[1200px] mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          Testimonial Management
        </h1>
      </div>

      <!-- Elevated Glass Table Container -->
      <div class="glass-elevated rounded-2xl border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] overflow-hidden">
        <!-- Sticky Header -->
        <div class="sticky top-0 z-10 glass-table-header px-4 py-3 border-b border-gray-200/30 dark:border-white/5">
          <div class="grid grid-cols-12 gap-4 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-white/40">
            <span class="col-span-2">Name</span>
            <span class="col-span-4">Quote</span>
            <span class="col-span-2">Grade</span>
            <span class="col-span-2">Approved</span>
            <span class="col-span-2 text-center">Actions</span>
          </div>
        </div>

        <!-- Table Rows with Staggered Entrance -->
        <div class="divide-y divide-gray-200/30 dark:divide-white/5">
          <div
            v-for="(t, idx) in testimonials"
            :key="t.id"
            class="glass-table-row grid grid-cols-12 gap-4 px-4 py-3 items-center transition-all duration-300 hover:bg-white/5 dark:hover:bg-white/5"
            :style="{ '--i': idx }"
          >
            <span class="col-span-2 text-sm font-medium text-gray-900 dark:text-white truncate">{{ t.name_display }}</span>
            <span class="col-span-4 text-sm text-gray-600 dark:text-white/70 truncate max-w-[300px]">{{ t.quote }}</span>
            <span class="col-span-2 text-sm text-gray-500 dark:text-white/40">{{ t.grade || '—' }}</span>
            <span class="col-span-2 flex justify-center">
              <span
                class="status-pill inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="t.is_approved
                  ? 'bg-green-500/10 text-green-400 border border-green-500/30 shadow-[0_0_10px_rgba(16,185,129,0.15)]'
                  : 'bg-amber-500/10 text-amber-400 border border-amber-500/30 shadow-[0_0_10px_rgba(245,158,11,0.15)]'"
              >
                {{ t.is_approved ? 'Approved' : 'Pending' }}
              </span>
            </span>
            <div class="col-span-2 flex items-center justify-center gap-1">
              <!-- Approve action (only if not approved) -->
              <div v-if="!t.is_approved" class="relative inline-flex">
                <button
                  @click="approve(t.id)"
                  class="glass-icon-btn group relative w-8 h-8 rounded-lg flex items-center justify-center text-green-400 hover:text-green-300 transition-colors"
                  @mouseenter="showTooltip(`approve-${t.id}`, $event.currentTarget)"
                  @mouseleave="hideTooltip"
                >
                  <CheckBadgeIcon class="w-4 h-4" />
                </button>
                <GlassTooltip :visible="tooltipVisible && tooltipId === `approve-${t.id}`" :targetRef="tooltipTargetRef" :delay="0">
                  <span class="text-xs text-white">Approve</span>
                </GlassTooltip>
              </div>
              <!-- Delete action -->
              <div class="relative inline-flex">
                <button
                  @click="confirmDelete(t.id)"
                  class="glass-icon-btn group relative w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:text-red-300 transition-colors ml-1 pl-1 border-l border-red-500/20"
                  @mouseenter="showTooltip(`delete-${t.id}`, $event.currentTarget)"
                  @mouseleave="hideTooltip"
                >
                  <TrashIcon class="w-4 h-4" />
                </button>
                <GlassTooltip :visible="tooltipVisible && tooltipId === `delete-${t.id}`" :targetRef="tooltipTargetRef" :delay="0">
                  <span class="text-xs text-white">Delete</span>
                </GlassTooltip>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="testimonials.length === 0" class="text-center py-16">
          <div class="glass-empty-state rounded-xl p-8 inline-flex flex-col items-center gap-3">
            <ChatBubbleLeftRightIcon class="w-10 h-10 text-gray-400 dark:text-white/20" />
            <span class="text-sm text-gray-500 dark:text-white/40">No testimonials yet.</span>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal (Ultra-Hero) -->
      <GlassModal v-model="deleteModalOpen" @close="deleteModalOpen = false">
        <div class="space-y-5">
          <h3 class="text-xl font-bold text-red-400">Delete Testimonial?</h3>
          <p class="text-sm text-gray-600 dark:text-white/60">This action cannot be undone.</p>
          <div class="flex justify-end gap-3 pt-2">
            <button @click="deleteModalOpen = false" class="glass-btn-cancel group relative px-4 py-2 rounded-xl bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 text-gray-700 dark:text-white/70 text-sm font-medium hover:bg-white/20 dark:hover:bg-white/10 transition-all duration-300 active:scale-95">
              Cancel
            </button>
            <button @click="executeDelete" class="glass-btn-danger group relative px-6 py-2 rounded-xl bg-red-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(239,68,68,0.4)] hover:-translate-y-0.5 active:scale-95">
              <span class="relative z-10">Delete</span>
              <div class="absolute inset-0 bg-red-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
          </div>
        </div>
      </GlassModal>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassModal from '@/Components/GlassModal.vue';
import GlassTooltip from '@/Components/GlassTooltip.vue';
import {
  CheckBadgeIcon,
  TrashIcon,
  ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  testimonials: Array,
});

const deleteModalOpen = ref(false);
const deletingId = ref(null);

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

async function approve(id) {
  try {
    await axios.post(`/admin/testimonials/${id}/approve`);
    location.reload();
  } catch (e) {
    console.error('Approve error:', e);
  }
}

function confirmDelete(id) {
  deletingId.value = id;
  deleteModalOpen.value = true;
}

async function executeDelete() {
  if (!deletingId.value) return;
  try {
    await axios.post(`/admin/testimonials/${deletingId.value}`, { _method: 'DELETE' });
    location.reload();
  } catch (e) {
    console.error('Delete error:', e);
  }
}
</script>

<style scoped>
/* ============================================================
   GLASS TESTIMONIALS INDEX – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* Elevated glass slab */
.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.05);
}

/* Sticky table header */
.glass-table-header {
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}
.dark .glass-table-header {
  background: rgba(0, 0, 0, 0.5);
}

/* Row styling with stagger entrance */
.glass-table-row {
  opacity: 0;
  animation: row-fade-in 0.35s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 30ms);
  transform: rotateY(0.5deg) rotateX(0.2deg);
  transition: background 0.2s ease, transform 0.2s ease;
}
.glass-table-row:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-1px) scale(1.005);
  background: rgba(255, 255, 255, 0.08);
  z-index: 5;
}
.dark .glass-table-row:hover {
  background: rgba(255, 255, 255, 0.04);
}

@keyframes row-fade-in {
  0% { opacity: 0; transform: rotateY(0.5deg) rotateX(0.2deg) translateY(8px); }
  100% { opacity: 1; transform: rotateY(0.5deg) rotateX(0.2deg) translateY(0); }
}

/* Status pills */
.status-pill {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  transition: all 0.2s ease;
}
.status-pill:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Glass icon buttons */
.glass-icon-btn {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid transparent;
  transition: all 0.2s ease;
}
.glass-icon-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.15);
  transform: translateY(-1px);
}
.dark .glass-icon-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
}

/* Buttons */
.glass-btn-danger {
  transform: rotateY(-2deg);
  will-change: transform;
}
.glass-btn-danger:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.glass-btn-danger:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
}

.glass-btn-cancel {
  transform: rotateY(-1deg);
  will-change: transform;
}
.glass-btn-cancel:hover {
  transform: rotateY(0deg) translateY(-1px) scale(1.01);
}

/* Localized glow */
.localized-glow {
  filter: blur(20px);
  opacity: 0;
  transition: opacity 0.4s ease;
}
.group:hover .localized-glow {
  opacity: 0.3;
}

/* Empty state */
.glass-empty-state {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.dark .glass-empty-state {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.06);
}

/* Mobile & accessibility overrides */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-table-row,
  .glass-table-row:hover,
  .glass-btn-danger,
  .glass-btn-cancel,
  .glass-icon-btn,
  .glass-btn-danger:hover,
  .glass-btn-cancel:hover,
  .glass-icon-btn:hover {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .glass-table-row,
  .glass-btn-danger,
  .glass-btn-cancel,
  .glass-icon-btn {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>