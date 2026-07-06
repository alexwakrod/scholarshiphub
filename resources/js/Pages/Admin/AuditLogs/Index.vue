<template>
  <AppLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold text-white mb-6">Audit Logs</h1>

      <!-- Filters -->
      <div class="flex flex-wrap items-center gap-4 mb-6">
        <GlassInput v-model="filters.user_id" placeholder="User ID" class="w-32" type="number" />
        <GlassSelect
          v-model="filters.action"
          :options="actionOptions"
          placeholder="Action"
        />
        <GlassInput v-model="filters.subject_type" placeholder="Subject type (e.g., Scholarship)" class="w-48" />
        <GlassInput v-model="filters.date_from" type="date" placeholder="From" />
        <GlassInput v-model="filters.date_to" type="date" placeholder="To" />
        <button @click="applyFilters" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
          Filter
        </button>
        <button @click="resetFilters" class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm hover:bg-white/20">
          Clear
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto glass-surface rounded-xl">
        <table class="w-full text-sm text-left">
          <thead class="text-xs text-white/50 uppercase border-b border-white/10">
            <tr>
              <th class="px-4 py-3">ID</th>
              <th class="px-4 py-3">User</th>
              <th class="px-4 py-3">Action</th>
              <th class="px-4 py-3">Subject</th>
              <th class="px-4 py-3">Description</th>
              <th class="px-4 py-3">Date</th>
              <th class="px-4 py-3">Diff</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs.data" :key="log.id" class="border-b border-white/5 hover:bg-white/5">
              <td class="px-4 py-3 text-white/50">{{ log.id }}</td>
              <td class="px-4 py-3">{{ log.user?.name || 'System' }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded-full text-xs" :class="actionClass(log.action)">
                  {{ log.action }}
                </span>
              </td>
              <td class="px-4 py-3 text-white/70">
                {{ log.subject_type ? log.subject_type.split('\\').pop() + ' #' + log.subject_id : '—' }}
              </td>
              <td class="px-4 py-3 text-white/70 max-w-[200px] truncate">{{ log.description }}</td>
              <td class="px-4 py-3 text-white/50">{{ formatDate(log.created_at) }}</td>
              <td class="px-4 py-3">
                <button
                  v-if="log.properties && (log.properties.old || log.properties.new)"
                  @click="selectedDiff = log"
                  class="text-blue-400 hover:underline text-xs"
                >
                  View Diff
                </button>
                <span v-else class="text-white/30">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex justify-center" v-if="logs.links">
        <button
          v-for="link in logs.links"
          :key="link.label"
          :href="link.url || '#'"
          :disabled="!link.url"
          class="px-3 py-1 mx-1 rounded text-sm text-white/70 hover:bg-white/10 disabled:opacity-30"
          :class="{ 'bg-white/20': link.active }"
          v-html="link.label"
        ></button>
      </div>

      <!-- Diff modal -->
      <GlassModal v-model="diffOpen" @close="diffOpen = false">
        <div v-if="selectedDiff" class="space-y-4">
          <h3 class="text-xl font-bold text-white">Change Details</h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <h4 class="text-white/50 font-medium mb-2">Old Values</h4>
              <pre class="whitespace-pre-wrap text-red-300">{{ JSON.stringify(selectedDiff.properties.old, null, 2) }}</pre>
            </div>
            <div>
              <h4 class="text-white/50 font-medium mb-2">New Values</h4>
              <pre class="whitespace-pre-wrap text-green-300">{{ JSON.stringify(selectedDiff.properties.new, null, 2) }}</pre>
            </div>
          </div>
        </div>
      </GlassModal>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import GlassModal from '@/Components/GlassModal.vue';

const props = defineProps({
  logs: Object,
  filters: Object,
  actions: Array,
});

const defaultFilters = {
  user_id: props.filters?.user_id || '',
  action: props.filters?.action || '',
  subject_type: props.filters?.subject_type || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
};

const filters = reactive({ ...defaultFilters });
const actionOptions = props.actions.map(a => ({ value: a, label: a }));

const diffOpen = ref(false);
const selectedDiff = ref(null);

function applyFilters() {
  const query = {};
  Object.entries(filters).forEach(([k, v]) => { if (v) query[k] = v; });
  router.get(route('admin.audit-logs.index'), query, { preserveState: true, replace: true });
}

function resetFilters() {
  Object.assign(filters, { user_id: '', action: '', subject_type: '', date_from: '', date_to: '' });
  applyFilters();
}

function formatDate(dateStr) {
  try {
    return new Date(dateStr).toLocaleString();
  } catch { return dateStr; }
}

function actionClass(action) {
  switch (action) {
    case 'created': return 'bg-green-500/20 text-green-300';
    case 'updated': return 'bg-blue-500/20 text-blue-300';
    case 'deleted': return 'bg-red-500/20 text-red-300';
    case 'login': case 'logout': return 'bg-gray-500/20 text-gray-300';
    default: return 'bg-white/10 text-white/70';
  }
}
</script>