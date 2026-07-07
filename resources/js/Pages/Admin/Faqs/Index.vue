<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-[1200px] mx-auto space-y-6">
      <!-- Header Row -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          FAQ Management
        </h1>
        <!-- Add FAQ Button with Tooltip -->
        <div class="relative inline-flex">
          <button
            @click="openCreate"
            class="glass-btn-primary group relative w-10 h-10 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#3b82f6] flex items-center justify-center"
            ref="addBtnRef"
            @mouseenter="showTooltip('add', $event.currentTarget)"
            @mouseleave="hideTooltip"
          >
            <span class="relative z-10">
              <PlusIcon class="w-5 h-5" />
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
          </button>
          <GlassTooltip :visible="tooltipVisible && tooltipId === 'add'" :targetRef="tooltipTargetRef" :delay="0">
            <span class="text-xs text-white">Add FAQ</span>
          </GlassTooltip>
        </div>
      </div>

      <!-- Elevated Glass Table Container -->
      <div class="glass-elevated rounded-2xl border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] overflow-hidden">
        <!-- Sticky Header -->
        <div class="sticky top-0 z-10 glass-table-header px-4 py-3 border-b border-gray-200/30 dark:border-white/5">
          <div class="grid grid-cols-12 gap-4 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-white/40">
            <span class="col-span-1">Order</span>
            <span class="col-span-3">Question</span>
            <span class="col-span-4">Answer</span>
            <span class="col-span-2">Published</span>
            <span class="col-span-2 text-center">Actions</span>
          </div>
        </div>

        <!-- Table Rows with Staggered Entrance -->
        <div class="divide-y divide-gray-200/30 dark:divide-white/5">
          <div
            v-for="(faq, idx) in faqs"
            :key="faq.id"
            class="glass-table-row grid grid-cols-12 gap-4 px-4 py-3 items-center transition-all duration-300 hover:bg-white/5 dark:hover:bg-white/5"
            :style="{ '--i': idx }"
          >
            <span class="col-span-1 text-sm text-gray-500 dark:text-white/40 tabular-nums">{{ faq.order }}</span>
            <span class="col-span-3 text-sm font-medium text-gray-900 dark:text-white truncate">{{ faq.question }}</span>
            <span class="col-span-4 text-sm text-gray-600 dark:text-white/70 truncate">{{ faq.answer }}</span>
            <span class="col-span-2 flex justify-center">
              <span
                class="status-pill inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="faq.is_published
                  ? 'bg-green-500/10 text-green-400 border border-green-500/30 shadow-[0_0_10px_rgba(16,185,129,0.15)]'
                  : 'bg-gray-400/10 text-gray-400 border border-gray-400/30'"
              >
                {{ faq.is_published ? 'Yes' : 'No' }}
              </span>
            </span>
            <div class="col-span-2 flex items-center justify-center gap-1">
              <!-- Edit action -->
              <div class="relative inline-flex">
                <button
                  @click="openEdit(faq)"
                  class="glass-icon-btn group relative w-8 h-8 rounded-lg flex items-center justify-center text-indigo-400 hover:text-indigo-300 transition-colors"
                  @mouseenter="showTooltip(`edit-${faq.id}`, $event.currentTarget)"
                  @mouseleave="hideTooltip"
                >
                  <PencilSquareIcon class="w-4 h-4" />
                </button>
                <GlassTooltip :visible="tooltipVisible && tooltipId === `edit-${faq.id}`" :targetRef="tooltipTargetRef" :delay="0">
                  <span class="text-xs text-white">Edit</span>
                </GlassTooltip>
              </div>
              <!-- Delete action -->
              <div class="relative inline-flex">
                <button
                  @click="confirmDelete(faq.id)"
                  class="glass-icon-btn group relative w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:text-red-300 transition-colors ml-1 pl-1 border-l border-red-500/20"
                  @mouseenter="showTooltip(`delete-${faq.id}`, $event.currentTarget)"
                  @mouseleave="hideTooltip"
                >
                  <TrashIcon class="w-4 h-4" />
                </button>
                <GlassTooltip :visible="tooltipVisible && tooltipId === `delete-${faq.id}`" :targetRef="tooltipTargetRef" :delay="0">
                  <span class="text-xs text-white">Delete</span>
                </GlassTooltip>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="faqs.length === 0" class="text-center py-16">
          <div class="glass-empty-state rounded-xl p-8 inline-flex flex-col items-center gap-3">
            <svg class="w-10 h-10 text-gray-400 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm text-gray-500 dark:text-white/40">No FAQs yet.</span>
          </div>
        </div>
      </div>

      <!-- Create/Edit Modal (Ultra-Hero) -->
      <GlassModal v-model="modalOpen" @close="closeModal">
        <div class="space-y-5">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ editing ? 'Edit FAQ' : 'New FAQ' }}</h3>
          <GlassInput v-model="form.question" placeholder="Question" />
          <GlassTextarea v-model="form.answer" placeholder="Answer" rows="4" />
          <GlassNumberInput v-model="form.order" :min="0" placeholder="Order" />
          <div class="flex items-center gap-2">
            <GlassToggle v-model="form.is_published" label="Published" />
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button @click="closeModal" class="glass-btn-cancel group relative px-4 py-2 rounded-xl bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 text-gray-700 dark:text-white/70 text-sm font-medium hover:bg-white/20 dark:hover:bg-white/10 transition-all duration-300 active:scale-95">
              Cancel
            </button>
            <button @click="save" class="glass-btn-primary group relative px-6 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95">
              <span class="relative z-10">{{ editing ? 'Update' : 'Create' }}</span>
              <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
          </div>
        </div>
      </GlassModal>

      <!-- Delete Confirmation Modal (Ultra-Hero) -->
      <GlassModal v-model="deleteModalOpen" @close="deleteModalOpen = false">
        <div class="space-y-5">
          <h3 class="text-xl font-bold text-red-400">Delete FAQ?</h3>
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
import { ref, reactive } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassModal from '@/Components/GlassModal.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassTextarea from '@/Components/GlassTextarea.vue';
import GlassNumberInput from '@/Components/GlassNumberInput.vue';
import GlassToggle from '@/Components/GlassToggle.vue';
import GlassTooltip from '@/Components/GlassTooltip.vue';
import {
  PlusIcon,
  PencilSquareIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  faqs: Array,
});

