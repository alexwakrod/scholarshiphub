<template>
  <div class="glass-datatable space-y-4">
    <!-- Top controls -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <GlassInput
          v-if="searchable"
          :modelValue="search"
          @update:modelValue="onSearch"
          placeholder="Search..."
          class="w-48"
        />
        <slot name="filters" />
      </div>
      <div class="flex items-center gap-2">
        <div v-if="columnVisibility" class="relative">
          <button @click="showColumnMenu = !showColumnMenu" class="glass-btn-columns group relative px-3 py-1.5 rounded-xl bg-white/10 dark:bg-white/5 backdrop-blur-sm border border-gray-300/50 dark:border-white/10 text-gray-700 dark:text-white/70 text-xs font-medium hover:bg-white/20 dark:hover:bg-white/10 transition-all duration-300 hover:-translate-y-0.5 active:scale-95 focus-visible:ring-2 focus-visible:ring-[#3b82f6]">
            <span class="relative z-10 flex items-center gap-1.5">
              <ViewColumnsIcon class="w-3.5 h-3.5" />
              Columns
            </span>
            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity blur-sm"></div>
          </button>
          <transition name="dropdown-fade">
            <div v-if="showColumnMenu" class="absolute right-0 mt-2 w-52 glass-dropdown rounded-2xl border border-gray-200/50 dark:border-white/10 shadow-2xl z-50 p-2 backdrop-blur-xl">
              <div v-for="col in columns" :key="col.key" class="flex items-start gap-2 py-1.5 px-2 rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                <GlassCheckbox :modelValue="visibleColumns.includes(col.key)" @update:modelValue="(val) => toggleColumn(col.key, val)" class="mt-0.5" />
                <span class="text-sm text-gray-700 dark:text-white/80">{{ col.label }}</span>
              </div>
            </div>
          </transition>
        </div>
        <GlassSelect
          v-if="perPageOptions"
          :modelValue="perPage"
          @update:modelValue="updatePerPage"
          :options="perPageOptions.map(n => ({ value: n, label: String(n) }))"
          placeholder="Per page"
          class="w-24"
        />
      </div>
    </div>

    <!-- Elevated Glass Table Container -->
    <div class="overflow-x-auto custom-scrollbar glass-elevated rounded-2xl border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)]">
      <table class="w-full text-sm text-left">
        <!-- Sticky Header -->
        <thead class="sticky top-0 z-10">
          <tr class="glass-table-header">
            <th
              v-for="col in visibleColumnsDetails"
              :key="col.key"
              class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-white/40 cursor-pointer select-none transition-colors duration-200 hover:text-gray-900 dark:hover:text-white/80"
              :class="{ 'sticky left-0 z-20': col.sticky }"
              @click="toggleSort(col)"
            >
              <div class="flex items-center gap-1">
                <slot :name="'header-' + col.key" :column="col">
                  {{ col.label }}
                </slot>
                <span v-if="sortField === col.key" class="text-[#3b82f6] dark:text-blue-400 flex items-center">
                  <ChevronUpIcon v-if="sortDir === 'asc'" class="w-3.5 h-3.5" />
                  <ChevronDownIcon v-else class="w-3.5 h-3.5" />
                </span>
              </div>
            </th>
          </tr>
        </thead>

        <!-- Body with staggered rows -->
        <tbody>
          <tr
            v-for="(row, idx) in data"
            :key="row.id || idx"
            class="glass-table-row border-b border-gray-200/30 dark:border-white/5 transition-all duration-300 hover:bg-white/5 dark:hover:bg-white/5"
            :style="{ '--i': idx }"
          >
            <td
              v-for="col in visibleColumnsDetails"
              :key="col.key"
              class="px-4 py-3 text-gray-700 dark:text-white/80"
              :class="{ 'sticky left-0 z-10': col.sticky }"
            >
              <slot :name="'cell-' + col.key" :row="row" :column="col">
                {{ col.format ? col.format(row[col.key]) : row[col.key] }}
              </slot>
            </td>
          </tr>
          <!-- Empty State Glass Panel -->
          <tr v-if="data.length === 0 && !loading">
            <td :colspan="visibleColumnsDetails.length" class="text-center py-12">
              <div class="glass-empty-state rounded-xl p-8 inline-flex flex-col items-center gap-3">
                <svg class="w-10 h-10 text-gray-400 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <span class="text-sm text-gray-500 dark:text-white/40">No data.</span>
              </div>
            </td>
          </tr>
          <!-- Loading Skeleton -->
          <tr v-if="loading">
            <td :colspan="visibleColumnsDetails.length" class="text-center py-4">
              <SkeletonLoader type="list" :count="3" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="flex items-center justify-between text-xs text-gray-500 dark:text-white/40">
      <span>Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }}</span>
      <div class="flex gap-1">
        <button
          v-for="link in pagination.links"
          :key="link.label"
          @click="goToPage(link.url)"
          :disabled="!link.url"
          class="glass-pagination-btn relative px-2 py-1 rounded-lg text-gray-600 dark:text-white/50 hover:text-gray-900 dark:hover:text-white/80 disabled:opacity-30 disabled:cursor-not-allowed transition-all duration-300 hover:bg-white/10 dark:hover:bg-white/5 active:scale-95"
          :class="{ 'bg-blue-500/10 dark:bg-blue-400/20 text-blue-600 dark:text-blue-400 font-semibold shadow-[0_0_10px_rgba(59,130,246,0.2)]': link.active }"
          v-html="link.label"
        ></button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import GlassInput from './GlassInput.vue';
