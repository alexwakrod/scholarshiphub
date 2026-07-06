<template>
  <GlassModal v-model="open" @close="close">
    <div class="space-y-4">
      <h3 class="text-xl font-bold text-gray-900 dark:text-white">Quick Survey</h3>
      <p class="text-sm text-gray-500 dark:text-white/50">Help us improve ScholarshipHub!</p>

      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-900 dark:text-white">Are you interested in fully‑funded scholarships?</span>
          <GlassToggle v-model="form.interested" />
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-900 dark:text-white">Would you like AI feedback on your documents?</span>
          <GlassToggle v-model="form.want_ai_feedback" />
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-900 dark:text-white">Would you pay for premium features?</span>
          <GlassToggle v-model="form.willing_to_pay" />
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-4">
        <button @click="close" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-white text-sm">Cancel</button>
        <button @click="submit" :disabled="submitting" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50">
          {{ submitting ? 'Submitting...' : 'Submit' }}
        </button>
      </div>
      <p v-if="error" class="text-red-500 dark:text-red-400 text-sm">{{ error }}</p>
      <p v-if="success" class="text-green-500 dark:text-green-400 text-sm">{{ success }}</p>
    </div>
  </GlassModal>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import GlassModal from './GlassModal.vue';
import GlassToggle from './GlassToggle.vue';

const props = defineProps({
  modelValue: Boolean,
});
const emit = defineEmits(['update:modelValue', 'submitted']);

const open = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

const form = reactive({
  interested: null,
  want_ai_feedback: null,
  willing_to_pay: null,
});

const submitting = ref(false);
const error = ref('');
const success = ref('');

function close() { open.value = false; }

async function submit() {
  submitting.value = true;
  error.value = '';
  success.value = '';
  try {
    await axios.post('/api/survey', form);
    success.value = 'Thank you for your feedback!';
    setTimeout(() => { close(); emit('submitted'); }, 1500);
  } catch (e) {
    error.value = e.response?.data?.message || 'Submission failed.';
  } finally {
    submitting.value = false;
  }
}
</script>