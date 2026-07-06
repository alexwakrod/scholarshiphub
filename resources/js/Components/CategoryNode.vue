<template>
  <div>
    <div
      class="flex items-center gap-2 py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors group"
      :style="{ paddingLeft: depth * 20 + 12 + 'px' }"
      draggable="true"
      @dragstart="onDragStart"
      @dragover.prevent="onDragOver"
      @dragleave="onDragLeave"
      @drop="onDrop"
      :class="{ 'bg-blue-500/10 dark:bg-blue-400/10 ring-2 ring-blue-400/50': isDropTarget }"
    >
      <button
        v-if="node.children && node.children.length"
        @click="expanded = !expanded"
        class="text-gray-400 dark:text-white/50 hover:text-gray-900 dark:hover:text-white transition-colors w-4 h-4 flex items-center justify-center"
      >
        <svg
          class="w-4 h-4 transition-transform"
          :class="{ 'rotate-90': expanded }"
          fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
      <span v-else class="w-4 h-4"></span>

      <svg class="w-4 h-4 text-gray-400 dark:text-white/30 cursor-grab" fill="currentColor" viewBox="0 0 24 24">
        <path d="M8 6h2v2H8V6zm6 0h2v2h-2V6zM8 11h2v2H8v-2zm6 0h2v2h-2v-2zm-6 5h2v2H8v-2zm6 0h2v2h-2v-2z"/>
      </svg>

      <span class="text-sm text-gray-900 dark:text-white flex-1">{{ node.name }}</span>

      <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <button @click="$emit('add-child', node.id)" class="text-xs text-green-500 dark:text-green-400 hover:underline">+</button>
        <button @click="$emit('edit', node)" class="text-xs text-blue-500 dark:text-blue-400 hover:underline">Edit</button>
        <button @click="$emit('delete', node.id)" class="text-xs text-red-500 dark:text-red-400 hover:underline">Del</button>
      </div>
    </div>

    <div v-if="expanded && node.children && node.children.length">
      <CategoryNode
        v-for="child in node.children"
        :key="child.id"
        :node="child"
        :depth="depth + 1"
        @edit="(n) => $emit('edit', n)"
        @add-child="(id) => $emit('add-child', id)"
        @delete="(id) => $emit('delete', id)"
        @reorder="(data) => $emit('reorder', data)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  node: Object,
  depth: Number,
});

defineEmits(['edit', 'add-child', 'delete', 'reorder']);

const expanded = ref(true);
const isDropTarget = ref(false);

function onDragStart(event) {
  event.dataTransfer.setData('text/plain', props.node.id);
  event.dataTransfer.effectAllowed = 'move';
  // Add dragging class to the source element (optional)
  event.currentTarget.classList.add('opacity-50');
}

function onDragOver(event) {
  event.preventDefault();
  event.dataTransfer.dropEffect = 'move';
  isDropTarget.value = true;
}

function onDragLeave() {
  isDropTarget.value = false;
}

function onDrop(event) {
  event.preventDefault();
  isDropTarget.value = false;
  const draggedId = parseInt(event.dataTransfer.getData('text/plain'), 10);
  if (!draggedId || draggedId === props.node.id) return;
  // Remove dragging class from source
  const sourceEl = document.querySelector(`[data-category-id="${draggedId}"]`);
  if (sourceEl) sourceEl.classList.remove('opacity-50');
  // Emit reorder event: move dragged node under this node (as child)
  emit('reorder', { draggedId, targetParentId: props.node.id });
}
</script>