<template>
  <AppLayout>
    <div class="p-4 md:p-6 h-full flex flex-col">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6">
        My <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Applications</span>
      </h1>

      <div class="flex-1 flex gap-4 overflow-hidden">
        <!-- ========== KANBAN (LEFT SIDE) ========== -->
        <div class="flex-1 min-w-0 overflow-x-auto custom-scrollbar">
          <div class="flex gap-4 min-h-[60vh]" style="transform-style: preserve-3d;">
            <div
              v-for="col in localColumns"
              :key="col.status"
              class="flex-1 min-w-[260px] max-w-[380px] flex flex-col transition-transform duration-500 transform -rotate-y-2 hover:rotate-y-0 hover:-translate-y-1"
              @dragover.prevent
              @drop="onDrop($event, col.status)"
            >
              <!-- Column header -->
              <div class="flex items-center justify-between mb-3 px-2">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-white/80 uppercase tracking-wider">
                  {{ col.status }}
                </h3>
                <span class="text-xs font-mono text-gray-500 dark:text-white/40 bg-gray-200/70 dark:bg-white/5 px-2 py-0.5 rounded-full">
                  {{ col.items?.length || 0 }}
                </span>
              </div>

              <!-- Drop zone -->
              <div
                class="flex-1 space-y-3 p-3 rounded-2xl transition-colors duration-300 overflow-y-auto custom-scrollbar glass-drop-zone"
              >
                <div
                  v-for="app in col.items"
                  :key="app.id"
                  class="group relative p-4 rounded-xl cursor-grab transition-all duration-300 glass-card-3d"
                  :class="{ 'dragging-active': draggedApp && draggedApp.id === app.id }"
                  draggable="true"
                  @dragstart="onDragStart($event, app)"
                  @click="selectApp(app)"
                >
                  <!-- Glow overlay -->
                  <div class="absolute -inset-0.5 rounded-xl bg-gradient-to-r from-blue-500/20 to-indigo-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 blur-xl pointer-events-none" />

                  <div class="relative z-10">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate transition-all duration-300 group-hover:bg-gradient-to-r group-hover:from-blue-400 group-hover:to-indigo-400 group-hover:bg-clip-text group-hover:text-transparent">
                      {{ app.title }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-white/50 mt-1">
                      Deadline: {{ app.deadline }}
                    </p>

                    <div class="flex items-center justify-between mt-3">
                      <div class="transition-transform duration-300 group-hover:scale-105">
                        <MatchGauge v-if="app.match_score" :score="app.match_score" :size="36" />
                        <span v-else class="text-xs text-gray-400 dark:text-white/30">No match</span>
                      </div>

                      <span class="text-[10px] px-2.5 py-0.5 rounded-full backdrop-blur-sm border font-medium"
                        :class="statusBadgeClass(app.status)"
                      >
                        {{ app.status }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Empty column -->
                <div v-if="!col.items || col.items.length === 0" class="text-center py-12 text-gray-400 dark:text-white/20 text-sm">
                  No applications
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ========== DETAIL PANEL (RIGHT SIDE) ========== -->
        <transition name="slide-panel">
          <div
            v-if="selectedApp"
            class="w-96 flex-shrink-0 glass-detail-panel rounded-2xl p-6 flex flex-col overflow-hidden"
          >
            <!-- Navigation arrows -->
            <div class="flex items-center justify-between mb-4">
              <button
                @click="navigateApp(-1)"
                :disabled="selectedAppIndex <= 0"
                class="p-1.5 rounded-lg glass-surface hover:glass-elevated transition-all disabled:opacity-30"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <span class="text-xs text-gray-500 dark:text-white/40">
                {{ selectedAppIndex + 1 }} / {{ flatList.length }}
              </span>
              <button
                @click="navigateApp(1)"
                :disabled="selectedAppIndex >= flatList.length - 1"
                class="p-1.5 rounded-lg glass-surface hover:glass-elevated transition-all disabled:opacity-30"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>

            <!-- Title & deadline -->
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1 truncate">{{ selectedApp.title }}</h3>
            <p class="text-xs text-gray-500 dark:text-white/40 mb-4">Deadline: {{ selectedApp.deadline }}</p>

            <!-- Checklist -->
            <div class="flex-1 overflow-y-auto custom-scrollbar space-y-4">
              <div>
                <h4 class="text-sm font-semibold text-gray-700 dark:text-white/80 mb-2">Checklist</h4>
                <ul class="space-y-2">
                  <li v-for="(task, idx) in (selectedApp.checklist || [])" :key="idx" class="flex items-center gap-3">
                    <GlassCheckbox
                      :modelValue="task.completed"
                      @update:modelValue="val => updateTask(selectedApp, task.task, val)"
                    />
                    <span :class="task.completed ? 'line-through text-gray-400 dark:text-white/40' : 'text-gray-900 dark:text-white/80'">
                      {{ task.task }}
                    </span>
                  </li>
                </ul>
                <div class="flex gap-2 mt-3">
                  <GlassInput v-model="newTask" placeholder="New task..." class="flex-1" />
                  <button @click="addTask(selectedApp)" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition-colors">
                    Add
                  </button>
                </div>
              </div>

              <!-- Notes -->
              <div>
                <h4 class="text-sm font-semibold text-gray-700 dark:text-white/80 mb-2">Notes</h4>
                <GlassTextarea
                  v-model="selectedApp.notes"
                  @blur="saveNotes(selectedApp)"
                  rows="4"
                  maxHeight="150"
                />
              </div>
            </div>

            <!-- Close button (optional) -->
            <button @click="selectedApp = null" class="mt-4 w-full py-2 rounded-xl bg-white/10 text-gray-700 dark:text-white/60 text-sm hover:bg-white/20 transition-colors">
              Close
            </button>
          </div>
        </transition>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassTextarea from '@/Components/GlassTextarea.vue';
import GlassCheckbox from '@/Components/GlassCheckbox.vue';
import MatchGauge from '@/Components/MatchGauge.vue';

const props = defineProps({
  columns: Array,
  statuses: Array,
});

const selectedApp = ref(null);
const newTask = ref('');

// Reactive copy of columns for optimistic updates and rollback
const localColumns = reactive(
  props.columns.map(col => ({
    status: col.status,
    items: col.items ? col.items.map(item => ({ ...item })) : [],
  }))
);

let draggedApp = null;

// Flatten all applications in column order for navigation
const flatList = computed(() => {
  const list = [];
  for (const col of localColumns) {
    for (const app of col.items) {
      list.push(app);
    }
  }
  return list;
});

// Index of currently selected app in flat list
const selectedAppIndex = computed(() => {
  if (!selectedApp.value) return -1;
  return flatList.value.findIndex(a => a.id === selectedApp.value.id);
});

function selectApp(app) {
  selectedApp.value = app;
}

function navigateApp(direction) {
  const index = selectedAppIndex.value + direction;
  if (index >= 0 && index < flatList.value.length) {
    selectedApp.value = flatList.value[index];
  }
}

function findAppById(id) {
  for (const col of localColumns) {
    const app = col.items.find(i => i.id === id);
    if (app) return { col, app };
  }
  return { col: null, app: null };
}

function onDragStart(event, app) {
  draggedApp = app;
  event.dataTransfer.effectAllowed = 'move';
  event.dataTransfer.setData('text/plain', app.id);
}

async function onDrop(event, newStatus) {
  event.preventDefault();
  if (!draggedApp) return;

  const appId = draggedApp.id;
  const { col: sourceCol, app } = findAppById(appId);
  if (!sourceCol || !app) return;
  if (sourceCol.status === newStatus) return;

  const destCol = localColumns.find(c => c.status === newStatus);
  if (!destCol) return;

  // Optimistic update
  sourceCol.items = sourceCol.items.filter(i => i.id !== appId);
  app.status = newStatus;
  destCol.items.push(app);

  try {
    await axios.put(`/applications/${appId}`, { status: newStatus });
    // If the moved app was selected, update the reference
    if (selectedApp.value && selectedApp.value.id === appId) {
      selectedApp.value = app;
    }
  } catch (error) {
    console.error('Status update failed, rolling back:', error);
    destCol.items = destCol.items.filter(i => i.id !== appId);
    app.status = sourceCol.status;
    sourceCol.items.push(app);
  }

  draggedApp = null;
}

async function updateTask(app, taskName, completed) {
  const originalChecklist = JSON.parse(JSON.stringify(app.checklist));
  app.checklist = app.checklist.map(t => t.task === taskName ? { ...t, completed } : t);

  try {
    await axios.put(`/applications/${app.id}`, { checklist: app.checklist });
  } catch (error) {
    console.error('updateTask failed, rolling back:', error);
    app.checklist = originalChecklist;
  }
}

async function addTask(app) {
  if (!newTask.value.trim()) return;
  const taskToAdd = { task: newTask.value.trim(), completed: false };
  const originalChecklist = JSON.parse(JSON.stringify(app.checklist));

  app.checklist = [...app.checklist, taskToAdd];

  try {
    await axios.put(`/applications/${app.id}`, { checklist: app.checklist });
    newTask.value = '';
  } catch (error) {
    console.error('addTask failed, rolling back:', error);
    app.checklist = originalChecklist;
  }
}

async function saveNotes(app) {
  const previousNotes = app.notes;
  try {
    await axios.put(`/applications/${app.id}`, { notes: app.notes });
  } catch (error) {
    console.error('saveNotes failed, rolling back:', error);
    app.notes = previousNotes;
  }
}

function statusBadgeClass(status) {
  const map = {
    interested: 'bg-yellow-500/10 border-yellow-500/30 text-yellow-600 dark:text-yellow-400',
    applied: 'bg-blue-500/10 border-blue-500/30 text-blue-600 dark:text-blue-400',
    submitted: 'bg-green-500/10 border-green-500/30 text-green-600 dark:text-green-400',
    accepted: 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400',
    rejected: 'bg-red-500/10 border-red-500/30 text-red-600 dark:text-red-400',
  };
  return map[status] || '';
}
</script>

<style scoped>
/* ============================================
   PREMIUM GLASS KANBAN – THEME‑AWARE
   ============================================ */

/* Drop zone */
.glass-drop-zone {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3);
}
.dark .glass-drop-zone {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(255, 255, 255, 0.06);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

/* 3D glass card */
.glass-card-3d {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.6);
  transform: rotateY(1deg) rotateX(0.5deg);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.dark .glass-card-3d {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.glass-card-3d:hover {
  transform: translateY(-2px) rotateY(0deg) rotateX(0deg) scale(1.02);
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.8);
  background: rgba(255, 255, 255, 0.4);
}
.dark .glass-card-3d:hover {
  background: rgba(255, 255, 255, 0.1);
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

.glass-card-3d.dragging-active,
.glass-card-3d:active {
  transform: none !important;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.4);
  transition: none;
}

/* Detail panel (right) */
.glass-detail-panel {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(24px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
}
.dark .glass-detail-panel {
  background: rgba(0, 0, 0, 0.4);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

/* Slide transition for panel */
.slide-panel-enter-active,
.slide-panel-leave-active {
  transition: all 0.3s ease;
}
.slide-panel-enter-from,
.slide-panel-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* 3D utilities */
.perspective-1000 { perspective: 1000px; }
.-rotate-y-2 { transform: rotateY(-2deg) rotateX(1deg); }
.rotate-y-0 { transform: rotateY(0deg); }

/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.15); border-radius: 4px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); }

/* Mobile: stack */
@media (max-width: 768px) {
  .flex-1.flex.gap-4 {
    flex-direction: column;
  }
  .glass-detail-panel {
    width: 100%;
    max-height: 50vh;
  }
  .perspective-1000 { perspective: none !important; }
  .-rotate-y-2, .glass-card-3d { transform: none !important; }
  .glass-card-3d:hover { transform: none !important; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.4); }
}

@media (prefers-reduced-motion: reduce) {
  .glass-card-3d, .glass-card-3d:hover, .-rotate-y-2, .slide-panel-enter-active, .slide-panel-leave-active {
    transition: none !important;
    transform: none !important;
  }
  .perspective-1000 { perspective: none !important; }
}
</style>