<template>
  <AppLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">Category Tree</h1>
        <button @click="openCreate(null)" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
          Add Root Category
        </button>
      </div>

      <!-- Tree -->
      <div class="space-y-1">
        <CategoryNode
          v-for="node in tree"
          :key="node.id"
          :node="node"
          :depth="0"
          @edit="openEdit"
          @add-child="openCreate"
          @delete="confirmDelete"
        />
      </div>

      <!-- Create/Edit modal -->
      <GlassModal v-model="modalOpen" @close="closeModal">
        <div class="space-y-4">
          <h3 class="text-xl font-bold text-white">{{ editing ? 'Edit Category' : 'New Category' }}</h3>
          <GlassInput v-model="form.name" placeholder="Category name" />
          <div v-if="form.parent_id !== undefined">
            <label class="block text-sm text-white/50 mb-1">Parent</label>
            <GlassSelect
              v-model="form.parent_id"
              :options="parentOptions"
              placeholder="None (root)"
            />
          </div>
          <div class="flex justify-end gap-3">
            <button @click="closeModal" class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm">Cancel</button>
            <button @click="save" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
              {{ editing ? 'Update' : 'Create' }}
            </button>
          </div>
        </div>
      </GlassModal>

      <!-- Delete confirmation -->
      <GlassModal v-model="deleteModalOpen" @close="deleteModalOpen = false">
        <div class="space-y-4">
          <h3 class="text-xl font-bold text-white">Delete Category?</h3>
          <p class="text-white/70">This will also delete all child categories. This action cannot be undone.</p>
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
import { ref, reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassModal from '@/Components/GlassModal.vue';
import GlassInput from '@/Components/GlassInput.vue';
import GlassSelect from '@/Components/GlassSelect.vue';
import CategoryNode from '@/Components/CategoryNode.vue';

const props = defineProps({
  tree: Array,
});

const modalOpen = ref(false);
const deleteModalOpen = ref(false);
const editing = ref(null);
const deletingId = ref(null);

const form = reactive({
  name: '',
  parent_id: null,
});

const parentOptions = ref([]);

function openCreate(parentId = null) {
  editing.value = null;
  form.name = '';
  form.parent_id = parentId;
  buildParentOptions(parentId);
  modalOpen.value = true;
}

function openEdit(node) {
  editing.value = node.id;
  form.name = node.name;
  form.parent_id = node.parent_id ?? null;
  buildParentOptions(node.parent_id ?? null);
  modalOpen.value = true;
}

function closeModal() {
  modalOpen.value = false;
}

function buildParentOptions(excludeId) {
  const options = [];
  function traverse(nodes, prefix = '') {
    nodes.forEach(n => {
      if (n.id !== excludeId) {
        options.push({ value: n.id, label: prefix + n.name });
      }
      if (n.children) traverse(n.children, prefix + '— ');
    });
  }
  traverse(props.tree);
  parentOptions.value = [{ value: null, label: 'None (root)' }, ...options];
}

async function save() {
  try {
    if (editing.value) {
      await axios.put(`/admin/categories/${editing.value}`, form);
    } else {
      await axios.post('/admin/categories', form);
    }
    location.reload();
  } catch (e) {
    console.error('Save category error:', e);
  }
}

function confirmDelete(id) {
  deletingId.value = id;
  deleteModalOpen.value = true;
}

async function executeDelete() {
  if (!deletingId.value) return;
  try {
    await axios.delete(`/admin/categories/${deletingId.value}`);
    location.reload();
  } catch (e) {
    console.error('Delete category error:', e);
  }
}
</script>