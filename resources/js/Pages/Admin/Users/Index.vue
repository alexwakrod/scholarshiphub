<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-[1440px] mx-auto space-y-6">
      <!-- Header Row -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          User Management
        </h1>
        <!-- Export Button with Tooltip -->
        <div class="relative inline-flex">
          <button
            @click="exportMenuOpen = !exportMenuOpen"
            class="glass-btn-export group relative w-10 h-10 rounded-xl bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 text-gray-600 dark:text-white/60 hover:text-gray-900 dark:hover:text-white overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg active:scale-95 focus-visible:ring-2 focus-visible:ring-[#3b82f6] flex items-center justify-center"
            ref="exportBtnRef"
            @mouseenter="showTooltip('export', $event.currentTarget)"
            @mouseleave="hideTooltip"
          >
            <span class="relative z-10">
              <ArrowDownTrayIcon class="w-5 h-5" />
            </span>
            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity blur-sm"></div>
          </button>
          <GlassTooltip :visible="tooltipVisible && tooltipId === 'export'" :targetRef="tooltipTargetRef" :delay="0">
            <span class="text-xs text-white">Export Users</span>
          </GlassTooltip>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-center gap-3">
        <GlassInput v-model="filters.search" placeholder="Search name or email..." class="w-64" />
        <GlassSelect
          v-model="filters.role"
          :options="[{value:'',label:'All roles'},{value:'student',label:'Student'},{value:'admin',label:'Admin'}]"
          placeholder="Role"
        />
        <div class="relative inline-flex">
          <button
            @click="resetFilters"
            class="glass-btn-clear group relative w-10 h-10 rounded-xl bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 text-gray-500 dark:text-white/40 hover:text-gray-900 dark:hover:text-white overflow-hidden transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg active:scale-95 focus-visible:ring-2 focus-visible:ring-[#3b82f6] flex items-center justify-center"
            ref="clearBtnRef"
            @mouseenter="showTooltip('clear', $event.currentTarget)"
            @mouseleave="hideTooltip"
          >
            <span class="relative z-10">
              <XMarkIcon class="w-5 h-5" />
            </span>
            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity blur-sm"></div>
          </button>
          <GlassTooltip :visible="tooltipVisible && tooltipId === 'clear'" :targetRef="tooltipTargetRef" :delay="0">
            <span class="text-xs text-white">Clear Filters</span>
          </GlassTooltip>
        </div>
      </div>

      <!-- Elevated Glass Table Container -->
      <div class="glass-elevated rounded-2xl border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] overflow-hidden">
        <!-- Sticky Header -->
        <div class="sticky top-0 z-10 glass-table-header px-4 py-3 border-b border-gray-200/30 dark:border-white/5">
          <div class="grid grid-cols-6 gap-4 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-white/40">
            <span>ID</span>
            <span>Name</span>
            <span class="col-span-2">Email</span>
            <span>Role</span>
            <span class="text-center">Actions</span>
          </div>
        </div>

        <!-- Table Rows with Staggered Entrance -->
        <div class="divide-y divide-gray-200/30 dark:divide-white/5">
          <div
            v-for="(row, idx) in users.data"
            :key="row.id"
            class="glass-table-row grid grid-cols-6 gap-4 px-4 py-3 items-center transition-all duration-300 hover:bg-white/5 dark:hover:bg-white/5"
            :style="{ '--i': idx }"
          >
            <span class="text-sm text-gray-500 dark:text-white/40 tabular-nums">{{ row.id }}</span>
            <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ row.name }}</span>
            <span class="text-sm text-gray-600 dark:text-white/70 truncate col-span-2">{{ row.email }}</span>
            <span class="text-sm">
              <span
                class="status-pill inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="row.role === 'admin'
                  ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/30'
                  : 'bg-blue-500/10 text-blue-400 border border-blue-500/30'"
              >
                {{ row.role }}
              </span>
            </span>
            <div class="flex items-center justify-center gap-1">
              <!-- View action -->
              <div class="relative inline-flex">
                <Link
                  :href="route('admin.users.show', row.id)"
                  class="glass-icon-btn group relative w-8 h-8 rounded-lg flex items-center justify-center text-indigo-400 hover:text-indigo-300 transition-colors"
                  @mouseenter="showTooltip(`view-${row.id}`, $event.currentTarget)"
                  @mouseleave="hideTooltip"
                >
                  <EyeIcon class="w-4 h-4" />
                </Link>
                <GlassTooltip :visible="tooltipVisible && tooltipId === `view-${row.id}`" :targetRef="tooltipTargetRef" :delay="0">
                  <span class="text-xs text-white">View Profile</span>
                </GlassTooltip>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!users.data.length" class="text-center py-16">
          <div class="glass-empty-state rounded-xl p-8 inline-flex flex-col items-center gap-3">
            <svg class="w-10 h-10 text-gray-400 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-sm text-gray-500 dark:text-white/40">No users found.</span>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="users.links" class="flex items-center justify-center gap-1">
        <button
          v-for="link in users.links"
          :key="link.label"
          :href="link.url || '#'"
          :disabled="!link.url"
          class="glass-pagination-btn relative px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 dark:text-white/50 hover:text-gray-900 dark:hover:text-white/80 disabled:opacity-30 disabled:cursor-not-allowed transition-all duration-300 hover:bg-white/10 dark:hover:bg-white/5 active:scale-95"
          :class="{ 'bg-blue-500/10 dark:bg-blue-400/20 text-blue-600 dark:text-blue-400 shadow-[0_0_10px_rgba(59,130,246,0.2)]': link.active }"
          v-html="link.label"
        ></button>
      </div>

      <!-- Export Dropdown (Hero Depth) -->
      <Teleport to="body">
        <transition name="dropdown-fade">
          <div
            v-if="exportMenuOpen"
            class="fixed z-50 glass-dropdown rounded-2xl border border-gray-200/50 dark:border-white/10 shadow-2xl p-2 min-w-[140px] backdrop-blur-xl"
            :style="dropdownStyle"
          >
            <button
              @click="exportData('csv')"
              class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-700 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
            >
              <DocumentTextIcon class="w-4 h-4 text-gray-500 dark:text-white/40" />
              CSV
            </button>
            <button
              @click="exportData('xlsx')"
              class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-700 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
            >
              <TableCellsIcon class="w-4 h-4 text-gray-500 dark:text-white/40" />
              Excel
            </button>
            <button
              @click="exportData('pdf')"
              class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-700 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
            >
              <DocumentIcon class="w-4 h-4 text-gray-500 dark:text-white/40" />
              PDF
            </button>
          </div>
        </transition>
      </Teleport>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassTooltip from '@/Components/GlassTooltip.vue';
