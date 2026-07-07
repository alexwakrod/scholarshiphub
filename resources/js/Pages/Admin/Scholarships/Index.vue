<template>
  <AppLayout>
    <div class="p-4 md:p-6 space-y-6 max-w-[1440px] mx-auto">
      <!-- Header Row -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          Scholarship Management
        </h1>
        <!-- Duplicate Selected button with tooltip -->
        <div class="relative inline-flex">
          <button
            @click="duplicateSelected"
            :disabled="!selectedIds.length"
            class="glass-btn-primary group relative w-10 h-10 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 text-white overflow-hidden transition-all duration-300 hover:shadow-[0_0_40px_rgba(99,102,241,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-indigo-400 flex items-center justify-center"
            ref="duplicateBtnRef"
            @mouseenter="showTooltip('duplicate', $event.currentTarget)"
            @mouseleave="hideTooltip"
          >
            <span class="relative z-10">
              <ClipboardDocumentIcon class="w-5 h-5" />
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-blue-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
          </button>
          <GlassTooltip :visible="tooltipVisible && tooltipId === 'duplicate'" :targetRef="tooltipTargetRef" :delay="0">
            <span class="text-xs text-white">Duplicate Selected</span>
          </GlassTooltip>
        </div>
      </div>

      <!-- Bulk Actions Bar -->
      <transition name="bulk-slide">
        <div
          v-if="selectedIds.length"
          class="bulk-actions-bar glass-elevated rounded-2xl p-4 flex flex-wrap items-center gap-3"
        >
          <span class="text-sm font-medium text-gray-700 dark:text-white/70">
            {{ selectedIds.length }} selected
          </span>
          <!-- Mark Active button with tooltip -->
          <div class="relative inline-flex">
            <button @click="bulkStatus('active')" 
              class="glass-btn-success group relative w-10 h-10 rounded-xl bg-green-600 text-white overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-green-400 flex items-center justify-center"
              ref="activeBtnRef"
              @mouseenter="showTooltip('markActive', $event.currentTarget)"
              @mouseleave="hideTooltip"
            >
              <span class="relative z-10">
                <CheckCircleIcon class="w-5 h-5" />
              </span>
              <div class="absolute inset-0 bg-green-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
            <GlassTooltip :visible="tooltipVisible && tooltipId === 'markActive'" :targetRef="tooltipTargetRef" :delay="0">
              <span class="text-xs text-white">Mark Active</span>
            </GlassTooltip>
          </div>

          <!-- Mark Expired button with tooltip -->
          <div class="relative inline-flex">
            <button @click="bulkStatus('expired')"
              class="glass-btn-warning group relative w-10 h-10 rounded-xl bg-amber-600 text-white overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(245,158,11,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-amber-400 flex items-center justify-center"
              ref="expiredBtnRef"
              @mouseenter="showTooltip('markExpired', $event.currentTarget)"
              @mouseleave="hideTooltip"
            >
              <span class="relative z-10">
                <ClockIcon class="w-5 h-5" />
              </span>
              <div class="absolute inset-0 bg-amber-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
            <GlassTooltip :visible="tooltipVisible && tooltipId === 'markExpired'" :targetRef="tooltipTargetRef" :delay="0">
              <span class="text-xs text-white">Mark Expired</span>
            </GlassTooltip>
          </div>

          <!-- Delete Selected button with tooltip -->
          <div class="relative inline-flex">
            <button @click="bulkDelete"
              class="glass-btn-danger group relative w-10 h-10 rounded-xl bg-red-600 text-white overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(239,68,68,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-red-400 flex items-center justify-center"
              ref="deleteBtnRef"
              @mouseenter="showTooltip('deleteSelected', $event.currentTarget)"
              @mouseleave="hideTooltip"
            >
              <span class="relative z-10">
                <TrashIcon class="w-5 h-5" />
              </span>
              <div class="absolute inset-0 bg-red-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
            <GlassTooltip :visible="tooltipVisible && tooltipId === 'deleteSelected'" :targetRef="tooltipTargetRef" :delay="0">
              <span class="text-xs text-white">Delete Selected</span>
            </GlassTooltip>
          </div>

          <!-- Batch Edit button with tooltip -->
          <div class="relative inline-flex">
            <button @click="openBatchEdit"
              class="glass-btn-primary group relative w-10 h-10 rounded-xl bg-blue-600 text-white overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(59,130,246,0.4)] hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-blue-400 flex items-center justify-center"
              ref="batchBtnRef"
              @mouseenter="showTooltip('batchEdit', $event.currentTarget)"
              @mouseleave="hideTooltip"
            >
              <span class="relative z-10">
                <PencilSquareIcon class="w-5 h-5" />
              </span>
              <div class="absolute inset-0 bg-blue-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
            </button>
            <GlassTooltip :visible="tooltipVisible && tooltipId === 'batchEdit'" :targetRef="tooltipTargetRef" :delay="0">
              <span class="text-xs text-white">Batch Edit</span>
            </GlassTooltip>
          </div>
        </div>
      </transition>

      <!-- GlassDataTable -->
      <GlassDataTable
        :columns="columns"
        :data="scholarships.data"
        :pagination="scholarships"
        :loading="false"
        :search="filters.search"
        :sortField="filters.sort"
        :sortDir="filters.direction"
        :perPage="filters.per_page"
        :searchable="true"
        :columnVisibility="true"
        :perPageOptions="[10, 25, 50]"
        @update:search="(v) => { filters.search = v; applyFilters(); }"
        @update:sortField="(v) => { filters.sort = v; applyFilters(); }"
        @update:sortDir="(v) => { filters.direction = v; applyFilters(); }"
        @update:perPage="(v) => { filters.per_page = v; applyFilters(); }"
        @page-change="(url) => router.get(url, filters, { preserveState: true, replace: true })"
      >
        <template #cell-actions="{ row }">
          <div class="flex items-center gap-1 justify-center">
            <!-- Edit action with tooltip -->
            <div class="relative inline-flex">
              <Link :href="route('admin.scholarships.edit', row.id)" 
                class="glass-icon-btn group relative w-8 h-8 rounded-lg flex items-center justify-center text-indigo-400 hover:text-indigo-300 transition-colors"
                @mouseenter="showTooltip(`edit-${row.id}`, $event.currentTarget)"
                @mouseleave="hideTooltip"
              >
                <PencilSquareIcon class="w-4 h-4" />
              </Link>
              <GlassTooltip :visible="tooltipVisible && tooltipId === `edit-${row.id}`" :targetRef="tooltipTargetRef" :delay="0">
                <span class="text-xs text-white">Edit</span>
              </GlassTooltip>
            </div>

            <!-- Re-parse action with tooltip -->
            <div class="relative inline-flex">
              <button @click="triggerParse(row.id)"
                class="glass-icon-btn group relative w-8 h-8 rounded-lg flex items-center justify-center text-amber-400 hover:text-amber-300 transition-colors"
                @mouseenter="showTooltip(`parse-${row.id}`, $event.currentTarget)"
                @mouseleave="hideTooltip"
              >
                <ArrowPathIcon class="w-4 h-4" />
              </button>
              <GlassTooltip :visible="tooltipVisible && tooltipId === `parse-${row.id}`" :targetRef="tooltipTargetRef" :delay="0">
                <span class="text-xs text-white">Re-parse with AI</span>
              </GlassTooltip>
            </div>

            <!-- Delete action with tooltip -->
            <div class="relative inline-flex">
              <button @click="confirmDelete(row.id)"
                class="glass-icon-btn group relative w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:text-red-300 transition-colors"
                @mouseenter="showTooltip(`delete-${row.id}`, $event.currentTarget)"
                @mouseleave="hideTooltip"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
              <GlassTooltip :visible="tooltipVisible && tooltipId === `delete-${row.id}`" :targetRef="tooltipTargetRef" :delay="0">
                <span class="text-xs text-white">Delete</span>
              </GlassTooltip>
            </div>
          </div>
        </template>
        <template #cell-status="{ row }">
          <div class="flex justify-center">
            <span
              class="status-pill inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              :class="row.status === 'active'
                ? 'bg-green-500/10 text-green-400 border border-green-500/30 shadow-[0_0_10px_rgba(16,185,129,0.15)]'
                : 'bg-red-500/10 text-red-400 border border-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.15)]'"
            >
              {{ row.status }}
            </span>
          </div>
        </template>
      </GlassDataTable>
    </div>

    <!-- Batch edit modal -->
    <BatchEditModal v-model="batchEditOpen" :ids="selectedIds" @saved="batchEditDone" />

    <UndoToast :visible="undoVisible" :message="undoMessage" :undoAction="undoAction" @undo="handleUndo" @dismiss="undoVisible = false" />
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassDataTable from '@/Components/GlassDataTable.vue';
import BatchEditModal from '@/Components/BatchEditModal.vue';
import UndoToast from '@/Components/UndoToast.vue';
import GlassTooltip from '@/Components/GlassTooltip.vue';
import { useDeferredDelete } from '@/composables/useDeferredDelete';
import {
  ClipboardDocumentIcon,
  CheckCircleIcon,
  ClockIcon,
  TrashIcon,
  PencilSquareIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  scholarships: Object,
  filters: Object,
});

