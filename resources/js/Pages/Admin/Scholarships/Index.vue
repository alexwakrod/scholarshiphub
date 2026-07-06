<template>
  <AppLayout>
    <div class="p-6 space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Scholarship Management</h1>
        <button @click="duplicateSelected" :disabled="!selectedIds.length" class="px-3 py-1 rounded bg-indigo-600 text-xs text-white disabled:opacity-50">Duplicate Selected</button>
      </div>

      <!-- Bulk actions -->
      <div v-if="selectedIds.length" class="flex items-center gap-3 p-3 bg-white/5 rounded-lg">
        <span class="text-sm text-white/70">{{ selectedIds.length }} selected</span>
        <button @click="bulkStatus('active')" class="px-3 py-1 rounded bg-green-600 text-xs text-white">Mark Active</button>
        <button @click="bulkStatus('expired')" class="px-3 py-1 rounded bg-yellow-600 text-xs text-white">Mark Expired</button>
        <button @click="bulkDelete" class="px-3 py-1 rounded bg-red-600 text-xs text-white">Delete</button>
        <button @click="openBatchEdit" class="px-3 py-1 rounded bg-blue-600 text-xs text-white">Batch Edit</button>
      </div>

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
          <div class="flex items-center gap-2">
            <Link :href="route('admin.scholarships.edit', row.id)" class="text-indigo-400 hover:underline text-xs">Edit</Link>
            <button @click="triggerParse(row.id)" class="text-yellow-400 hover:underline text-xs">Re-parse</button>
            <button @click="confirmDelete(row.id)" class="text-red-400 hover:underline text-xs">Delete</button>
          </div>
        </template>
        <template #cell-status="{ row }">
          <span class="px-2 py-0.5 rounded-full text-xs" :class="row.status === 'active' ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'">
            {{ row.status }}
          </span>
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
import { useDeferredDelete } from '@/composables/useDeferredDelete';

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

function applyFilters() {
  router.get(route('admin.scholarships.index'), filters, { preserveState: true, replace: true });
}

function toggleSelectAll(event) {
  selectedIds.value = event.target.checked ? props.scholarships.data.map(s => s.id) : [];
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
    await axios.delete(`/admin/scholarships/${id}`);
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