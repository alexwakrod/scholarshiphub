<template>
  <AppLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">User Management</h1>
        <div class="flex items-center gap-3 relative">
          <button @click="exportMenuOpen = !exportMenuOpen" class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm hover:bg-white/20">
            Export
          </button>
          <div v-if="exportMenuOpen" class="absolute right-0 top-full mt-2 w-40 glass-overlay rounded-xl border border-white/10 shadow-lg z-50">
            <button @click="exportData('csv')" class="block w-full text-left px-4 py-2 text-sm hover:bg-white/10 rounded-t-xl">CSV</button>
            <button @click="exportData('xlsx')" class="block w-full text-left px-4 py-2 text-sm hover:bg-white/10">Excel</button>
            <button @click="exportData('pdf')" class="block w-full text-left px-4 py-2 text-sm hover:bg-white/10 rounded-b-xl">PDF</button>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-center gap-4 mb-6">
        <GlassInput v-model="filters.search" placeholder="Search name or email..." class="w-64" />
        <GlassSelect
          v-model="filters.role"
          :options="[{value:'',label:'All roles'},{value:'student',label:'Student'},{value:'admin',label:'Admin'}]"
          placeholder="Role"
        />
        <button @click="resetFilters" class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm hover:bg-white/20">
          Clear
        </button>
      </div>

      <!-- Responsive Table -->
      <ResponsiveTable :columns="columns" :rows="users.data">
        <template #cell-actions="{ row }">
          <div class="flex items-center gap-2">
            <Link
              :href="route('admin.users.show', row.id)"
              class="text-indigo-400 hover:underline text-xs"
            >
              View
            </Link>
            <button
              v-if="row.role !== 'admin'"
              @click="impersonate(row.id)"
              class="text-yellow-400 hover:underline text-xs"
            >
              Impersonate
            </button>
          </div>
        </template>
      </ResponsiveTable>

      <!-- Pagination -->
      <div class="mt-6 flex justify-center" v-if="users.links">
        <button
          v-for="link in users.links"
          :key="link.label"
          :href="link.url || '#'"
          :disabled="!link.url"
          class="px-3 py-1 mx-1 rounded text-sm text-white/70 hover:bg-white/10 disabled:opacity-30"
          :class="{ 'bg-white/20': link.active }"
          v-html="link.label"
        ></button>
      </div>
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
import ResponsiveTable from '@/Components/ResponsiveTable.vue';

const props = defineProps({
  users: Object,
  filters: Object,
});

const columns = [
  { key: 'id', label: 'ID', sticky: true },
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role' },
  { key: 'created_at', label: 'Joined', format: (val) => new Date(val).toLocaleDateString() },
  { key: 'actions', label: 'Actions' },
];

const initialFilters = {
  search: props.filters?.search || '',
  role: props.filters?.role || '',
  sort: props.filters?.sort || 'created_at',
  direction: props.filters?.direction || 'desc',
};

const filters = reactive({ ...initialFilters });
const exportMenuOpen = ref(false);

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

async function impersonate(userId) {
  try {
    await axios.post(`/admin/impersonate/${userId}`);
    window.location.href = '/dashboard';
  } catch (e) {
    console.error('Impersonation failed:', e);
    alert('Impersonation failed.');
  }
}
</script>