const modalOpen = ref(false);
const editing = ref(null);
const deleteModalOpen = ref(false);
const deletingId = ref(null);

const form = reactive({
  question: '',
  answer: '',
  order: 0,
  is_published: true,
});

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

function openCreate() {
  editing.value = null;
  form.question = '';
  form.answer = '';
  form.order = 0;
  form.is_published = true;
  modalOpen.value = true;
}

function openEdit(faq) {
  editing.value = faq.id;
  form.question = faq.question;
  form.answer = faq.answer;
  form.order = faq.order;
  form.is_published = faq.is_published;
  modalOpen.value = true;
}

function closeModal() {
  modalOpen.value = false;
}

async function save() {
  try {
    if (editing.value) {
      await axios.put(`/admin/faqs/${editing.value}`, form);
    } else {
      await axios.post('/admin/faqs', form);
    }
    location.reload();
  } catch (e) {
    console.error('Save FAQ error:', e);
  }
}

function confirmDelete(id) {
  deletingId.value = id;
  deleteModalOpen.value = true;
}

async function executeDelete() {
  if (!deletingId.value) return;
  try {
    await axios.post(`/admin/faqs/${deletingId.value}`, { _method: 'DELETE' });
    location.reload();
  } catch (e) {
    console.error('Delete FAQ error:', e);
  }
}
</script>

<style scoped>
/* ============================================================
   GLASS FAQS INDEX – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* Elevated glass slab for table container */
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

/* Primary button */
.glass-btn-primary {
  transform: rotateY(-2deg);
  will-change: transform;
}
.glass-btn-primary:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.glass-btn-primary:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
}

/* Danger button */
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

/* Cancel button */
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

/* Icon buttons */
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
  .glass-btn-primary,
  .glass-btn-danger,
  .glass-btn-cancel,
  .glass-table-row,
  .glass-table-row:hover {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .glass-table-row,
  .glass-btn-primary,
  .glass-btn-danger,
  .glass-btn-cancel,
  .glass-icon-btn {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>