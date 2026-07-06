<template>
  <div
    class="grid gap-4"
    :style="{
      gridTemplateColumns: `repeat(${columns}, 1fr)`,
      gridAutoRows: `${rowHeight}px`,
    }"
  >
    <div
      v-for="widget in widgets"
      :key="widget.widget_id"
      class="glass-elevated rounded-xl p-4 cursor-grab hover:shadow-lg transition-shadow relative group"
      :style="{
        gridColumn: `span ${widget.width}`,
        gridRow: `span ${widget.height}`,
      }"
      draggable="true"
      @dragstart="onDragStart($event, widget.widget_id)"
      @dragover.prevent="onDragOver($event, widget.widget_id)"
      @drop="onDrop($event, widget.widget_id)"
    >
      <!-- Title bar -->
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-white/70 uppercase">{{ widget.title }}</h3>
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
          <button @click="resizeWidget(widget.widget_id, 1)" class="text-gray-400 dark:text-white/50 hover:text-gray-900 dark:hover:text-white text-xs px-1" title="Wider">&#x21C4;</button>
          <button @click="toggleVisibility(widget.widget_id)" class="text-gray-400 dark:text-white/50 hover:text-gray-900 dark:hover:text-white text-xs px-1" :title="widget.visible ? 'Hide' : 'Show'">{{ widget.visible ? '&#x2715;' : '&#x25B6;' }}</button>
        </div>
      </div>

      <!-- Widget content slot -->
      <slot :name="widget.widget_id" :widget="widget" :config="widget.config">
        <p class="text-gray-500 dark:text-white/50 text-sm">Widget content</p>
      </slot>
    </div>

    <div v-if="widgets.length === 0" class="col-span-full text-center py-16 text-gray-500 dark:text-white/50">
      No widgets added. Configure your dashboard.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  columns: { type: Number, default: 3 },
  rowHeight: { type: Number, default: 200 },
  initialWidgets: { type: Array, default: () => [] },
  layoutSaveUrl: { type: String, default: '/admin/dashboard/layout' },
});

const emit = defineEmits(['layout-changed']);

const widgets = ref([]);

onMounted(() => {
  widgets.value = props.initialWidgets.map(w => ({ ...w }));
});

let draggedWidgetId = null;

function onDragStart(event, widgetId) {
  draggedWidgetId = widgetId;
  event.dataTransfer.effectAllowed = 'move';
  event.dataTransfer.setData('text/plain', widgetId);
}

function onDragOver(event, targetWidgetId) {
  if (!draggedWidgetId || draggedWidgetId === targetWidgetId) return;
  event.dataTransfer.dropEffect = 'move';
}

function onDrop(event, targetWidgetId) {
  event.preventDefault();
  if (!draggedWidgetId || draggedWidgetId === targetWidgetId) return;
  const sourceIndex = widgets.value.findIndex(w => w.widget_id === draggedWidgetId);
  const targetIndex = widgets.value.findIndex(w => w.widget_id === targetWidgetId);
  if (sourceIndex === -1 || targetIndex === -1) return;
  const moved = widgets.value.splice(sourceIndex, 1)[0];
  widgets.value.splice(targetIndex, 0, moved);
  saveLayout();
}

function resizeWidget(widgetId, amount = 1) {
  const widget = widgets.value.find(w => w.widget_id === widgetId);
  if (!widget) return;
  widget.width = Math.max(1, Math.min(props.columns, widget.width + amount));
  saveLayout();
}

function toggleVisibility(widgetId) {
  const widget = widgets.value.find(w => w.widget_id === widgetId);
  if (!widget) return;
  widget.visible = !widget.visible;
  saveLayout();
}

async function saveLayout() {
  try {
    const payload = widgets.value.map(w => ({
      widget_id: w.widget_id, position_x: 0, position_y: 0, width: w.width, height: w.height, visible: w.visible,
    }));
    await axios.put(props.layoutSaveUrl, { widgets: payload });
    emit('layout-changed', widgets.value);
  } catch (e) {
    console.error('Failed to save widget layout:', e);
  }
}
</script>