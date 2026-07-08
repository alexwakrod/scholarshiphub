<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-[1440px] mx-auto space-y-6">
      <!-- Header with Batch Actions -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          File Manager
        </h1>
        <div class="flex items-center gap-3">
          <div class="relative inline-flex" v-if="selectedIds.length">
            <button
              @click="batchDownload"
              class="glass-btn-download group relative w-10 h-10 rounded-xl bg-indigo-600 text-white overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(99,102,241,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-indigo-400 flex items-center justify-center"
              ref="downloadBtnRef"
              @mouseenter="showTooltip('download', $event.currentTarget)"
              @mouseleave="hideTooltip"
            >
              <span class="relative z-10"><ArrowDownTrayIcon class="w-5 h-5" /></span>
              <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-purple-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
            <GlassTooltip :visible="tooltipVisible && tooltipId === 'download'" :targetRef="tooltipTargetRef" :delay="0">
              <span class="text-xs text-white">Download Selected (ZIP)</span>
            </GlassTooltip>
          </div>
          <div class="relative inline-flex" v-if="selectedIds.length">
            <button
              @click="confirmBatchDelete"
              class="glass-btn-danger group relative w-10 h-10 rounded-xl bg-red-600 text-white overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(239,68,68,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-red-400 flex items-center justify-center"
              ref="deleteBtnRef"
              @mouseenter="showTooltip('deleteSelected', $event.currentTarget)"
              @mouseleave="hideTooltip"
            >
              <span class="relative z-10"><TrashIcon class="w-5 h-5" /></span>
              <div class="absolute inset-0 bg-red-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
            <GlassTooltip :visible="tooltipVisible && tooltipId === 'deleteSelected'" :targetRef="tooltipTargetRef" :delay="0">
              <span class="text-xs text-white">Delete Selected</span>
            </GlassTooltip>
          </div>
        </div>
      </div>

      <!-- Filters Bar (Elevated Glass) -->
      <div class="glass-elevated rounded-2xl p-4 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] flex flex-wrap items-center gap-3">
        <GlassInput v-model="filters.search" placeholder="Search file name..." class="w-48" />
        <GlassSelect
          v-model="filters.file_type"
          :options="[{value:'',label:'All types'},{value:'cv',label:'CV'},{value:'personal_statement',label:'Personal Statement'},{value:'other',label:'Other'}]"
          placeholder="Type"
        />
        <div class="relative inline-flex">
          <button
            @click="resetFilters"
            class="glass-btn-clear group relative w-10 h-10 rounded-xl bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 text-gray-500 dark:text-white/40 hover:text-gray-900 dark:hover:text-white overflow-hidden transition-all duration-300 hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#3b82f6] flex items-center justify-center"
            ref="clearBtnRef"
            @mouseenter="showTooltip('clearFilters', $event.currentTarget)"
            @mouseleave="hideTooltip"
          >
            <span class="relative z-10"><XMarkIcon class="w-5 h-5" /></span>
            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity blur-sm"></div>
          </button>
          <GlassTooltip :visible="tooltipVisible && tooltipId === 'clearFilters'" :targetRef="tooltipTargetRef" :delay="0">
            <span class="text-xs text-white">Clear Filters</span>
          </GlassTooltip>
        </div>
      </div>

      <!-- File Grid with Staggered Entrance -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div
          v-for="(doc, idx) in documents.data"
          :key="doc.id"
          class="glass-file-card group relative p-3 rounded-2xl border transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl stagger-item cursor-pointer"
          :class="[
            selectedIds.includes(doc.id)
              ? 'border-blue-400 shadow-[0_0_20px_rgba(59,130,246,0.3)] ring-1 ring-blue-400/50'
              : 'border-gray-200/60 dark:border-white/5',
          ]"
          :style="{ '--i': idx }"
          @click="toggleSelect(doc.id)"
        >
          <!-- File type icon -->
          <div class="w-16 h-16 mx-auto mb-2 rounded-xl bg-white/10 dark:bg-white/5 flex items-center justify-center backdrop-blur-sm">
            <svg v-if="doc.file_type === 'cv'" class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <svg v-else class="w-8 h-8 text-gray-400 dark:text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <p class="text-xs text-gray-900 dark:text-white truncate w-full font-medium">{{ doc.file_name }}</p>
          <p class="text-xs text-gray-500 dark:text-white/40 mt-1 capitalize">{{ doc.file_type.replace('_', ' ') }}</p>
          <p class="text-xs text-gray-400 dark:text-white/30 mt-0.5">{{ doc.user?.name || 'Unknown' }}</p>

          <!-- Hover Actions (Glass Toolbar) -->
          <div class="absolute top-2 right-2 flex gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
            <!-- Download action -->
            <div class="relative inline-flex">
              <a
                :href="`/admin/file-manager/${doc.id}/download`"
                class="glass-icon-btn group/icon w-7 h-7 rounded-lg flex items-center justify-center text-blue-400 hover:text-blue-300 transition-colors"
                @click.stop
                @mouseenter="showTooltip(`download-${doc.id}`, $event.currentTarget)"
                @mouseleave="hideTooltip"
              >
                <ArrowDownTrayIcon class="w-3.5 h-3.5" />
              </a>
              <GlassTooltip :visible="tooltipVisible && tooltipId === `download-${doc.id}`" :targetRef="tooltipTargetRef" :delay="0">
                <span class="text-xs text-white">Download</span>
              </GlassTooltip>
            </div>
            <!-- Delete action -->
            <div class="relative inline-flex">
              <button
                @click.stop="confirmDelete(doc.id)"
                class="glass-icon-btn group/icon w-7 h-7 rounded-lg flex items-center justify-center text-red-400 hover:text-red-300 transition-colors"
                @mouseenter="showTooltip(`delete-${doc.id}`, $event.currentTarget)"
                @mouseleave="hideTooltip"
              >
                <TrashIcon class="w-3.5 h-3.5" />
              </button>
              <GlassTooltip :visible="tooltipVisible && tooltipId === `delete-${doc.id}`" :targetRef="tooltipTargetRef" :delay="0">
                <span class="text-xs text-white">Delete</span>
              </GlassTooltip>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-center gap-1" v-if="documents.links">
        <button
          v-for="link in documents.links"
          :key="link.label"
          :href="link.url || '#'"
          :disabled="!link.url"
          class="glass-pagination-btn relative px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 dark:text-white/50 hover:text-gray-900 dark:hover:text-white/80 disabled:opacity-30 disabled:cursor-not-allowed transition-all duration-300 hover:bg-white/10 dark:hover:bg-white/5 active:scale-95"
          :class="{ 'bg-blue-500/10 dark:bg-blue-400/20 text-blue-600 dark:text-blue-400 shadow-[0_0_10px_rgba(59,130,246,0.2)]': link.active }"
          v-html="link.label"
        ></button>
      </div>

      <!-- Empty State -->
      <div v-if="documents.data.length === 0" class="text-center py-16">
        <div class="glass-empty-state rounded-xl p-8 inline-flex flex-col items-center gap-3">
          <svg class="w-10 h-10 text-gray-400 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          <span class="text-sm text-gray-500 dark:text-white/40">No files found.</span>
        </div>
      </div>

      <!-- Delete Confirmation Modal (Ultra-Hero) -->
      <GlassModal v-model="deleteModalOpen" @close="deleteModalOpen = false">
        <div class="space-y-5">
          <h3 class="text-xl font-bold text-red-400">Delete Files?</h3>
          <p class="text-sm text-gray-600 dark:text-white/60">Are you sure you want to delete the selected files? This cannot be undone.</p>
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
import { ref, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassModal from '@/Components/GlassModal.vue';
import GlassTooltip from '@/Components/GlassTooltip.vue';
import {
  ArrowDownTrayIcon,
  TrashIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  documents: Object,
  filters: Object,
});

