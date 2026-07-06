<template>
  <div v-if="error" class="text-red-500 dark:text-red-400 text-xs">{{ error }}</div>
  <a
    v-else-if="metadata"
    :href="url"
    target="_blank"
    rel="noopener noreferrer"
    class="flex border border-gray-200 dark:border-white/10 rounded-xl overflow-hidden hover:border-gray-400 dark:hover:border-white/20 transition-colors bg-gray-100 dark:bg-white/5 no-underline"
  >
    <img
      v-if="metadata.image"
      :src="metadata.image"
      class="w-32 h-20 object-cover border-r border-gray-200 dark:border-white/10 flex-shrink-0"
      alt="Link preview"
    />
    <div class="p-3 flex-1 min-w-0">
      <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ metadata.title || url }}</p>
      <p class="text-xs text-gray-500 dark:text-white/50 mt-0.5 line-clamp-2">{{ metadata.description || '' }}</p>
    </div>
  </a>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  url: { type: String, required: true },
});

const metadata = ref(null);
const error = ref('');

async function fetchMetadata() {
  try {
    const { data } = await axios.get('/api/og-metadata', { params: { url: props.url } });
    metadata.value = data;
  } catch (e) {
    console.error('LinkPreview fetch error:', e);
    error.value = 'Could not load preview.';
  }
}

onMounted(fetchMetadata);
</script>