const columns = [
  { key: 'id', label: 'ID', sticky: true, sortable: true },
  { key: 'title', label: 'Title', sortable: true },
  { key: 'provider', label: 'Provider', sortable: true },
  { key: 'country', label: 'Country', sortable: true },
  { key: 'deadline', label: 'Deadline', sortable: true, format: (v) => v?.substring(0,10) },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'actions', label: 'Actions' },
];

const selectedIds = ref([]);
const filters = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  sort: props.filters?.sort || 'created_at',
  direction: props.filters?.direction || 'desc',
  per_page: props.filters?.per_page || 15,
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

function applyFilters() {
  router.get(route('admin.scholarships.index'), filters, { preserveState: true, replace: true });
}

async function triggerParse(id) {
  try { await axios.post(`/admin/scholarships/${id}/parse`); alert('Parse triggered.'); } catch (e) {}
}

const { scheduleDelete, cancelDelete } = useDeferredDelete();
const undoVisible = ref(false);
const undoMessage = ref('');
const undoAction = ref(null);

function confirmDelete(id) {
  scheduleDelete(async () => {
    // Use method spoofing: POST with _method=DELETE
    await axios.post(`/admin/scholarships/${id}`, {
      _method: 'DELETE',
    });
    router.reload();
  }, 5000, 'Scholarship deleted. Undo?');
  undoVisible.value = true;
  undoMessage.value = 'Scholarship deleted. Undo?';
  undoAction.value = () => { cancelDelete(); undoVisible.value = false; };
}

