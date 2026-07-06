<template>
  <div class="inline-block min-w-[60px] cursor-pointer" @click="startEditing" v-if="!editing">
    <slot name="view">{{ modelValue }}</slot>
  </div>
  <div v-else class="flex items-center gap-1">
    <input
      ref="inputRef"
      :value="modelValue"
      @input="updateLocal"
      @keydown.escape="cancel"
      @keydown.enter="save"
      @blur="save"
      class="px-2 py-1 text-sm rounded bg-white dark:bg-white/10 border border-gray-300 dark:border-white/20 text-gray-900 dark:text-white outline-none focus:border-blue-400 w-full"
    />
    <button @click="save" class="text-green-500 dark:text-green-400 text-xs">✓</button>
    <button @click="cancel" class="text-red-500 dark:text-red-400 text-xs">✕</button>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  editUrl: { type: String, default: '' },
  field: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'saved']);

const editing = ref(false);
const localValue = ref(props.modelValue);
const inputRef = ref(null);

async function startEditing() {
  editing.value = true;
  localValue.value = props.modelValue;
  await nextTick();
  inputRef.value?.focus();
}

function updateLocal(event) {
  localValue.value = event.target.value;
}

async function save() {
  editing.value = false;
  if (localValue.value !== props.modelValue) {
    emit('update:modelValue', localValue.value);
    if (props.editUrl) {
      try {
        const payload = {};
        payload[props.field || 'value'] = localValue.value;
        await axios.put(props.editUrl, payload);
        emit('saved', localValue.value);
      } catch (e) {
        console.error('Inline edit save failed:', e);
        localValue.value = props.modelValue;
        emit('update:modelValue', props.modelValue);
      }
    }
  }
}

function cancel() {
  editing.value = false;
  localValue.value = props.modelValue;
}
</script>