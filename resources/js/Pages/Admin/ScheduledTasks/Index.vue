<template>
  <AppLayout>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-white">Scheduled Tasks</h1>

      <!-- Registered commands list -->
      <GlassCard variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Available Commands</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="cmd in commands" :key="cmd.signature" class="p-4 rounded-xl bg-white/5 flex flex-col">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-white font-medium">{{ cmd.signature }}</p>
                <p class="text-xs text-white/50 mt-1">{{ cmd.description }}</p>
                <p class="text-xs text-white/30 mt-0.5">Cron: {{ cmd.cron }}</p>
              </div>
            </div>
            <div class="flex items-center justify-between mt-4">
              <span class="text-xs text-white/40">Next run: {{ cmd.nextRun }}</span>
              <button
                @click="runCommand(cmd.signature)"
                :disabled="running[cmd.signature]"
                class="px-3 py-1 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700 disabled:opacity-50"
              >
                {{ running[cmd.signature] ? 'Running...' : 'Run Now' }}
              </button>
            </div>
          </div>
        </div>
      </GlassCard>

      <!-- Execution history -->
      <GlassCard variant="elevated" class="p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Execution History</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="text-xs text-white/50 uppercase border-b border-white/10">
              <tr>
                <th class="px-4 py-3">Command</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Started</th>
                <th class="px-4 py-3">Finished</th>
                <th class="px-4 py-3">Output</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in logs.data" :key="log.id" class="border-b border-white/5 hover:bg-white/5">
                <td class="px-4 py-3 text-white">{{ log.command_name }}</td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 rounded-full text-xs" :class="statusClass(log.status)">
                    {{ log.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-white/50">{{ formatDate(log.started_at) }}</td>
                <td class="px-4 py-3 text-white/50">{{ log.finished_at ? formatDate(log.finished_at) : '—' }}</td>
                <td class="px-4 py-3">
                  <button
                    v-if="log.output"
                    @click="viewOutput(log)"
                    class="text-blue-400 hover:underline text-xs"
                  >
                    View
                  </button>
                  <span v-else class="text-white/30">—</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div class="mt-4 flex justify-center" v-if="logs.links">
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
      </GlassCard>

      <!-- Output modal -->
      <GlassModal v-model="outputModalOpen" @close="outputModalOpen = false">
        <div class="space-y-4">
          <h3 class="text-xl font-bold text-white">Command Output</h3>
          <pre class="whitespace-pre-wrap text-sm text-white/70 bg-black/20 p-3 rounded-lg max-h-96 overflow-y-auto">{{ selectedOutput }}</pre>
        </div>
      </GlassModal>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassCard from '@/Components/GlassCard.vue';
import GlassModal from '@/Components/GlassModal.vue';

const props = defineProps({
  commands: Array,  // [{ signature, description, cron, nextRun }]
  logs: Object,     // paginated logs
  filters: Object,
});

const running = reactive({});
const outputModalOpen = ref(false);
const selectedOutput = ref('');

async function runCommand(signature) {
  running[signature] = true;
  try {
    await axios.post('/admin/scheduled-tasks/run', { command: signature });
    // Refresh logs after a short delay
    setTimeout(() => {
      router.reload({ only: ['logs'] });
      running[signature] = false;
    }, 3000);
  } catch (e) {
    console.error('Run command error:', e);
    running[signature] = false;
  }
}

function viewOutput(log) {
  selectedOutput.value = log.output;
  outputModalOpen.value = true;
}

function statusClass(status) {
  const map = {
    success: 'bg-green-500/20 text-green-300',
    failed: 'bg-red-500/20 text-red-300',
    running: 'bg-blue-500/20 text-blue-300',
  };
  return map[status] || '';
}

function formatDate(dateStr) {
  try {
    return new Date(dateStr).toLocaleString();
  } catch { return dateStr; }
}
</script>