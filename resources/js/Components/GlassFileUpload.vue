<template>
  <div>
    <!-- Drop zone / click area -->
    <div
      class="relative border-2 border-dashed rounded-xl p-8 transition-all duration-300 cursor-pointer text-center"
      :class="dragging
        ? 'border-blue-400 bg-blue-500/10 dark:bg-blue-400/10 shadow-[0_0_30px_rgba(59,130,246,0.2)]'
        : 'border-gray-300 dark:border-white/10 hover:border-gray-400 dark:hover:border-white/30 hover:bg-gray-50 dark:hover:bg-white/5'"
      @dragover.prevent="dragging = true"
      @dragleave.prevent="dragging = false"
      @drop.prevent="handleDrop"
      @click="triggerInput"
    >
      <input
        ref="fileInput"
        type="file"
        class="hidden"
        :accept="accept"
        :multiple="multiple"
        @change="handleFileChange"
      />

      <!-- No files selected -->
      <div v-if="!fileItems.length" class="text-gray-500 dark:text-white/60">
        <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <p class="text-sm text-gray-500 dark:text-white/60">Drag & drop files here, or click to browse</p>
        <p class="text-xs text-gray-400 dark:text-white/40 mt-1">Max file size: {{ maxSizeMB }} MB</p>
      </div>

      <!-- File list with progress bars -->
      <ul v-else class="space-y-3 text-left">
        <li
          v-for="(item, idx) in fileItems"
          :key="idx"
          class="flex items-center gap-3 p-3 rounded-lg bg-white/10 dark:bg-white/5 border border-gray-200 dark:border-white/5"
        >
          <svg class="w-8 h-8 text-gray-400 dark:text-white/50 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-900 dark:text-white truncate">{{ item.file.name }}</p>
            <p class="text-xs text-gray-500 dark:text-white/40">{{ formatSize(item.file.size) }}</p>
            <div v-if="item.uploading" class="mt-1 h-1.5 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-200"
                :style="{ width: item.progress + '%' }"
              ></div>
            </div>
            <p v-if="item.error" class="text-xs text-red-400 mt-0.5">{{ item.error }}</p>
          </div>
          <button @click.stop="removeItem(idx)" class="text-red-400 hover:text-red-600 dark:hover:text-red-300 p-1 shrink-0 transition-colors">&times;</button>
        </li>
      </ul>
    </div>

    <!-- Upload button -->
    <div class="mt-3 flex justify-end gap-2" v-if="fileItems.length && !allUploaded">
      <button
        @click="uploadAll"
        class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50 transition-all"
        :disabled="uploading"
      >
        {{ uploading ? 'Uploading...' : 'Upload' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  accept: { type: String, default: '' },
  multiple: { type: Boolean, default: false },
  maxSize: { type: Number, default: 10 },
  uploadUrl: { type: String, default: '' },
  extraData: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:modelValue', 'uploaded']);

const fileInput = ref(null);
const dragging = ref(false);
const fileItems = ref([]);

const maxSizeMB = computed(() => props.maxSize);
const allUploaded = computed(() => fileItems.value.every(i => i.uploading === false && !i.error));
const uploading = computed(() => fileItems.value.some(i => i.uploading));

function triggerInput() {
  fileInput.value?.click();
}

function handleFileChange(event) {
  const selected = Array.from(event.target.files);
  processFiles(selected);
}

function handleDrop(event) {
  dragging.value = false;
  const dropped = Array.from(event.dataTransfer.files);
  processFiles(dropped);
}

function processFiles(selected) {
  const maxBytes = props.maxSize * 1024 * 1024;
  const validFiles = [];
  for (const file of selected) {
    if (file.size <= maxBytes) {
      validFiles.push({ file, progress: 0, uploading: false, error: '' });
    } else {
      console.warn(`File "${file.name}" exceeds max size and was skipped.`);
    }
  }
  if (!props.multiple) {
    fileItems.value = validFiles.slice(0, 1);
  } else {
    fileItems.value = [...fileItems.value, ...validFiles];
  }
  emit('update:modelValue', fileItems.value.map(i => i.file));
}

function removeItem(index) {
  fileItems.value.splice(index, 1);
  emit('update:modelValue', fileItems.value.map(i => i.file));
}

async function uploadAll() {
  if (!props.uploadUrl) {
    emit('update:modelValue', fileItems.value.map(i => i.file));
    return;
  }

  for (const item of fileItems.value) {
    if (item.uploading || item.error) continue;
    item.uploading = true;
    item.progress = 0;
    const formData = new FormData();
    formData.append('file', item.file);
    if (props.extraData) {
      Object.entries(props.extraData).forEach(([key, value]) => formData.append(key, value));
    }

    try {
      await axios.post(props.uploadUrl, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        onUploadProgress: (progressEvent) => {
          if (progressEvent.total) {
            item.progress = Math.round((progressEvent.loaded / progressEvent.total) * 100);
          }
        },
      });
      item.uploading = false;
      item.progress = 100;
    } catch (err) {
      item.uploading = false;
      item.error = err.response?.data?.message || 'Upload failed';
    }
  }

  const successful = fileItems.value.filter(i => !i.error).map(i => i.file);
  emit('uploaded', successful);
}

function formatSize(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / 1048576).toFixed(1) + ' MB';
}
</script>