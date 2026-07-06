<template>
  <div class="space-y-2">
    <div class="flex gap-4" :class="layout === 'split' ? 'flex-col md:flex-row' : 'flex-col'">
      <!-- editor -->
      <div :class="layout === 'split' ? 'flex-1' : 'w-full'">
        <textarea
          v-model="localValue"
          @input="updatePreview"
          class="w-full px-4 py-3 rounded-xl glass-input border border-gray-300 dark:border-white/20 outline-none text-gray-900 dark:text-white resize-y min-h-[200px]"
          :placeholder="placeholder"
        ></textarea>
      </div>
      <!-- preview -->
      <div
        v-if="layout === 'split'"
        class="flex-1 prose prose-sm max-w-none p-4 rounded-xl glass-surface border border-gray-200 dark:border-white/10 overflow-y-auto text-gray-900 dark:text-gray-100"
        v-html="renderedHtml"
      ></div>
    </div>
    <!-- toolbar -->
    <div class="flex items-center justify-between">
      <div class="flex gap-1">
        <button @click="insertFormat('**', '**')" class="px-1.5 py-0.5 text-xs rounded bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-300 dark:hover:bg-white/20">B</button>
        <button @click="insertFormat('*', '*')" class="px-1.5 py-0.5 text-xs rounded bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-300 dark:hover:bg-white/20">I</button>
        <button @click="insertFormat('[Link](', ')')" class="px-1.5 py-0.5 text-xs rounded bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-300 dark:hover:bg-white/20">Link</button>
        <button @click="insertFormat('- ', '')" class="px-1.5 py-0.5 text-xs rounded bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-300 dark:hover:bg-white/20">List</button>
      </div>
      <span class="text-xs text-gray-500 dark:text-white/40">{{ characterCount }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { marked } from 'marked';

marked.setOptions({ breaks: true, gfm: true });

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Write markdown...' },
  layout: { type: String, default: 'editor-only', validator: (v) => ['editor-only', 'split'].includes(v) },
});

const emit = defineEmits(['update:modelValue']);

const localValue = ref(props.modelValue);
const renderedHtml = ref('');
const characterCount = computed(() => localValue.value.length + ' characters');

watch(() => props.modelValue, (val) => { localValue.value = val; updatePreview(); });

function updatePreview() {
  emit('update:modelValue', localValue.value);
  try {
    renderedHtml.value = marked.parse(localValue.value || '');
  } catch (e) {
    renderedHtml.value = '<p style="color:red;">Invalid markdown</p>';
  }
}

function insertFormat(before, after) {
  const textarea = document.querySelector('textarea');
  if (!textarea) return;
  const start = textarea.selectionStart, end = textarea.selectionEnd;
  const text = localValue.value;
  const selected = text.substring(start, end);
  localValue.value = text.substring(0, start) + before + selected + after + text.substring(end);
  updatePreview();
  textarea.focus();
  textarea.setSelectionRange(start + before.length, start + before.length + selected.length);
}

updatePreview();
</script>