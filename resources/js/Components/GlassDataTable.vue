<template>
  <div class="space-y-4">
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
          <button @click="showColumnMenu = !showColumnMenu" class="px-3 py-1 rounded-lg bg-white/10 text-white text-xs hover:bg-white/20">
            Columns
          </button>
          <div v-if="showColumnMenu" class="absolute right-0 mt-1 w-48 glass-overlay rounded-xl border border-white/10 shadow-lg z-50 p-2">
            <div v-for="col in columns" :key="col.key" class="flex items-center gap-2 py-1">
              <GlassCheckbox :modelValue="visibleColumns.includes(col.key)" @update:modelValue="(val) => toggleColumn(col.key, val)" />
              <span class="text-xs text-white">{{ col.label }}</span>
            </div>
          </div>
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

    <!-- Table -->
    <div class="overflow-x-auto glass-surface rounded-xl">
      <table class="w-full text-sm text-left">
        <thead class="text-xs text-white/50 uppercase border-b border-white/10">
          <tr>
            <th
              v-for="col in visibleColumnsDetails"
              :key="col.key"
              class="px-4 py-3 cursor-pointer select-none hover:text-white"
              :class="{ 'sticky left-0 bg-gray-900 z-10': col.sticky }"
              @click="toggleSort(col)"
            >
              <div class="flex items-center gap-1">
                <slot :name="'header-' + col.key" :column="col">
                  {{ col.label }}
                </slot>
                <span v-if="sortField === col.key" class="text-blue-400">
                  {{ sortDir === 'asc' ? '▲' : '▼' }}
                </span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, idx) in data"
            :key="row.id || idx"
            class="border-b border-white/5 hover:bg-white/5 transition-colors"
          >
            <td
              v-for="col in visibleColumnsDetails"
              :key="col.key"
              class="px-4 py-3"
              :class="{ 'sticky left-0 bg-gray-900 z-10': col.sticky }"
            >
              <slot :name="'cell-' + col.key" :row="row" :column="col">
                {{ col.format ? col.format(row[col.key]) : row[col.key] }}
              </slot>
            </td>
          </tr>
          <tr v-if="data.length === 0 && !loading">
            <td :colspan="visibleColumnsDetails.length" class="text-center py-8 text-white/50">
              No data.
            </td>
          </tr>
          <tr v-if="loading">
            <td :colspan="visibleColumnsDetails.length" class="text-center py-4">
              <SkeletonLoader type="list" :count="3" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="flex items-center justify-between text-xs text-white/50">
      <span>Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }}</span>
      <div class="flex gap-1">
        <button
          v-for="link in pagination.links"
          :key="link.label"
          @click="goToPage(link.url)"
          :disabled="!link.url"
          class="px-2 py-1 rounded hover:bg-white/10 disabled:opacity-30"
          :class="{ 'bg-white/20': link.active }"
          v-html="link.label"
        ></button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import GlassInput from './GlassInput.vue';
import GlassSelect from './GlassSelect.vue';
import GlassCheckbox from './GlassCheckbox.vue';
import SkeletonLoader from './SkeletonLoader.vue';

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