async function bulkStatus(status) {
  try {
    await Promise.all(selectedIds.value.map(id => axios.put(`/admin/scholarships/${id}`, { status })));
    router.reload();
  } catch (e) { console.error(e); }
}

function bulkDelete() {
  if (!selectedIds.value.length) return;
  if (!confirm('Delete selected?')) return;
  selectedIds.value.forEach(id => confirmDelete(id));
  selectedIds.value = [];
}

const batchEditOpen = ref(false);
function openBatchEdit() { batchEditOpen.value = true; }
function batchEditDone() { batchEditOpen.value = false; router.reload(); }

async function duplicateSelected() {
  try {
    await Promise.all(selectedIds.value.map(id => axios.post(`/admin/scholarships/${id}/duplicate`)));
    router.reload();
  } catch (e) { console.error(e); }
}

function handleUndo() { if (undoAction.value) { undoAction.value(); undoVisible.value = false; } }
</script>

<style scoped>
/* ============================================================
   GLASS SCHOLARSHIPS INDEX – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

/* Elevated glass slab for bulk actions */
.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.5);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow:
    0 8px 32px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

/* 3D Button physics (common) – all square icon buttons */
.glass-btn-primary,
.glass-btn-success,
.glass-btn-warning,
.glass-btn-danger {
  transform: rotateY(-2deg);
  will-change: transform;
}
.glass-btn-primary:hover:not(:disabled),
.glass-btn-success:hover:not(:disabled),
.glass-btn-warning:hover:not(:disabled),
.glass-btn-danger:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.glass-btn-primary:active:not(:disabled),
.glass-btn-success:active:not(:disabled),
.glass-btn-warning:active:not(:disabled),
.glass-btn-danger:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
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

/* Glass icon buttons for row actions */
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

/* Status pills – floating glass tokens */
.status-pill {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  transition: all 0.2s ease;
}
.status-pill:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Bulk actions bar staggered entrance */
.bulk-slide-enter-active {
  transition: all 0.35s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.bulk-slide-leave-active {
  transition: all 0.25s ease;
}
.bulk-slide-enter-from {
  opacity: 0;
  transform: translateY(-12px);
}
.bulk-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* Mobile and accessibility overrides */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-btn-primary,
  .glass-btn-success,
  .glass-btn-warning,
  .glass-btn-danger,
  .glass-btn-primary:hover,
  .glass-btn-success:hover,
  .glass-btn-warning:hover,
  .glass-btn-danger:hover,
  .glass-btn-primary:active,
  .glass-btn-success:active,
  .glass-btn-warning:active,
  .glass-btn-danger:active {
    transform: none !important;
  }
  .glass-icon-btn:hover {
    transform: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>