const defaultFilters = {
  search: props.filters?.search || '',
  file_type: props.filters?.file_type || '',
};

const filters = reactive({ ...defaultFilters });
const selectedIds = ref([]);
const deleteModalOpen = ref(false);
const deleteTargetIds = ref([]);

// Tooltip state (added for icon-only buttons – does not alter existing logic)
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

function resetFilters() {
  Object.assign(filters, { search: '', file_type: '' });
  applyFilters();
}

function applyFilters() {
  const query = {};
  if (filters.search) query.search = filters.search;
  if (filters.file_type) query.file_type = filters.file_type;
  router.get(route('admin.file-manager.index'), query, { preserveState: true, replace: true });
}

watch(filters, applyFilters, { deep: true });

function toggleSelect(id) {
  const idx = selectedIds.value.indexOf(id);
  if (idx >= 0) {
    selectedIds.value.splice(idx, 1);
  } else {
    selectedIds.value.push(id);
  }
}

async function batchDownload() {
  const ids = selectedIds.value;
  if (!ids.length) return;
  window.open(`/admin/file-manager/batch-download?ids=${ids.join(',')}`, '_blank');
}

function confirmBatchDelete() {
  deleteTargetIds.value = [...selectedIds.value];
  deleteModalOpen.value = true;
}