import {
  ArrowDownTrayIcon,
  XMarkIcon,
  EyeIcon,
  DocumentTextIcon,
  TableCellsIcon,
  DocumentIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  users: Object,
  filters: Object,
});

const initialFilters = {
  search: props.filters?.search || '',
  role: props.filters?.role || '',
  sort: props.filters?.sort || 'created_at',
  direction: props.filters?.direction || 'desc',
};

const filters = reactive({ ...initialFilters });
const exportMenuOpen = ref(false);
const exportBtnRef = ref(null);

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

// Dropdown positioning (anchored to export button)
const dropdownStyle = ref({ top: '0px', left: '0px' });
function updateDropdownPosition() {
  if (!exportBtnRef.value) return;
  const rect = exportBtnRef.value.getBoundingClientRect();
  dropdownStyle.value = {
    top: (rect.bottom + 8) + 'px',
    right: (window.innerWidth - rect.right) + 'px',
  };
}
import { watch } from 'vue';
watch(exportMenuOpen, (val) => {
  if (val) {
    updateDropdownPosition();
    window.addEventListener('scroll', updateDropdownPosition, true);
    window.addEventListener('resize', updateDropdownPosition);
  } else {
    window.removeEventListener('scroll', updateDropdownPosition, true);
    window.removeEventListener('resize', updateDropdownPosition);
  }
});

function resetFilters() {
  filters.search = '';
  filters.role = '';
  applyFilters();
}

function applyFilters() {
  const query = {};
  if (filters.search) query.search = filters.search;
  if (filters.role) query.role = filters.role;
  query.sort = filters.sort;
  query.direction = filters.direction;
  router.get(route('admin.users.index'), query, { preserveState: true, replace: true });
}

function exportData(format) {
  exportMenuOpen.value = false;
  const query = new URLSearchParams({
    format,
    search: filters.search || '',
    role: filters.role || '',
  }).toString();
  window.open(`/admin/users/export?${query}`, '_blank');
}
</script>

<style scoped>
/* ============================================================
   GLASS USERS INDEX – THEME‑AWARE & BLUEPRINT COMPLIANT
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

/* Export button */
.glass-btn-export {
  transform: rotateY(-1deg);
  will-change: transform;
}
.glass-btn-export:hover {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}

/* Clear button */
.glass-btn-clear {
  transform: rotateY(-1deg);
  will-change: transform;
}
.glass-btn-clear:hover {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
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

/* Pagination buttons */
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

/* Dropdown panel – Hero depth */
.glass-dropdown {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
}
.dark .glass-dropdown {
  background: rgba(0, 0, 0, 0.75);
}

.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.96);
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
  .glass-btn-export,
  .glass-btn-export:hover,
  .glass-btn-clear,
  .glass-btn-clear:hover,
  .glass-pagination-btn,
  .glass-pagination-btn:hover,
  .glass-icon-btn,
  .glass-icon-btn:hover {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .glass-table-row,
  .glass-btn-export,
  .glass-btn-clear,
  .glass-pagination-btn,
  .glass-icon-btn,
  .dropdown-fade-enter-active,
  .dropdown-fade-leave-active {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>