import GlassSelect from './GlassSelect.vue';
import GlassCheckbox from './GlassCheckbox.vue';
import SkeletonLoader from './SkeletonLoader.vue';
import { ViewColumnsIcon, ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  columns: { type: Array, required: true },
  data: { type: Array, required: true },
  pagination: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  searchable: { type: Boolean, default: true },
  columnVisibility: { type: Boolean, default: true },
  perPageOptions: { type: Array, default: () => [10, 25, 50] },
  search: { type: String, default: '' },
  sortField: { type: String, default: '' },
  sortDir: { type: String, default: 'asc' },
  perPage: { type: Number, default: 15 },
});

const emit = defineEmits([
  'update:search',
  'update:sortField',
  'update:sortDir',
  'update:perPage',
  'page-change',
]);

const showColumnMenu = ref(false);

// column visibility
const visibleColumns = ref(props.columns.map(c => c.key));
const visibleColumnsDetails = computed(() =>
  props.columns.filter(c => visibleColumns.value.includes(c.key))
);

function toggleColumn(key, visible) {
  if (visible) {
    visibleColumns.value.push(key);
  } else {
    visibleColumns.value = visibleColumns.value.filter(k => k !== key);
  }
}

function toggleSort(col) {
  if (!col.sortable) return;
  if (props.sortField === col.key) {
    emit('update:sortDir', props.sortDir === 'asc' ? 'desc' : 'asc');
  } else {
    emit('update:sortField', col.key);
    emit('update:sortDir', 'asc');
  }
}

function onSearch(val) {
  emit('update:search', val);
}

function updatePerPage(val) {
  emit('update:perPage', val);
}

function goToPage(url) {
  emit('page-change', url);
}
</script>

<style scoped>
/* ============================================================
   GLASS DATA TABLE – THEME‑AWARE & BLUEPRINT COMPLIANT
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
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}
.dark .glass-table-header {
  background: rgba(0, 0, 0, 0.5);
  border-bottom-color: rgba(255, 255, 255, 0.08);
}

/* Row styling with stagger entrance */
.glass-table-row {
  opacity: 0;
  animation: row-fade-in 0.35s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 30ms);
  transition: background 0.2s ease, transform 0.2s ease;
  transform: rotateY(0.5deg) rotateX(0.2deg);
}
.glass-table-row:hover {
  transform: rotateY(0deg) rotateX(0deg) translateY(-1px) scale(1.005);
  background: rgba(255, 255, 255, 0.08);
}
.dark .glass-table-row:hover {
  background: rgba(255, 255, 255, 0.04);
}

@keyframes row-fade-in {
  0% { opacity: 0; transform: rotateY(0.5deg) rotateX(0.2deg) translateY(8px); }
  100% { opacity: 1; transform: rotateY(0.5deg) rotateX(0.2deg) translateY(0); }
}

/* Dropdown menu – Hero depth */
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

/* Columns toggle button */
.glass-btn-columns {
  transform: rotateY(-1deg);
  will-change: transform;
}
.glass-btn-columns:hover {
  transform: rotateY(0deg) translateY(-1px) scale(1.02);
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

/* Empty state glass panel */
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

/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar { height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.15);
  border-radius: 999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.15);
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.25);
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.25);
}

/* Mobile & accessibility overrides */
@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-table-row,
  .glass-table-row:hover,
  .glass-btn-columns,
  .glass-btn-columns:hover,
  .glass-pagination-btn,
  .glass-pagination-btn:hover {
    transform: none !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .glass-table-row,
  .glass-btn-columns,
  .glass-pagination-btn,
  .dropdown-fade-enter-active,
  .dropdown-fade-leave-active {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}
</style>