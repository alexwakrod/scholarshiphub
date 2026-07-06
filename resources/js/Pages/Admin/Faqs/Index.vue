<template>
  <AppLayout>
    <div class="p-6 space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">FAQ Management</h1>
        <button @click="openCreate" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
          Add FAQ
        </button>
      </div>

      <!-- List -->
      <div class="overflow-x-auto glass-surface rounded-xl">
        <table class="w-full text-sm text-left">
          <thead class="text-xs text-white/50 uppercase border-b border-white/10">
            <tr>
              <th class="px-4 py-3 w-16">Order</th>
              <th class="px-4 py-3">Question</th>
              <th class="px-4 py-3">Answer</th>
              <th class="px-4 py-3">Published</th>
              <th class="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="faq in faqs" :key="faq.id" class="border-b border-white/5 hover:bg-white/5">
              <td class="px-4 py-3 text-white/70">{{ faq.order }}</td>
              <td class="px-4 py-3 font-medium text-white max-w-[200px] truncate">{{ faq.question }}</td>
              <td class="px-4 py-3 text-white/70 max-w-[300px] truncate">{{ faq.answer }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded-full text-xs" :class="faq.is_published ? 'bg-green-500/20 text-green-300' : 'bg-gray-500/20 text-gray-400'">
                  {{ faq.is_published ? 'Yes' : 'No' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button @click="openEdit(faq)" class="text-indigo-400 hover:underline text-xs">Edit</button>
                  <button @click="confirmDelete(faq.id)" class="text-red-400 hover:underline text-xs">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="faqs.length === 0" class="text-center py-8 text-white/50">No FAQs yet.</div>
      </div>

      <!-- Create/Edit modal -->
      <GlassModal v-model="modalOpen" @close="closeModal">
        <div class="space-y-4">
          <h3 class="text-xl font-bold text-white">{{ editing ? 'Edit FAQ' : 'New FAQ' }}</h3>
          <GlassInput v-model="form.question" placeholder="Question" />
          <GlassTextarea v-model="form.answer" placeholder="Answer" rows="4" />
          <GlassNumberInput v-model="form.order" :min="0" placeholder="Order" />
          <div class="flex items-center gap-2">
            <GlassToggle v-model="form.is_published" label="Published" />
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
          <h3 class="text-xl font-bold text-white">Delete FAQ?</h3>
          <p class="text-white/70">This action cannot be undone.</p>
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
import GlassTextarea from '@/Components/GlassTextarea.vue';
import GlassNumberInput from '@/Components/GlassNumberInput.vue';
import GlassToggle from '@/Components/GlassToggle.vue';

const props = defineProps({
  faqs: Array,
});

const modalOpen = ref(false);
const editing = ref(null);
const deleteModalOpen = ref(false);
const deletingId = ref(null);

const form = reactive({
  question: '',
  answer: '',
  order: 0,
  is_published: true,
});

function openCreate() {
  editing.value = null;
  form.question = '';
  form.answer = '';
  form.order = 0;
  form.is_published = true;
  modalOpen.value = true;
}

function openEdit(faq) {
  editing.value = faq.id;
  form.question = faq.question;
  form.answer = faq.answer;
  form.order = faq.order;
  form.is_published = faq.is_published;
  modalOpen.value = true;
}

function closeModal() {
  modalOpen.value = false;
}

async function save() {
  try {
    if (editing.value) {
      await axios.put(`/admin/faqs/${editing.value}`, form);
    } else {
      await axios.post('/admin/faqs', form);
    }
    location.reload();
  } catch (e) {
    console.error('Save FAQ error:', e);
  }
}

function confirmDelete(id) {
  deletingId.value = id;
  deleteModalOpen.value = true;
}

async function executeDelete() {
  if (!deletingId.value) return;
  try {
    await axios.delete(`/admin/faqs/${deletingId.value}`);
    location.reload();
  } catch (e) {
    console.error('Delete FAQ error:', e);
  }
}
</script>