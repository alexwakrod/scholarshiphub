<template>
  <GlassModal v-model="open" @close="close">
    <div class="space-y-4">
      <h3 class="text-xl font-bold text-gray-900 dark:text-white">Batch Edit {{ ids.length }} Scholarships</h3>
      <p class="text-sm text-gray-500 dark:text-white/50">Choose the fields you want to update.</p>

      <div class="space-y-3">
        <div>
          <GlassCheckbox v-model="editFields.status" label="Status" />
          <GlassSelect
            v-if="editFields.status"
            v-model="values.status"
            :options="[{value:'active',label:'Active'},{value:'expired',label:'Expired'}]"
            class="mt-1"
          />
        </div>
        <div>
          <GlassCheckbox v-model="editFields.provider" label="Provider" />
          <GlassInput v-if="editFields.provider" v-model="values.provider" class="mt-1" />
        </div>
        <div>
          <GlassCheckbox v-model="editFields.country" label="Country" />
          <GlassInput v-if="editFields.country" v-model="values.country" class="mt-1" />
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-4">
        <button @click="close" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-white text-sm">Cancel</button>
        <button @click="apply" :disabled="!anyFieldSelected" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50">
          Apply
        </button>
      </div>
      <p v-if="error" class="text-red-500 dark:text-red-400 text-sm">{{ error }}</p>
    </div>
  </GlassModal>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import GlassModal from './GlassModal.vue';
import GlassCheckbox from './GlassCheckbox.vue';
import GlassSelect from './GlassSelect.vue';
import GlassInput from './GlassInput.vue';

const props = defineProps({
  modelValue: Boolean,
  ids: Array,
});

const emit = defineEmits(['update:modelValue', 'saved']);

const open = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

const editFields = reactive({
  status: false,
  provider: false,
  country: false,
});

const values = reactive({
  status: 'active',
  provider: '',
  country: '',
});

const error = ref('');

const anyFieldSelected = computed(() => {
  return editFields.status || editFields.provider || editFields.country;
});

function close() {
  open.value = false;
  resetForm();
}

function resetForm() {
  editFields.status = false;
  editFields.provider = false;
  editFields.country = false;
  values.status = 'active';
  values.provider = '';
  values.country = '';
  error.value = '';
}

async function apply() {
  const payload = { ids: props.ids, fields: {} };
  if (editFields.status) payload.fields.status = values.status;
  if (editFields.provider) payload.fields.provider = values.provider;
  if (editFields.country) payload.fields.country = values.country;

  try {
    await axios.put('/admin/scholarships/batch-edit', payload);
    emit('saved');
    close();
  } catch (e) {
    error.value = e.response?.data?.message || 'Batch edit failed.';
  }
}
</script>