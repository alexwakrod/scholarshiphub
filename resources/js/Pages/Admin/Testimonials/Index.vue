<template>
  <AppLayout>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-white">Testimonial Management</h1>

      <div class="overflow-x-auto glass-surface rounded-xl">
        <table class="w-full text-sm text-left">
          <thead class="text-xs text-white/50 uppercase border-b border-white/10">
            <tr>
              <th class="px-4 py-3">Name</th>
              <th class="px-4 py-3">Quote</th>
              <th class="px-4 py-3">Grade</th>
              <th class="px-4 py-3">Approved</th>
              <th class="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in testimonials" :key="t.id" class="border-b border-white/5 hover:bg-white/5">
              <td class="px-4 py-3 text-white">{{ t.name_display }}</td>
              <td class="px-4 py-3 text-white/70 max-w-[300px] truncate">{{ t.quote }}</td>
              <td class="px-4 py-3 text-white/70">{{ t.grade || '—' }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded-full text-xs" :class="t.is_approved ? 'bg-green-500/20 text-green-300' : 'bg-yellow-500/20 text-yellow-300'">
                  {{ t.is_approved ? 'Approved' : 'Pending' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button
                    v-if="!t.is_approved"
                    @click="approve(t.id)"
                    class="text-green-400 hover:underline text-xs"
                  >
                    Approve
                  </button>
                  <button @click="confirmDelete(t.id)" class="text-red-400 hover:underline text-xs">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="testimonials.length === 0" class="text-center py-8 text-white/50">No testimonials yet.</div>
      </div>

      <!-- Delete confirmation modal -->
      <GlassModal v-model="deleteModalOpen" @close="deleteModalOpen = false">
        <div class="space-y-4">
          <h3 class="text-xl font-bold text-white">Delete Testimonial?</h3>
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
import { ref } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import GlassModal from '@/Components/GlassModal.vue';

const props = defineProps({
  testimonials: Array,
});

const deleteModalOpen = ref(false);
const deletingId = ref(null);

async function approve(id) {
  try {
    await axios.post(`/admin/testimonials/${id}/approve`);
    location.reload();
  } catch (e) {
    console.error('Approve error:', e);
  }
}

function confirmDelete(id) {
  deletingId.value = id;
  deleteModalOpen.value = true;
}

async function executeDelete() {
  if (!deletingId.value) return;
  try {
    await axios.delete(`/admin/testimonials/${deletingId.value}`);
    location.reload();
  } catch (e) {
    console.error('Delete error:', e);
  }
}
</script>