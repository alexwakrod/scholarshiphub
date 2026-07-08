<template>
  <AppLayout>
    <div class="p-4 md:p-6 max-w-[1400px] mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
          Scheduled Tasks
        </h1>
      </div>

      <!-- Available Commands Grid -->
      <div class="glass-elevated rounded-2xl p-5 md:p-6 border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)]">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <CommandLineIcon class="w-5 h-5 text-blue-400" />
          Available Commands
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="cmd in commands" :key="cmd.signature" class="glass-surface rounded-xl p-4 border border-gray-200/60 dark:border-white/5 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1 hover:shadow-lg stagger-item" :style="{ '--i': commands.indexOf(cmd) }">
            <div>
              <p class="text-sm font-mono font-bold text-gray-900 dark:text-white truncate">{{ cmd.signature }}</p>
              <p class="text-xs text-gray-500 dark:text-white/40 mt-1 line-clamp-2">{{ cmd.description }}</p>
              <div class="flex items-center gap-2 mt-2">
                <span class="text-xs text-gray-400 dark:text-white/30 font-mono">Cron: {{ cmd.cron }}</span>
                <span class="text-xs text-gray-400 dark:text-white/30">· Next: {{ cmd.nextRun }}</span>
              </div>
            </div>
            <div class="mt-3 flex justify-end">
              <div class="relative inline-flex">
                <button
                  @click="runCommand(cmd.signature)"
                  :disabled="running[cmd.signature]"
                  class="glass-btn-run group relative w-9 h-9 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white overflow-hidden transition-all duration-300 hover:shadow-[0_0_30px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 focus-visible:ring-2 focus-visible:ring-emerald-400 flex items-center justify-center"
                  @mouseenter="showTooltip(`run-${cmd.signature}`, $event.currentTarget)"
                  @mouseleave="hideTooltip"
                >
                  <span class="relative z-10">
                    <PlayIcon v-if="!running[cmd.signature]" class="w-4 h-4" />
                    <ArrowPathIcon v-else class="w-4 h-4 animate-spin" />
                  </span>
                  <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 group-hover:opacity-30 transition-opacity blur-xl localized-glow"></div>
                </button>
                <GlassTooltip :visible="tooltipVisible && tooltipId === `run-${cmd.signature}`" :targetRef="tooltipTargetRef" :delay="0">
                  <span class="text-xs text-white">{{ running[cmd.signature] ? 'Running...' : 'Run Now' }}</span>
                </GlassTooltip>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Execution History Table -->
      <div class="glass-elevated rounded-2xl border border-gray-200/60 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.4)] overflow-hidden">
        <div class="sticky top-0 z-10 glass-table-header px-4 py-3 border-b border-gray-200/30 dark:border-white/5 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <ClockIcon class="w-5 h-5 text-blue-400" />
            Execution History
          </h2>
        </div>
        <div class="grid grid-cols-12 gap-4 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-white/40 border-b border-gray-200/30 dark:border-white/5">
          <span class="col-span-3">Command</span>
          <span class="col-span-2">Status</span>
          <span class="col-span-2">Started</span>
          <span class="col-span-2">Finished</span>
          <span class="col-span-3 text-center">Output</span>
        </div>
        <div class="divide-y divide-gray-200/30 dark:divide-white/5">
          <div
            v-for="(log, idx) in logs.data"
            :key="log.id"
            class="grid grid-cols-12 gap-4 px-4 py-3 items-center glass-table-row transition-all duration-300 hover:bg-white/5 dark:hover:bg-white/5"
            :style="{ '--i': idx }"
          >
            <span class="col-span-3 text-sm font-mono text-gray-900 dark:text-white truncate">{{ log.command_name }}</span>
            <span class="col-span-2">
              <span class="status-pill inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="{
                  'bg-green-500/10 text-green-400 border border-green-500/30': log.status === 'success',
                  'bg-red-500/10 text-red-400 border border-red-500/30': log.status === 'failed',
                  'bg-blue-500/10 text-blue-400 border border-blue-500/30': log.status === 'running'
                }">
                {{ log.status }}
              </span>
            </span>
            <span class="col-span-2 text-sm text-gray-500 dark:text-white/40">{{ formatDate(log.started_at) }}</span>
            <span class="col-span-2 text-sm text-gray-500 dark:text-white/40">{{ log.finished_at ? formatDate(log.finished_at) : '—' }}</span>
            <span class="col-span-3 flex justify-center">
              <div class="relative inline-flex">
                <button
                  v-if="log.output"
                  @click="viewOutput(log)"
                  class="glass-icon-btn group relative w-8 h-8 rounded-lg flex items-center justify-center text-blue-400 hover:text-blue-300 transition-colors"
                  @mouseenter="showTooltip(`view-${log.id}`, $event.currentTarget)"
                  @mouseleave="hideTooltip"
                >
                  <DocumentTextIcon class="w-4 h-4" />
                </button>
                <GlassTooltip v-if="log.output" :visible="tooltipVisible && tooltipId === `view-${log.id}`" :targetRef="tooltipTargetRef" :delay="0">
                  <span class="text-xs text-white">View Output</span>
                </GlassTooltip>
                <span v-else class="text-sm text-gray-500 dark:text-white/40">—</span>
              </div>
            </span>
          </div>
        </div>
        <!-- Empty state -->
        <div v-if="!logs.data.length" class="text-center py-16">
          <div class="glass-empty-state rounded-xl p-8 inline-flex flex-col items-center gap-3">
            <ClockIcon class="w-10 h-10 text-gray-400 dark:text-white/20" />
            <span class="text-sm text-gray-500 dark:text-white/40">No execution history.</span>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="logs.links" class="flex items-center justify-center gap-1">
        <button
          v-for="link in logs.links"
          :key="link.label"
          :href="link.url || '#'"
          :disabled="!link.url"
          class="glass-pagination-btn relative px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 dark:text-white/50 hover:text-gray-900 dark:hover:text-white/80 disabled:opacity-30 disabled:cursor-not-allowed transition-all duration-300 hover:bg-white/10 dark:hover:bg-white/5 active:scale-95"
          :class="{ 'bg-blue-500/10 dark:bg-blue-400/20 text-blue-600 dark:text-blue-400 shadow-[0_0_10px_rgba(59,130,246,0.2)]': link.active }"
          v-html="link.label"
        ></button>
      </div>

      <!-- Output Modal -->
      <GlassModal v-model="outputModalOpen" @close="outputModalOpen = false">
        <div class="space-y-5">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <DocumentTextIcon class="w-5 h-5 text-blue-400" />
            Command Output
          </h3>
          <div class="glass-surface rounded-xl p-4 border border-gray-200/60 dark:border-white/5">
            <pre class="whitespace-pre-wrap text-xs text-gray-700 dark:text-white/70 max-h-96 overflow-y-auto custom-scrollbar">{{ selectedOutput }}</pre>
          </div>
        </div>
      </GlassModal>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassModal from '@/Components/GlassModal.vue';
