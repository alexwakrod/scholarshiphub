<template>
  <GlassModal v-model="open" @close="open = false">
    <div class="space-y-4">
      <h3 class="text-xl font-bold text-gray-900 dark:text-white">Changes</h3>
      <div v-if="loading" class="text-gray-500 dark:text-white/50 text-sm">Loading...</div>
      <div v-else-if="error" class="text-red-500 dark:text-red-400 text-sm">{{ error }}</div>
      <div v-else class="grid grid-cols-2 gap-4 text-sm">
        <div>
          <h4 class="text-gray-500 dark:text-white/50 font-medium mb-2">Previous</h4>
          <pre class="whitespace-pre-wrap text-red-600 dark:text-red-300 text-xs max-h-60 overflow-y-auto">{{ formattedOld }}</pre>
        </div>
        <div>
          <h4 class="text-gray-500 dark:text-white/50 font-medium mb-2">Current</h4>
          <pre class="whitespace-pre-wrap text-green-600 dark:text-green-300 text-xs max-h-60 overflow-y-auto">{{ formattedNew }}</pre>
        </div>
      </div>
    </div>
  </GlassModal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import GlassModal from './GlassModal.vue';

const props = defineProps({
  scholarshipId: { type: Number, required: true },
  modelValue: Boolean,
});

const emit = defineEmits(['update:modelValue']);

const open = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

const loading = ref(false);
const error = ref('');
const oldData = ref(null);
const newData = ref(null);

const formattedOld = computed(() => oldData.value ? JSON.stringify(oldData.value, null, 2) : '');
const formattedNew = computed(() => newData.value ? JSON.stringify(newData.value, null, 2) : '');

watch(open, async (val) => {
  if (val) {
    loading.value = true;
    error.value = '';
    oldData.value = null;
    newData.value = null;
    try {
      const { data } = await axios.get(`/admin/scholarships/${props.scholarshipId}/diff`);
      oldData.value = data.old;
      newData.value = data.new;
    } catch (e) {
      error.value = e.response?.data?.message || 'Error loading diff.';
    } finally {
      loading.value = false;
    }
  }
});
</script>