function confirmDelete(id) {
  deleteTargetIds.value = [id];
  deleteModalOpen.value = true;
}

async function executeDelete() {
  try {
    await Promise.all(deleteTargetIds.value.map(id => axios.post(`/admin/file-manager/${id}`, { _method: 'DELETE' })));
    selectedIds.value = selectedIds.value.filter(id => !deleteTargetIds.value.includes(id));
    deleteModalOpen.value = false;
    location.reload();
  } catch (e) {
    console.error('Delete files error:', e);
  }
}
</script>

<style scoped>
/* ============================================================
   GLASS FILE MANAGER – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.05);
}

.glass-file-card {
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  transform: rotateY(-1deg) rotateX(0.5deg);
  will-change: transform;
  transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease, border-color 0.3s ease;
  opacity: 0;
  animation: card-fade-in 0.4s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 40ms);
}
.dark .glass-file-card {
  background: rgba(255, 255, 255, 0.04);
}
.glass-file-card:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-4px) scale(1.02);
  background: rgba(255, 255, 255, 0.45);
  z-index: 10;
}
.dark .glass-file-card:hover {
  background: rgba(255, 255, 255, 0.08);
}

@keyframes card-fade-in {
  0% { opacity: 0; transform: rotateY(-1deg) rotateX(0.5deg) translateY(12px); }
  100% { opacity: 1; transform: rotateY(-1deg) rotateX(0.5deg) translateY(0); }
}

/* Batch action buttons */
.glass-btn-download {
  transform: rotateY(-2deg);
  will-change: transform;
}
.glass-btn-download:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.glass-btn-download:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
}

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

.glass-btn-clear {
  transform: rotateY(-1deg);
  will-change: transform;
}
.glass-btn-clear:hover {
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

/* Pagination */
.glass-pagination-btn {
  transform: rotateY(-1deg);
  will-change: transform;
  transition: all 0.2s ease;
}
.glass-pagination-btn:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-1px) scale(1.02);
}
.glass-pagination-btn:active:not(:disabled) {
  transform: scale(0.95);
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
  .glass-file-card,
  .glass-file-card:hover,
  .glass-btn-download,
  .glass-btn-danger,
  .glass-btn-clear,
  .glass-icon-btn,
  .glass-pagination-btn {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .glass-file-card,
  .glass-btn-download,
  .glass-btn-danger,
  .glass-btn-clear,
  .glass-icon-btn,
  .glass-pagination-btn {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>