import GlassTooltip from '@/Components/GlassTooltip.vue';
import {
  CommandLineIcon,
  PlayIcon,
  ArrowPathIcon,
  ClockIcon,
  DocumentTextIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  commands: Array,
  logs: Object,
  filters: Object,
});

const running = reactive({});
const outputModalOpen = ref(false);
const selectedOutput = ref('');

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

async function runCommand(signature) {
  running[signature] = true;
  try {
    await axios.post('/admin/scheduled-tasks/run', { command: signature });
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

function formatDate(dateStr) {
  try {
    return new Date(dateStr).toLocaleString();
  } catch { return dateStr; }
}
</script>

<style scoped>
/* ============================================================
   GLASS SCHEDULED TASKS – THEME‑AWARE & BLUEPRINT COMPLIANT
   ============================================================ */

.glass-elevated {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
.dark .glass-elevated {
  background: rgba(255, 255, 255, 0.05);
}

.glass-surface {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.dark .glass-surface {
  background: rgba(0, 0, 0, 0.2);
}

.glass-table-header {
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}
.dark .glass-table-header {
  background: rgba(0, 0, 0, 0.5);
}

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

.stagger-item {
  opacity: 0;
  animation: fade-in-up 0.4s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 60ms);
}
@keyframes fade-in-up {
  0% { opacity: 0; transform: translateY(12px); }
  100% { opacity: 1; transform: translateY(0); }
}

.glass-btn-run {
  transform: rotateY(-2deg);
  will-change: transform;
}
.glass-btn-run:hover:not(:disabled) {
  transform: rotateY(0deg) translateY(-2px) scale(1.02);
}
.glass-btn-run:active:not(:disabled) {
  transform: scale(0.95) translateY(1px);
  transition-duration: 0.1s;
}

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

.status-pill {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  transition: all 0.2s ease;
}
.status-pill:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

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

.localized-glow {
  filter: blur(20px);
  opacity: 0;
  transition: opacity 0.4s ease;
}
.group:hover .localized-glow {
  opacity: 0.3;
}

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.15);
  border-radius: 999px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.15);
}

@media (max-width: 767px), (hover: none) and (pointer: coarse) {
  .glass-table-row,
  .glass-table-row:hover,
  .glass-btn-run,
  .glass-icon-btn,
  .glass-pagination-btn {
    transform: none !important;
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