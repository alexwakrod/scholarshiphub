<template>
  <AppLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">File Manager</h1>
        <div class="flex items-center gap-3">
          <button
            v-if="selectedIds.length"
            @click="batchDownload"
            class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700"
          >
            Download Selected (ZIP)
          </button>
          <button
            v-if="selectedIds.length"
            @click="confirmBatchDelete"
            class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700"
          >
            Delete Selected
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-center gap-4 mb-6">
        <GlassInput v-model="filters.search" placeholder="Search file name..." class="w-64" />
        <GlassSelect
          v-model="filters.file_type"
          :options="[{value:'',label:'All types'},{value:'cv',label:'CV'},{value:'personal_statement',label:'Personal Statement'},{value:'other',label:'Other'}]"
          placeholder="Type"
        />
        <button @click="resetFilters" class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm hover:bg-white/20">
          Clear
        </button>
      </div>

      <!-- File grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <GlassCard
          v-for="doc in documents.data"
          :key="doc.id"
          class="p-3 flex flex-col items-center text-center cursor-pointer hover:bg-white/10 transition-colors relative"
          :class="{ 'ring-2 ring-blue-400': selectedIds.includes(doc.id) }"
          @click="toggleSelect(doc.id)"
        >
          <!-- file type icon -->
          <div class="w-16 h-16 mb-2 rounded-lg bg-white/10 flex items-center justify-center">
            <svg v-if="doc.file_type === 'cv'" class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <svg v-else class="w-8 h-8 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <p class="text-xs text-white truncate w-full">{{ doc.file_name }}</p>
          <p class="text-xs text-white/40 mt-1 capitalize">{{ doc.file_type.replace('_', ' ') }}</p>
          <p class="text-xs text-white/30 mt-1">{{ doc.user?.name || 'Unknown' }}</p>
          <!-- Actions -->
          <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <a :href="`/admin/file-manager/${doc.id}/download`" class="text-blue-400 text-xs" title="Download" @click.stop>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
            </a>
            <button @click.stop="confirmDelete(doc.id)" class="text-red-400 text-xs" title="Delete">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </GlassCard>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex justify-center" v-if="documents.links">
        <button
          v-for="link in documents.links"
          :key="link.label"
          :href="link.url || '#'"
          :disabled="!link.url"
          class="px-3 py-1 mx-1 rounded text-sm text-white/70 hover:bg-white/10 disabled:opacity-30"
          :class="{ 'bg-white/20': link.active }"
          v-html="link.label"
        ></button>
      </div>

      <!-- Empty state -->
      <div v-if="documents.data.length === 0" class="text-center py-16 text-white/50">
        No files found.
      </div>

      <!-- Confirm delete modal -->
      <GlassModal v-model="deleteModalOpen" @close="deleteModalOpen = false">
        <div class="space-y-4">
          <h3 class="text-xl font-bold text-white">Delete Files?</h3>
          <p class="text-white/70">Are you sure you want to delete the selected files? This cannot be undone.</p>
          <div class="flex justify-end gap-3">
            <button @click="deleteModalOpen = false" class="px-4 py-2 rounded-lg bg-white/10 text-white">Cancel</button>
            <button @click="executeDelete" class="px-4 py-2 rounded-lg bg-red-600 text-white">Delete</button>
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
import GlassCard from '@/Components/GlassCard.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassModal from '@/Components/GlassModal.vue';

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
    await Promise.all(deleteTargetIds.value.map(id => axios.delete(`/admin/file-manager/${id}`)));
    selectedIds.value = selectedIds.value.filter(id => !deleteTargetIds.value.includes(id));
    deleteModalOpen.value = false;
    location.reload();
  } catch (e) {
    console.error('Delete files error:', e);
  }
}
</script>