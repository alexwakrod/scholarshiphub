<template>
  <div
    ref="overlayRef"
    class="absolute inset-0 z-10 cursor-crosshair"
    :class="{ 'pointer-events-none': !enabled }"
    @click="addAnnotation"
  >
    <!-- Existing annotations -->
    <div
      v-for="ann in annotations"
      :key="ann.id"
      class="absolute group"
      :style="{ left: ann.position_x + '%', top: ann.position_y + '%' }"
    >
      <div
        class="w-3 h-3 rounded-full bg-yellow-400 border border-white shadow cursor-pointer transform -translate-x-1/2 -translate-y-1/2"
        @click.stop="editAnnotation(ann)"
      ></div>
      <div
        class="absolute left-4 top-0 hidden group-hover:block glass-tooltip text-xs px-2 py-1 rounded whitespace-nowrap z-20"
      >
        {{ ann.note }}
        <button @click.stop="deleteAnnotation(ann.id)" class="ml-2 text-red-400 hover:text-red-300">&times;</button>
      </div>
    </div>

    <!-- Inline editor when adding/editing -->
    <div
      v-if="editing"
      class="absolute glass-overlay rounded-xl border border-white/20 p-3 z-30 shadow-xl"
      :style="{ left: editPosition.x + '%', top: editPosition.y + '%' }"
    >
      <GlassTextarea v-model="editNote" rows="2" placeholder="Add note..." class="mb-2" />
      <div class="flex justify-end gap-2">
        <button @click="cancelEdit" class="px-2 py-1 text-xs rounded bg-white/10 text-white">Cancel</button>
        <button @click="saveAnnotation" class="px-2 py-1 text-xs rounded bg-blue-600 text-white">Save</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import GlassTextarea from './GlassTextarea.vue';

const props = defineProps({
  chartId: { type: String, required: true },
  saveUrl: { type: String, default: '/admin/analytics/annotations' },
  enabled: { type: Boolean, default: true },
});

const overlayRef = ref(null);
const annotations = ref([]);
const editing = ref(false);
const editNote = ref('');
const editPosition = ref({ x: 0, y: 0 });
const editId = ref(null);

onMounted(async () => {
  try {
    const { data } = await axios.get(props.saveUrl, { params: { chart_id: props.chartId } });
    annotations.value = data;
  } catch (e) {
    console.error('Failed to load annotations:', e);
  }
});

function getRelativeCoords(event) {
  if (!overlayRef.value) return { x: 0, y: 0 };
  const rect = overlayRef.value.getBoundingClientRect();
  return {
    x: ((event.clientX - rect.left) / rect.width) * 100,
    y: ((event.clientY - rect.top) / rect.height) * 100,
  };
}

function addAnnotation(event) {
  if (!props.enabled) return;
  const pos = getRelativeCoords(event);
  editId.value = null;
  editNote.value = '';
  editPosition.value = pos;
  editing.value = true;
}

function editAnnotation(ann) {
  editId.value = ann.id;
  editNote.value = ann.note;
  editPosition.value = { x: ann.position_x, y: ann.position_y };
  editing.value = true;
}

function cancelEdit() {
  editing.value = false;
  editNote.value = '';
  editId.value = null;
}

async function saveAnnotation() {
  if (!editNote.value.trim()) {
    cancelEdit();
    return;
  }
  try {
    if (editId.value) {
      await axios.put(`${props.saveUrl}/${editId.value}`, {
        note: editNote.value,
      });
    } else {
      const { data } = await axios.post(props.saveUrl, {
        chart_id: props.chartId,
        position_x: editPosition.value.x,
        position_y: editPosition.value.y,
        note: editNote.value,
      });
      annotations.value.push(data);
    }
    cancelEdit();
  } catch (e) {
    console.error('Save annotation failed:', e);
  }
}

async function deleteAnnotation(id) {
  try {
    await axios.delete(`${props.saveUrl}/${id}`);
    annotations.value = annotations.value.filter(a => a.id !== id);
  } catch (e) {
    console.error('Delete annotation failed:', e);
  }
}
</script>

<style scoped>
.glass-tooltip {
  background: rgba(0,0,0,0.85);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.2);
}
</style>